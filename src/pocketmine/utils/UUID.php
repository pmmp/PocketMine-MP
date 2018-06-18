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

class UUID{

	private $parts = [0, 0, 0, 0];
	private $version = null;

	public function __construct(int $part1 = 0, int $part2 = 0, int $part3 = 0, int $part4 = 0, int $version = null){
		$this->parts = [$part1, $part2, $part3, $part4];

		$this->version = $version ?? ($this->parts[1] & 0xf000) >> 12;
	}

	public function getVersion() : int{
		return $this->version;
	}

	public function equals(UUID $uuid) : bool{
		return $uuid->parts === $this->parts;
	}

	/**
	 * Creates an UUID from an hexadecimal representation
	 *
	 * @param string $uuid
	 * @param int    $version
	 * @return UUID
	 */
	public static function fromString(string $uuid, int $version = null) : UUID{
		return self::fromBinary(hex2bin(str_replace("-", "", trim($uuid))), $version);
	}

	/**
	 * Creates an UUID from a binary representation
	 *
	 * @param string $uuid
	 * @param int    $version
	 * @return UUID
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function fromBinary(string $uuid, int $version = null) : UUID{
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
	public static function fromData(string ...$data) : UUID{
		$hash = hash("md5", implode($data), true);

		return self::fromBinary($hash, 3);
	}

	public static function fromRandom() : UUID{
		return self::fromData(Binary::writeInt(time()), Binary::writeShort(getmypid()), Binary::writeShort(getmyuid()), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)), Binary::writeInt(mt_rand(-0x7fffffff, 0x7fffffff)));
	}

	public function toBinary() : string{
		return Binary::writeInt($this->parts[0]) . Binary::writeInt($this->parts[1]) . Binary::writeInt($this->parts[2]) . Binary::writeInt($this->parts[3]);
	}

	public function toString() : string{
		$hex = bin2hex($this->toBinary());

		//xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx 8-4-4-4-12
		return substr($hex, 0, 8) . "-" . substr($hex, 8, 4) . "-" . substr($hex, 12, 4) . "-" . substr($hex, 16, 4) . "-" . substr($hex, 20, 12);
	}

	public function __toString() : string{
		return $this->toString();
	}

	public function getPart(int $partNumber){
		if($partNumber < 0 or $partNumber > 3){
			throw new \InvalidArgumentException("Invalid UUID part index $partNumber");
		}
		return $this->parts[$partNumber];
	}

	public function getParts() : array{
		return $this->parts;
	}
}
