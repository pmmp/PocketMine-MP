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

namespace pocketmine\utils;

class UUID{

	private $parts = [0, 0, 0, 0];
	private $version = null;

	public function __construct($part1 = 0, $part2 = 0, $part3 = 0, $part4 = 0, $version = null){
		$this->parts[0] = (int) $part1;
		$this->parts[1] = (int) $part2;
		$this->parts[2] = (int) $part3;
		$this->parts[3] = (int) $part4;

		$this->version = $version === null ? ($this->parts[1] & 0xf000) >> 12 : (int) $version;
	}

	public function getVersion(){
		return $this->version;
	}

	public function equals(UUID $uuid){
		return $uuid->parts[0] === $this->parts[0] and $uuid->parts[1] === $this->parts[1] and $uuid->parts[2] === $this->parts[2] and $uuid->parts[3] === $this->parts[3];
	}

	/**
	 * Creates an UUID from an hexadecimal representation
	 *
	 * @param string $uuid
	 * @param int    $version
	 * @return UUID
	 */
	public static function fromString($uuid, $version = null){
		return self::fromBinary(hex2bin(str_replace("-", "", trim($uuid))), $version);
	}

	/**
	 * Creates an UUID from a binary representation
	 *
	 * @param string $uuid
	 * @param int    $version
	 * @return UUID
	 */
	public static function fromBinary($uuid, $version = null){
		if(strlen($uuid) !== 16){
			throw new \InvalidArgumentException("Must have exactly 16 bytes");
		}

		return new UUID(Binary::readInt(substr($uuid, 0, 4)), Binary::readInt(substr($uuid, 4, 4)), Binary::readInt(substr($uuid, 8, 4)), Binary::readInt(substr($uuid, 12, 4)), $version);
	}

	/**
	 * Creates an UUIDv3 from binary data or list of binary data
	 *
	 * @param string ...$data
	 * @return UUID
	 */
	public static function fromData(...$data){
		$hash = hash("md5", implode($data), true);

		return self::fromBinary($hash, 3);
	}

	public static function fromRandom(){
		return self::fromData(Binary::writeInt(time()), Binary::writeShort(getmypid()), Binary::writeShort(getmyuid()), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)));
	}

	public function toBinary(){
		return Binary::writeInt($this->parts[0]) . Binary::writeInt($this->parts[1]) . Binary::writeInt($this->parts[2]) . Binary::writeInt($this->parts[3]);
	}

	public function toString(){
		$hex = bin2hex(self::toBinary());

		//xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx 8-4-4-12
		if($this->version !== null){
			return substr($hex, 0, 8) . "-" . substr($hex, 8, 4) . "-" . hexdec($this->version) . substr($hex, 13, 3) . "-8" . substr($hex, 17, 3) . "-" . substr($hex, 20, 12);
		}
		return substr($hex, 0, 8) . "-" . substr($hex, 8, 4) . "-" . substr($hex, 12, 4) . "-" . substr($hex, 16, 4) . "-" . substr($hex, 20, 12);
	}

	public function __toString(){
		return $this->toString();
	}
}