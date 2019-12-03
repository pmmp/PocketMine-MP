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

namespace pocketmine\network\mcpe\protocol\types\entity;

use pocketmine\network\mcpe\serializer\NetworkBinaryStream;

final class StringMetadataProperty implements MetadataProperty{
	/** @var string */
	private $value;

	/**
	 * @param string $value
	 */
	public function __construct(string $value){
		$this->value = $value;
	}

	public static function id() : int{
		return EntityMetadataTypes::STRING;
	}

	public static function read(NetworkBinaryStream $in) : self{
		return new self($in->getString());
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->value);
	}

	public function equals(MetadataProperty $other) : bool{
		return $other instanceof self and $other->value === $this->value;
	}
}
