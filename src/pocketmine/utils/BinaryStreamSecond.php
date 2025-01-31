<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\utils;

#include <rules/BinaryIO.h>

use function chr;
use function ord;
use function strlen;
use function substr;

class BinaryStreamSecond{

	/** @var int */
	public $offset;
	/** @var string */
	public $buffer;

	public function __construct(string $buffer = "", int $offset = 0){
		$this->buffer = $buffer;
		$this->offset = $offset;
	}

	/**
	 * @return void
	 */
	public function reset(){
		$this->buffer = "";
		$this->offset = 0;
	}

	/**
	 * Rewinds the stream pointer to the start.
	 */
	public function rewind() : void{
		$this->offset = 0;
	}

	public function setOffset(int $offset) : void{
		$this->offset = $offset;
	}

	/**
	 * @return void
	 */
	public function setBuffer(string $buffer = "", int $offset = 0){
		$this->buffer = $buffer;
		$this->offset = $offset;
	}

	public function getOffset() : int{
		return $this->offset;
	}

	public function getBuffer() : string{
		return $this->buffer;
	}

	/**
	 * @param int|true $len
	 *
	 * @throws BinaryDataException if there are not enough bytes left in the buffer
	 */
	public function get($len) : string{
		if($len === 0){
			return "";
		}

		$buflen = strlen($this->buffer);
		if($len === true){
			$str = substr($this->buffer, $this->offset);
			$this->offset = $buflen;
			return $str;
		}
		if($len < 0){
			$this->offset = $buflen - 1;
			return "";
		}
		$remaining = $buflen - $this->offset;
		if($remaining < $len){
			throw new BinaryDataException("Not enough bytes left in buffer: need $len, have $remaining");
		}

		return $len === 1 ? $this->buffer[$this->offset++] : substr($this->buffer, ($this->offset += $len) - $len, $len);
	}

	/**
	 * @throws BinaryDataException
	 */
	public function getRemaining() : string{
		$buflen = strlen($this->buffer);
		if($this->offset >= $buflen){
			throw new BinaryDataException("No bytes left to read");
		}
		$str = substr($this->buffer, $this->offset);
		$this->offset = $buflen;
		return $str;
	}

	/**
	 * @return void
	 */
	public function put(string $str){
		$this->buffer .= $str;
	}

	public function getBool() : bool{
		return $this->get(1) !== "\x00";
	}

	/**
	 * @return void
	 */
	public function putBool(bool $v){
		$this->buffer .= ($v ? "\x01" : "\x00");
	}

	public function getByte() : int{
		return ord($this->get(1));
	}

	/**
	 * @return void
	 */
	public function putByte(int $v){
		$this->buffer .= chr($v);
	}

	public function getShort() : int{
		return Binary::readShort($this->get(2));
	}

	public function getSignedShort() : int{
		return Binary::readSignedShort($this->get(2));
	}

	/**
	 * @return void
	 */
	public function putShort(int $v){
		$this->buffer .= Binary::writeShort($v);
	}

	public function getLShort() : int{
		return Binary::readLShort($this->get(2));
	}

	public function getSignedLShort() : int{
		return Binary::readSignedLShort($this->get(2));
	}

	/**
	 * @return void
	 */
	public function putLShort(int $v){
		$this->buffer .= Binary::writeLShort($v);
	}

	public function getTriad() : int{
		return Binary::readTriad($this->get(3));
	}

	/**
	 * @return void
	 */
	public function putTriad(int $v){
		$this->buffer .= Binary::writeTriad($v);
	}

	public function getLTriad() : int{
		return Binary::readLTriad($this->get(3));
	}

	/**
	 * @return void
	 */
	public function putLTriad(int $v){
		$this->buffer .= Binary::writeLTriad($v);
	}

	public function getInt() : int{
		return Binary::readInt($this->get(4));
	}

	/**
	 * @return void
	 */
	public function putInt(int $v){
		$this->buffer .= Binary::writeInt($v);
	}

	public function getLInt() : int{
		return Binary::readLInt($this->get(4));
	}

	/**
	 * @return void
	 */
	public function putLInt(int $v){
		$this->buffer .= Binary::writeLInt($v);
	}

	public function getFloat() : float{
		return Binary::readFloat($this->get(4));
	}

	public function getRoundedFloat(int $accuracy) : float{
		return Binary::readRoundedFloat($this->get(4), $accuracy);
	}

	/**
	 * @return void
	 */
	public function putFloat(float $v){
		$this->buffer .= Binary::writeFloat($v);
	}

	public function getLFloat() : float{
		return Binary::readLFloat($this->get(4));
	}

	public function getRoundedLFloat(int $accuracy) : float{
		return Binary::readRoundedLFloat($this->get(4), $accuracy);
	}

	/**
	 * @return void
	 */
	public function putLFloat(float $v){
		$this->buffer .= Binary::writeLFloat($v);
	}

	public function getDouble() : float{
		return Binary::readDouble($this->get(8));
	}

	public function putDouble(float $v) : void{
		$this->buffer .= Binary::writeDouble($v);
	}

	public function getLDouble() : float{
		return Binary::readLDouble($this->get(8));
	}

	public function putLDouble(float $v) : void{
		$this->buffer .= Binary::writeLDouble($v);
	}

	public function getLong() : int{
		return Binary::readLong($this->get(8));
	}

	/**
	 * @return void
	 */
	public function putLong(int $v){
		$this->buffer .= Binary::writeLong($v);
	}

	public function getLLong() : int{
		return Binary::readLLong($this->get(8));
	}

	/**
	 * @return void
	 */
	public function putLLong(int $v){
		$this->buffer .= Binary::writeLLong($v);
	}

	/**
	 * Reads a 32-bit variable-length unsigned integer from the buffer and returns it.
	 */
	public function getUnsignedVarInt() : int{
		return Binary::readUnsignedVarInt($this->buffer, $this->offset);
	}

	/**
	 * Writes a 32-bit variable-length unsigned integer to the end of the buffer.
	 *
	 * @return void
	 */
	public function putUnsignedVarInt(int $v){
		$this->put(Binary::writeUnsignedVarInt($v));
	}

	/**
	 * Reads a 32-bit zigzag-encoded variable-length integer from the buffer and returns it.
	 */
	public function getVarInt() : int{
		return Binary::readVarInt($this->buffer, $this->offset);
	}

	/**
	 * Writes a 32-bit zigzag-encoded variable-length integer to the end of the buffer.
	 *
	 * @return void
	 */
	public function putVarInt(int $v){
		$this->put(Binary::writeVarInt($v));
	}

	/**
	 * Reads a 64-bit variable-length integer from the buffer and returns it.
	 */
	public function getUnsignedVarLong() : int{
		return Binary::readUnsignedVarLong($this->buffer, $this->offset);
	}

	/**
	 * Writes a 64-bit variable-length integer to the end of the buffer.
	 *
	 * @return void
	 */
	public function putUnsignedVarLong(int $v){
		$this->buffer .= Binary::writeUnsignedVarLong($v);
	}

	/**
	 * Reads a 64-bit zigzag-encoded variable-length integer from the buffer and returns it.
	 */
	public function getVarLong() : int{
		return Binary::readVarLong($this->buffer, $this->offset);
	}

	/**
	 * Writes a 64-bit zigzag-encoded variable-length integer to the end of the buffer.
	 *
	 * @return void
	 */
	public function putVarLong(int $v){
		$this->buffer .= Binary::writeVarLong($v);
	}

	/**
	 * Returns whether the offset has reached the end of the buffer.
	 */
	public function feof() : bool{
		return !isset($this->buffer[$this->offset]);
	}
}