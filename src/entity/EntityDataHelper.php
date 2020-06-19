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

namespace pocketmine\entity;

use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\world\World;
use function count;

final class EntityDataHelper{

	private function __construct(){
		//NOOP
	}

	public static function parseLocation(CompoundTag $nbt, World $world) : Location{
		$pos = self::parseVec3($nbt, "Pos", false);

		$yawPitch = $nbt->getTag("Rotation");
		if(!($yawPitch instanceof ListTag) or $yawPitch->getTagType() !== NBT::TAG_Float){
			throw new \UnexpectedValueException("'Rotation' should be a List<Float>");
		}
		$values = $yawPitch->getValue();
		if(count($values) !== 2){
			throw new \UnexpectedValueException("Expected exactly 2 entries for 'Rotation'");
		}

		return Location::fromObject($pos, $world, $values[0]->getValue(), $values[1]->getValue());
	}

	public static function parseVec3(CompoundTag $nbt, string $tagName, bool $optional) : Vector3{
		$pos = $nbt->getTag($tagName);
		if($pos === null and $optional){
			return new Vector3(0, 0, 0);
		}
		if(!($pos instanceof ListTag) or $pos->getTagType() !== NBT::TAG_Double){
			throw new \UnexpectedValueException("'$tagName' should be a List<Double>");
		}
		/** @var DoubleTag[] $values */
		$values = $pos->getValue();
		if(count($values) !== 3){
			throw new \UnexpectedValueException("Expected exactly 3 entries in '$tagName' tag");
		}
		return new Vector3($values[0]->getValue(), $values[1]->getValue(), $values[2]->getValue());
	}

	/**
	 * Helper function which creates minimal NBT needed to spawn an entity.
	 */
	public static function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0) : CompoundTag{
		return CompoundTag::create()
			->setTag("Pos", new ListTag([
				new DoubleTag($pos->x),
				new DoubleTag($pos->y),
				new DoubleTag($pos->z)
			]))
			->setTag("Motion", new ListTag([
				new DoubleTag($motion !== null ? $motion->x : 0.0),
				new DoubleTag($motion !== null ? $motion->y : 0.0),
				new DoubleTag($motion !== null ? $motion->z : 0.0)
			]))
			->setTag("Rotation", new ListTag([
				new FloatTag($yaw),
				new FloatTag($pitch)
			]));
	}
}
