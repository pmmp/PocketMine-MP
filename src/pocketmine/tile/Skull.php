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
	public const TYPE_SKELETON = 0;
	public const TYPE_WITHER = 1;
	public const TYPE_ZOMBIE = 2;
	public const TYPE_HUMAN = 3;
	public const TYPE_CREEPER = 4;
	public const TYPE_DRAGON = 5;

	public const TAG_SKULL_TYPE = "SkullType"; //TAG_Byte
	public const TAG_ROT = "Rot"; //TAG_Byte
	public const TAG_MOUTH_MOVING = "MouthMoving"; //TAG_Byte
	public const TAG_MOUTH_TICK_COUNT = "MouthTickCount"; //TAG_Int

	public function __construct(Level $level, CompoundTag $nbt){
		if(!$nbt->hasTag(self::TAG_SKULL_TYPE, ByteTag::class)){
			$nbt->setByte(self::TAG_SKULL_TYPE, 0, true);
		}
		if(!$nbt->hasTag(self::TAG_ROT, ByteTag::class)){
			$nbt->setByte(self::TAG_ROT, 0, true);
		}
		parent::__construct($level, $nbt);
	}

	public function setType(int $type){
		$this->namedtag->setByte(self::TAG_SKULL_TYPE, $type);
		$this->onChanged();
	}

	public function getType() : int{
		return $this->namedtag->getByte(self::TAG_SKULL_TYPE);
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setTag($this->namedtag->getTag(self::TAG_SKULL_TYPE));
		$nbt->setTag($this->namedtag->getTag(self::TAG_ROT));
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->setByte(self::TAG_SKULL_TYPE, $item !== null ? $item->getDamage() : self::TYPE_SKELETON);

		$rot = 0;
		if($face === Vector3::SIDE_UP and $player !== null){
			$rot = floor(($player->yaw * 16 / 360) + 0.5) & 0x0F;
		}
		$nbt->setByte("Rot", $rot);
	}
}
