<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace raklib\server;

use pocketmine\utils\Binary;
use raklib\protocol\ACK;
use raklib\protocol\Datagram;
use raklib\protocol\EncapsulatedPacket;
use raklib\protocol\NACK;
use raklib\protocol\Packet;
use raklib\RakLib;
use raklib\utils\InternetAddress;
use function asort;
use function bin2hex;
use function chr;
use function count;
use function dechex;
use function max;
use function microtime;
use function ord;
use function serialize;
use function socket_strerror;
use function strlen;
use function substr;
use function time;
use function time_sleep_until;
use function trim;
use const PHP_INT_MAX;
use const SOCKET_ECONNRESET;
use const SOCKET_EWOULDBLOCK;

class SessionManager{

	private const RAKLIB_TPS = 100;
	private const RAKLIB_TIME_PER_TICK = 1 / self::RAKLIB_TPS;

	/** @var RakLibServer */
	protected $server;
	/** @var UDPServerSocket */
	protected $socket;

	/** @var int */
	protected $receiveBytes = 0;
	/** @var int */
	protected $sendBytes = 0;

	/** @var Session[] */
	protected $sessions = [];

	/** @var array */
	protected $traffic = [];

	/** @var OfflineMessageHandler */
	protected $offlineMessageHandler;
	/** @var string */
	protected $name = "";

	/** @var int */
	protected $packetLimit = 200;

	/** @var bool */
	protected $shutdown = false;

	/** @var int */
	protected $ticks = 0;
	/** @var float */
	protected $lastMeasure;

	/** @var int[] string (address) => int (unblock time) */
	protected $block = [];
	/** @var int[] string (address) => int (number of packets) */
	protected $ipSec = [];

	/** @var string[] regex filters used to block out unwanted raw packets */
	protected $rawPacketFilters = [];

	/** @var bool */
	public $portChecking = false;

	/** @var int */
	protected $startTimeMS;

	/** @var int */
	protected $maxMtuSize;

	/** @var InternetAddress */
	protected $reusableAddress;

	public function __construct(RakLibServer $server, UDPServerSocket $socket, int $maxMtuSize){
		$this->server = $server;
		$this->socket = $socket;

		$this->startTimeMS = (int) (microtime(true) * 1000);
		$this->maxMtuSize = $maxMtuSize;

		$this->offlineMessageHandler = new OfflineMessageHandler($this);

		$this->reusableAddress = clone $this->socket->getBindAddress();

		$this->run();
	}

	/**
	 * Returns the time in milliseconds since server start.
	 */
	public function getRakNetTimeMS() : int{
		return ((int) (microtime(true) * 1000)) - $this->startTimeMS;
	}

	public function getPort() : int{
		return $this->socket->getBindAddress()->port;
	}

	public function getMaxMtuSize() : int{
		return $this->maxMtuSize;
	}

	public function getProtocolVersion() : int{
		return $this->server->getProtocolVersion();
	}

	public function getLogger() : \ThreadedLogger{
		return $this->server->getLogger();
	}

	public function run() : void{
		$this->tickProcessor();
	}

	private function tickProcessor() : void{
		$this->lastMeasure = microtime(true);

		while(!$this->shutdown or count($this->sessions) > 0){
			$start = microtime(true);

			/*
			 * The below code is designed to allow co-op between sending and receiving to avoid slowing down either one
			 * when high traffic is coming either way. Yielding will occur after 100 messages.
			 */
			do{
				$stream = !$this->shutdown;
				for($i = 0; $i < 100 && $stream && !$this->shutdown; ++$i){
					$stream = $this->receiveStream();
				}

				$socket = true;
				for($i = 0; $i < 100 && $socket; ++$i){
					$socket = $this->receivePacket();
				}
			}while($stream || $socket);

			$this->tick();

			$time = microtime(true) - $start;
			if($time < self::RAKLIB_TIME_PER_TICK){
				@time_sleep_until(microtime(true) + self::RAKLIB_TIME_PER_TICK - $time);
			}
		}

		$this->socket->close();
	}

	private function tick() : void{
		$time = microtime(true);
		foreach($this->sessions as $session){
			$session->update($time);
			if($session->isFullyDisconnected()){
				$this->removeSessionInternal($session);
			}
		}

		$this->ipSec = [];

		if(!$this->shutdown and ($this->ticks % self::RAKLIB_TPS) === 0){
			if($this->sendBytes > 0 or $this->receiveBytes > 0){
				$diff = max(0.005, $time - $this->lastMeasure);
				$this->streamOption("bandwidth", serialize([
					"up" => $this->sendBytes / $diff,
					"down" => $this->receiveBytes / $diff
				]));
				$this->sendBytes = 0;
				$this->receiveBytes = 0;
			}
			$this->lastMeasure = $time;

			if(count($this->block) > 0){
				asort($this->block);
				$now = time();
				foreach($this->block as $address => $timeout){
					if($timeout <= $now){
						unset($this->block[$address]);
					}else{
						break;
					}
				}
			}
		}

		++$this->ticks;
	}

	public function is_flooding($source, $port){
		if(!empty($source) and !empty($port)){
		    if(!isset($this->traffic[$source][$port])){
			    $this->traffic[$source][$port] = [
			        'start' => date('s'),
			        'packets' => 0
			    ];
		    }

		    /*$sessionsIP = count($this->traffic[$source]);
		    if ($sessionsIP > 6) {
		    	$this->blockAddress($source); //TODO: возможно новый метод..
		    }*/

		    if(date('s') == $this->traffic[$source][$port]['start']){
		    	$this->traffic[$source][$port]['packets']++;
		    	return false;
		    }

		    if($this->traffic[$source][$port]['packets'] > 1000){
			    $this->server->getLogger()->warning("Flood detected from IP: {$source}");
			    $this->server->getLogger()->warning("PPS: {$this->traffic[$source][$port]['packets']}");
			    $this->blockAddress($source);
			    unset($this->traffic[$source]);
		    }else{
		    	unset($this->traffic[$source][$port]);
		    }
	    }
	}

	/** @phpstan-impure */
	private function receivePacket() : bool{
		$address = $this->reusableAddress;

		$len = $this->socket->readPacket($buffer, $address->ip, $address->port);
		if($len === false){
			$error = $this->socket->getLastError();
			if($error === SOCKET_EWOULDBLOCK){ //no data
				return false;
			}elseif($error === SOCKET_ECONNRESET){ //client disconnected improperly, maybe crash or lost connection
				return true;
			}

			$this->getLogger()->debug("Socket error occurred while trying to recv ($error): " . trim(socket_strerror($error)));
			return false;
		}

		$this->receiveBytes += $len;
		if(isset($this->block[$address->ip])){
			return true;
		}

		if(isset($this->ipSec[$address->ip])){
			if(++$this->ipSec[$address->ip] >= $this->packetLimit){
				$this->blockAddress($address->ip);
				return true;
			}
		}else{
			$this->ipSec[$address->ip] = 1;
		}

		if($this->is_flooding($address->ip, $address->port)){
			return true;
		}

		if($len < 1){
			return true;
		}

		try{
			$session = $this->getSession($address);
			if($session !== null){
				$header = ord($buffer[0]);
				if(($header & Datagram::BITFLAG_VALID) !== 0){
					if(($header & Datagram::BITFLAG_ACK) !== 0){
						$session->handlePacket(new ACK($buffer));
					}elseif(($header & Datagram::BITFLAG_NAK) !== 0){
						$session->handlePacket(new NACK($buffer));
					}else{
						$session->handlePacket(new Datagram($buffer));
					}
				    return true;
				}elseif($session->isConnected()){
					//allows unconnected packets if the session is stuck in DISCONNECTING state, useful if the client
					//didn't disconnect properly for some reason (e.g. crash)
					$this->server->getLogger()->debug("Ignored unconnected packet from $address due to session already opened (0x" . bin2hex($buffer[0]) . ")");
				    return true;
				}
			}

			if(!$this->shutdown){
				if(!($handled = $this->offlineMessageHandler->handleRaw($buffer, $address))){
					foreach($this->rawPacketFilters as $pattern){
						if(preg_match($pattern, $buffer) > 0){
							$handled = true;
							$this->streamRaw($address, $buffer);
							break;
						}
					}
				}

				if(!$handled){
					$this->server->getLogger()->debug("Ignored packet from $address due to no session opened (0x" . bin2hex($buffer[0]) . ")");
				}
			}
		}catch(\Throwable $e){
			$logger = $this->getLogger();
			$logger->debug("Packet from $address (" . strlen($buffer) . " bytes): 0x" . bin2hex($buffer));
			$logger->logException($e);
			$this->blockAddress($address->ip, 5);
		}

		return true;
	}

	public function sendPacket(Packet $packet, InternetAddress $address) : void{
		$packet->encode();
		$this->sendBytes += $this->socket->writePacket($packet->getBuffer(), $address->ip, $address->port);
	}

	public function closeSession(int $sessionId) : void{
		if(isset($this->sessions[$sessionId])){
			$this->sessions[$sessionId]->initiateDisconnect("server disconnect");
		}
	}

	public function streamEncapsulated(Session $session, EncapsulatedPacket $packet, int $flags = RakLib::PRIORITY_NORMAL) : void{
		$id = $session->getAddress()->toString();
		$buffer = chr(RakLib::PACKET_ENCAPSULATED) . chr(strlen($id)) . $id . chr($flags) . $packet->toInternalBinary();
		$this->server->pushThreadToMainPacket($buffer);
	}

	public function streamRaw(InternetAddress $source, string $payload) : void{
		$buffer = chr(RakLib::PACKET_RAW) . chr(strlen($source->ip)) . $source->ip . Binary::writeShort($source->port) . $payload;
		$this->server->pushThreadToMainPacket($buffer);
	}

	public function streamClose(string $identifier, string $reason) : void{
		$buffer = chr(RakLib::PACKET_CLOSE_SESSION) . chr(strlen($identifier)) . $identifier . chr(strlen($reason)) . $reason;
		$this->server->pushThreadToMainPacket($buffer);
	}

	protected function streamInvalid(string $identifier) : void{
		$buffer = chr(RakLib::PACKET_INVALID_SESSION) . chr(strlen($identifier)) . $identifier;
		$this->server->pushThreadToMainPacket($buffer);
	}

	protected function streamOpen(Session $session) : void{
		$address = $session->getAddress();
		$identifier = $address->toString();
		$buffer = chr(RakLib::PACKET_OPEN_SESSION) . chr(strlen($identifier)) . $identifier . chr(strlen($address->ip)) . $address->ip . Binary::writeShort($address->port) . Binary::writeLong($session->getID());
		$this->server->pushThreadToMainPacket($buffer);
	}

	protected function streamACK(string $identifier, int $identifierACK) : void{
		$buffer = chr(RakLib::PACKET_ACK_NOTIFICATION) . chr(strlen($identifier)) . $identifier . Binary::writeInt($identifierACK);
		$this->server->pushThreadToMainPacket($buffer);
	}

	/**
	 * @param mixed  $value
	 */
	protected function streamOption(string $name, $value) : void{
		$buffer = chr(RakLib::PACKET_SET_OPTION) . chr(strlen($name)) . $name . $value;
		$this->server->pushThreadToMainPacket($buffer);
	}

	public function streamPingMeasure(Session $session, int $pingMS) : void{
		$identifier = $session->getAddress()->toString();
		$buffer = chr(RakLib::PACKET_REPORT_PING) . chr(strlen($identifier)) . $identifier . Binary::writeInt($pingMS);
		$this->server->pushThreadToMainPacket($buffer);
	}

	/** @phpstan-impure */
	public function receiveStream() : bool{
		if(($packet = $this->server->readMainToThreadPacket()) !== null){
			$id = ord($packet[0]);
			$offset = 1;
			if($id === RakLib::PACKET_ENCAPSULATED){
				$len = ord($packet[$offset++]);
				$identifier = substr($packet, $offset, $len);
				$offset += $len;
				$session = $this->sessions[$identifier] ?? null;
				if($session !== null and $session->isConnected()){
					$flags = ord($packet[$offset++]);
					$buffer = substr($packet, $offset);
					$session->addEncapsulatedToQueue(EncapsulatedPacket::fromInternalBinary($buffer), $flags);
				}else{
					$this->streamInvalid($identifier);
				}
			}elseif($id === RakLib::PACKET_RAW){
				$len = ord($packet[$offset++]);
				$address = substr($packet, $offset, $len);
				$offset += $len;
				$port = Binary::readShort(substr($packet, $offset, 2));
				$offset += 2;
				$payload = substr($packet, $offset);
				$this->socket->writePacket($payload, $address, $port);
			}elseif($id === RakLib::PACKET_CLOSE_SESSION){
				$len = ord($packet[$offset++]);
				$identifier = substr($packet, $offset, $len);
				if(isset($this->sessions[$identifier])){
					$this->sessions[$identifier]->initiateDisconnect("close session");
				}else{
					$this->streamInvalid($identifier);
				}
			}elseif($id === RakLib::PACKET_INVALID_SESSION){
				$len = ord($packet[$offset++]);
				$identifier = substr($packet, $offset, $len);
				if(isset($this->sessions[$identifier])){
					$this->removeSessionInternal($this->sessions[$identifier]);
				}
			}elseif($id === RakLib::PACKET_SET_OPTION){
				$len = ord($packet[$offset++]);
				$name = substr($packet, $offset, $len);
				$offset += $len;
				$value = substr($packet, $offset);
				switch($name){
					case "name":
						$this->name = $value;
						break;
					case "portChecking":
						$this->portChecking = (bool) $value;
						break;
					case "packetLimit":
						$this->packetLimit = (int) $value;
						break;
				}
			}elseif($id === RakLib::PACKET_BLOCK_ADDRESS){
				$len = ord($packet[$offset++]);
				$address = substr($packet, $offset, $len);
				$offset += $len;
				$timeout = Binary::readInt(substr($packet, $offset, 4));
				$this->blockAddress($address, $timeout);
			}elseif($id === RakLib::PACKET_UNBLOCK_ADDRESS){
				$len = ord($packet[$offset++]);
				$address = substr($packet, $offset, $len);
				$this->unblockAddress($address);
			}elseif($id === RakLib::PACKET_RAW_FILTER){
				$pattern = substr($packet, $offset);
				$this->rawPacketFilters[] = $pattern;
			}elseif($id === RakLib::PACKET_SHUTDOWN){
				foreach($this->sessions as $session){
					$session->initiateDisconnect("server shutdown");
				}

				$this->shutdown = true;
			}elseif($id === RakLib::PACKET_EMERGENCY_SHUTDOWN){
				$this->shutdown = true;
			}else{
				$this->getLogger()->debug("Unknown RakLib internal packet (ID 0x" . dechex($id) . ") received from main thread");
			}

			return true;
		}

		return false;
	}

	public function blockAddress(string $address, int $timeout = 300) : void{
		$final = time() + $timeout;
		if(!isset($this->block[$address]) or $timeout === -1){
			if($timeout === -1){
				$final = PHP_INT_MAX;
			}else{
				$this->getLogger()->notice("Blocked $address for $timeout seconds");
			}
			$this->block[$address] = $final;
		}elseif($this->block[$address] < $final){
			$this->block[$address] = $final;
		}
	}

	public function unblockAddress(string $address) : void{
		unset($this->block[$address]);
		$this->getLogger()->debug("Unblocked $address");
	}

	public function getSession(InternetAddress $address) : ?Session{
		return $this->sessions[$address->toString()] ?? null;
	}

	public function sessionExists(InternetAddress $address) : bool{
		return isset($this->sessions[$address->toString()]);
	}

	public function createSession(InternetAddress $address, int $clientId, int $mtuSize) : Session{
		$existingSession = $this->sessions[$address->toString()] ?? null;
		if($existingSession !== null){
			$existingSession->forciblyDisconnect("client reconnect");
			$this->removeSessionInternal($existingSession);
		}

		$this->checkSessions();

		$this->sessions[$address->toString()] = $session = new Session($this, clone $address, $clientId, $mtuSize);
		$this->getLogger()->debug("Created session for $address with MTU size $mtuSize");

		return $session;
	}

	private function removeSessionInternal(Session $session) : void{
		unset($this->sessions[$session->getAddress()->toString()]);
	}

	public function openSession(Session $session) : void{
		$this->streamOpen($session);
	}

	private function checkSessions() : void{
		if(count($this->sessions) > 4096){
			foreach($this->sessions as $i => $s){
				if($s->isTemporal()){
					unset($this->sessions[$i]);
					if(count($this->sessions) <= 4096){
						break;
					}
				}
			}
		}
	}

	public function notifyACK(Session $session, int $identifierACK) : void{
		$this->streamACK($session->getAddress()->toString(), $identifierACK);
	}

	public function getName() : string{
		return $this->name;
	}

	public function getID() : int{
		return $this->server->getServerId();
	}
}