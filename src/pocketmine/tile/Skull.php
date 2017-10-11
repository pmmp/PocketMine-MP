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

namespace pocketmine\tile;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class Skull extends Spawnable{
	const TYPE_SKELETON = 0;
	const TYPE_WITHER = 1;
	const TYPE_ZOMBIE = 2;
	const TYPE_HUMAN = 3;
	const TYPE_CREEPER = 4;
	const TYPE_DRAGON = 5;

	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->SkullType)){
			$nbt->SkullType = new ByteTag("SkullType", 0);
		}
		if(!isset($nbt->Rot)){
			$nbt->Rot = new ByteTag("Rot", 0);
		}
		parent::__construct($level, $nbt);
	}

	public function setType(int $type){
		$this->namedtag->SkullType->setValue($type);
		$this->onChanged();
	}

	public function getType() : int{
		return $this->namedtag->SkullType->getValue();
	}

	public function addAdditionalSpawnData(CompoundTag $nbt){
		$nbt->SkullType = $this->namedtag->SkullType;
		$nbt->Rot = $this->namedtag->Rot;
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->SkullType = new ByteTag("SkullType", $item !== null ? $item->getDamage() : self::TYPE_SKELETON);

		$rot = 0;
		if($face === Vector3::SIDE_UP and $player !== null){
			$rot = floor(($player->yaw * 16 / 360) + 0.5) & 0x0F;
		}
		$nbt->Rot = new ByteTag("Rot", $rot);
	}
}