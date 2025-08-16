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

namespace pocketmine\data\bedrock\block\convert;

use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DripleafState;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\MobHeadType;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\utils\SingletonTrait;

final class ValueMappings{
	use SingletonTrait; //???

	/**
	 * @var StringEnumMap[]
	 * @phpstan-var array<class-string<covariant \UnitEnum>, StringEnumMap<covariant \UnitEnum>>
	 */
	private array $enumMappings = [];

	public function __construct(){
		//flattened ID components - we can't generate constants for these
		$this->addEnum(DyeColor::class, fn(DyeColor $case) => match ($case) {
			DyeColor::BLACK => "black",
			DyeColor::BLUE => "blue",
			DyeColor::BROWN => "brown",
			DyeColor::CYAN => "cyan",
			DyeColor::GRAY => "gray",
			DyeColor::GREEN => "green",
			DyeColor::LIGHT_BLUE => "light_blue",
			DyeColor::LIGHT_GRAY => "light_gray",
			DyeColor::LIME => "lime",
			DyeColor::MAGENTA => "magenta",
			DyeColor::ORANGE => "orange",
			DyeColor::PINK => "pink",
			DyeColor::PURPLE => "purple",
			DyeColor::RED => "red",
			DyeColor::WHITE => "white",
			DyeColor::YELLOW => "yellow"
		});

		//full ID mappings - these don't benefit from prefix/suffix generation because of different suffixes, or
		//because they're only used by a single block type
		$this->addEnum(MobHeadType::class, fn(MobHeadType $case) => match($case){
			MobHeadType::CREEPER => Ids::CREEPER_HEAD,
			MobHeadType::DRAGON => Ids::DRAGON_HEAD,
			MobHeadType::PIGLIN => Ids::PIGLIN_HEAD,
			MobHeadType::PLAYER => Ids::PLAYER_HEAD,
			MobHeadType::SKELETON => Ids::SKELETON_SKULL,
			MobHeadType::WITHER_SKELETON => Ids::WITHER_SKELETON_SKULL,
			MobHeadType::ZOMBIE => Ids::ZOMBIE_HEAD
		});
		$this->addEnum(FroglightType::class, fn(FroglightType $case) => match($case){
			FroglightType::OCHRE => Ids::OCHRE_FROGLIGHT,
			FroglightType::PEARLESCENT => Ids::PEARLESCENT_FROGLIGHT,
			FroglightType::VERDANT => Ids::VERDANT_FROGLIGHT,
		});
		$this->addEnum(DirtType::class, fn(DirtType $case) => match($case){
			DirtType::NORMAL => Ids::DIRT,
			DirtType::COARSE => Ids::COARSE_DIRT,
			DirtType::ROOTED => Ids::DIRT_WITH_ROOTS,
		});

		//state value mappings
		$this->addEnum(DripleafState::class, fn(DripleafState $case) => match($case){
			DripleafState::STABLE => StringValues::BIG_DRIPLEAF_TILT_NONE,
			DripleafState::UNSTABLE => StringValues::BIG_DRIPLEAF_TILT_UNSTABLE,
			DripleafState::PARTIAL_TILT => StringValues::BIG_DRIPLEAF_TILT_PARTIAL_TILT,
			DripleafState::FULL_TILT => StringValues::BIG_DRIPLEAF_TILT_FULL_TILT
		});
		$this->addEnum(BellAttachmentType::class, fn(BellAttachmentType $case) => match($case){
			BellAttachmentType::FLOOR => StringValues::ATTACHMENT_STANDING,
			BellAttachmentType::CEILING => StringValues::ATTACHMENT_HANGING,
			BellAttachmentType::ONE_WALL => StringValues::ATTACHMENT_SIDE,
			BellAttachmentType::TWO_WALLS => StringValues::ATTACHMENT_MULTIPLE,
		});
		$this->addEnum(LeverFacing::class, fn(LeverFacing $case) => match($case){
			LeverFacing::DOWN_AXIS_Z => StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH,
			LeverFacing::DOWN_AXIS_X => StringValues::LEVER_DIRECTION_DOWN_EAST_WEST,
			LeverFacing::UP_AXIS_Z => StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH,
			LeverFacing::UP_AXIS_X => StringValues::LEVER_DIRECTION_UP_EAST_WEST,
			LeverFacing::NORTH => StringValues::LEVER_DIRECTION_NORTH,
			LeverFacing::SOUTH => StringValues::LEVER_DIRECTION_SOUTH,
			LeverFacing::WEST => StringValues::LEVER_DIRECTION_WEST,
			LeverFacing::EAST => StringValues::LEVER_DIRECTION_EAST
		});
	}

	/**
	 * @phpstan-template TEnum of \UnitEnum
	 * @phpstan-param class-string<TEnum>     $class
	 * @phpstan-param \Closure(TEnum): string $mapper
	 */
	private function addEnum(string $class, \Closure $mapper) : void{
		$this->enumMappings[$class] = new StringEnumMap($class, $mapper);
	}

	/**
	 * @phpstan-template TEnum of \UnitEnum
	 * @phpstan-param class-string<TEnum> $class
	 * @phpstan-return StringEnumMap<TEnum>
	 */
	public function getEnumMap(string $class) : StringEnumMap{
		if(!isset($this->enumMappings[$class])){
			throw new \InvalidArgumentException("No enum mapping found for class: $class");
		}
		/**
		 * @phpstan-var StringEnumMap<TEnum> $map
		 */
		$map = $this->enumMappings[$class];
		return $map;
	}
}
