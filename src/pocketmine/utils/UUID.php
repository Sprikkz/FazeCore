<?php



namespace pocketmine\utils;

class UUID {

	private $parts;
	private $version;

	/**
	 * UUID constructor.
	 *
	 * @param int  $part1
	 * @param int  $part2
	 * @param int  $part3
	 * @param int  $part4
	 * @param null $version
	 */
	public function __construct($part1 = 0, $part2 = 0, $part3 = 0, $part4 = 0, $version = null){
		$this->parts[0] = (int) $part1;
		$this->parts[1] = (int) $part2;
		$this->parts[2] = (int) $part3;
		$this->parts[3] = (int) $part4;

		$this->version = $version === null ? ($this->parts[1] & 0xf000) >> 12 : (int) $version;
	}

	/**
	 * @return int|null
	 */
	public function getVersion(){
		return $this->version;
	}

	/**
	 * @param UUID $uuid
	 *
	 * @return bool
	 */
	public function equals(UUID $uuid){
		return $uuid->parts === $this->parts;
	}

	/**
	 * Creates a UUID from a hexadecimal representation
	 *
	 * @param string $uuid
	 * @param int    $version
	 *
	 * @return UUID
	 */
	public static function fromString($uuid, $version = null){
		//TODO: should we be stricter about the notation (8-4-4-4-12)?
		$binary = @hex2bin(str_replace("-", "", trim($uuid)));
		if($binary === false){
			throw new \InvalidArgumentException("Invalid UUID representation in hexadecimal string");
		}
		return self::fromBinary($binary, $version);
	}

	/**
	 * Creates a UUID from a binary representation
	 *
	 * @param string $uuid
	 * @param int    $version
	 *
	 * @return UUID
	 */
	public static function fromBinary($uuid, $version = null){
		if(strlen($uuid) !== 16){
			throw new \InvalidArgumentException("Must be exactly 16 bytes");
		}

		return new UUID(Binary::readInt(substr($uuid, 0, 4)), Binary::readInt(substr($uuid, 4, 4)), Binary::readInt(substr($uuid, 8, 4)), Binary::readInt(substr($uuid, 12, 4)), $version);
	}

	/**
	 * Creates a UUIDv3 from binary data or a list of binary data.
	 *
	 * @param array|string ...$data
	 *
	 * @return UUID
	 */
	public static function fromData(...$data){
		$hash = hash("md5", implode($data), true);

		return self::fromBinary($hash, 3);
	}

	/**
	 * @return UUID
	 */
	public static function fromRandom(){
		return self::fromData(Binary::writeInt(time()), Binary::writeShort(getmypid()), Binary::writeShort(getmyuid()), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)));
	}

	/**
	 * @return string
	 */
	public function toBinary(){
		return Binary::writeInt($this->parts[0]) . Binary::writeInt($this->parts[1]) . Binary::writeInt($this->parts[2]) . Binary::writeInt($this->parts[3]);
	}

	/**
	 * @return string
	 */
	public function toString(){
		$hex = bin2hex($this->toBinary());

		//xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx 8-4-4-12
		return substr($hex, 0, 8) . "-" . substr($hex, 8, 4) . "-" . substr($hex, 12, 4) . "-" . substr($hex, 16, 4) . "-" . substr($hex, 20, 12);
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return $this->toString();
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function getPart(int $partNumber) : int{
		if($partNumber < 0 or $partNumber > 3){
			throw new \InvalidArgumentException("Invalid UUID part index $partNumber");
		}
		return $this->parts[$partNumber];
	}

	/**
	 * @return int[]
	 */
	public function getParts() : array{
		return $this->parts;
	}
}