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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class CompoundTagMetadataProperty implements MetadataProperty{
	/** @var CompoundTag */
	private $value;

	public function __construct(CompoundTag $value){
		$this->value = clone $value;
	}

	public function getValue() : CompoundTag{
		return clone $this->value;
	}

	public static function id() : int{
		return EntityMetadataTypes::COMPOUND_TAG;
	}

	public function equals(MetadataProperty $other) : bool{
		return $other instanceof self and $other->value->equals($this->value);
	}

	/**
	 * @throws PacketDecodeException
	 */
	public static function read(PacketSerializer $in) : self{
		return new self($in->getNbtCompoundRoot());
	}

	public function write(PacketSerializer $out) : void{
		$out->put((new NetworkNbtSerializer())->write(new TreeRoot($this->value)));
	}
}
