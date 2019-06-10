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

namespace pocketmine\block\tile;

use pocketmine\block\utils\SkullType;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

/**
 * @deprecated
 * @see \pocketmine\block\Skull
 */
class Skull extends Spawnable{

	private const TAG_SKULL_TYPE = "SkullType"; //TAG_Byte
	private const TAG_ROT = "Rot"; //TAG_Byte
	private const TAG_MOUTH_MOVING = "MouthMoving"; //TAG_Byte
	private const TAG_MOUTH_TICK_COUNT = "MouthTickCount"; //TAG_Int

	/** @var SkullType */
	private $skullType;
	/** @var int */
	private $skullRotation = 0;

	public function __construct(World $world, Vector3 $pos){
		$this->skullType = SkullType::SKELETON();
		parent::__construct($world, $pos);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if($nbt->hasTag(self::TAG_SKULL_TYPE, ByteTag::class)){
			try{
				$this->skullType = SkullType::fromMagicNumber($nbt->getByte(self::TAG_SKULL_TYPE));
			}catch(\InvalidArgumentException $e){
				//bad data, drop it
			}
		}
		$rotation = $nbt->getByte(self::TAG_ROT, 0, true);
		if($rotation >= 0 and $rotation <= 15){
			$this->skullRotation = $rotation;
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_SKULL_TYPE, $this->skullType->getMagicNumber());
		$nbt->setByte(self::TAG_ROT, $this->skullRotation);
	}

	public function setSkullType(SkullType $type) : void{
		$this->skullType = $type;
	}

	public function getSkullType() : SkullType{
		return $this->skullType;
	}

	public function getRotation() : int{
		return $this->skullRotation;
	}

	public function setRotation(int $rotation) : void{
		$this->skullRotation = $rotation;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_SKULL_TYPE, $this->skullType->getMagicNumber());
		$nbt->setByte(self::TAG_ROT, $this->skullRotation);
	}
}
