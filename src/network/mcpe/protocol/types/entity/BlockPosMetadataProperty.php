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

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class BlockPosMetadataProperty implements MetadataProperty{

	/** @var Vector3 */
	private $value;

	public function __construct(Vector3 $value){
		$this->value = $value->floor();
	}

	public function getValue() : Vector3{
		return $this->value;
	}

	public static function id() : int{
		return EntityMetadataTypes::POS;
	}

	public static function read(PacketSerializer $in) : self{
		$x = $y = $z = 0;
		$in->getSignedBlockPosition($x, $y, $z);
		return new self(new Vector3($x, $y, $z));
	}

	public function write(PacketSerializer $out) : void{
		$out->putSignedBlockPosition($this->value->x, $this->value->y, $this->value->z);
	}

	public function equals(MetadataProperty $other) : bool{
		return $other instanceof self and $other->value->equals($this->value);
	}
}
