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

namespace pocketmine\player\camera\instruction;

use pocketmine\data\bedrock\CameraEaseTypeIdMap;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\camera\CameraPreset;
use pocketmine\player\camera\CameraPresetFactory;
use pocketmine\player\camera\element\CameraEase;
use pocketmine\player\camera\element\CameraState;

class SetCameraInstruction implements CameraInstruction{

	private const TAG_SET = "set"; //TAG_Compound

	private const TAG_PRESET = "preset"; //TAG_Int

	private const TAG_POSITION = "pos"; //TAG_Compound & TAG_List<TAG_Float>

	private const TAG_ROTATION = "rot"; //TAG_Compound
	private const TAG_ROTATION_YAW = "y"; //TAG_Float
	private const TAG_ROTATION_PITCH = "x"; //TAG_Float

	private const TAG_EASE = "ease"; //TAG_Compound
	private const TAG_EASE_TYPE = "type"; //TAG_String
	private const TAG_EASE_DURATION = "time"; //TAG_Float

	public function __construct(
		private CameraPreset $preset,
		private ?CameraState $state = null,
		private ?CameraEase $ease = null
	) {
	}

	public function writeInstructionData(CompoundTag $tag) : void{
		$setTag = CompoundTag::create();

		if ($this->state !== null) {
			if (($position = $this->state->getPosition()) !== null) {
				$setTag->setTag(self::TAG_POSITION, CompoundTag::create() //why use double position tag? mojang...
					->setTag(self::TAG_POSITION, new ListTag([
						new FloatTag($position->x),
						new FloatTag($position->y),
						new FloatTag($position->z)
					]))
				);
			}
			if (($yaw = $this->state->getYaw()) !== null && ($pitch = $this->state->getPitch()) !== null) {
				$setTag->setTag(self::TAG_ROTATION, CompoundTag::create()
					->setFloat(self::TAG_ROTATION_YAW, $yaw)
					->setFloat(self::TAG_ROTATION_PITCH, $pitch)
				);
			}
		}

		if ($this->ease !== null) {
			$setTag->setTag(self::TAG_EASE, CompoundTag::create()
				->setFloat(self::TAG_EASE_DURATION, $this->ease->getDuration())
				->setString(self::TAG_EASE_TYPE, CameraEaseTypeIdMap::getInstance()->toId($this->ease->getType()))
			);
		}

		$setTag->setInt(self::TAG_PRESET, CameraPresetFactory::getInstance()->getRuntimeId($this->preset->getIdentifier()));

		$tag->setTag(self::TAG_SET, $setTag);
	}
}
