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

use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\MobHeadType;
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
		$this->addEnum(MobHeadType::class, fn(MobHeadType $case) => match($case){
			MobHeadType::CREEPER => Ids::CREEPER_HEAD,
			MobHeadType::DRAGON => Ids::DRAGON_HEAD,
			MobHeadType::PIGLIN => Ids::PIGLIN_HEAD,
			MobHeadType::PLAYER => Ids::PLAYER_HEAD,
			MobHeadType::SKELETON => Ids::SKELETON_SKULL,
			MobHeadType::WITHER_SKELETON => Ids::WITHER_SKELETON_SKULL,
			MobHeadType::ZOMBIE => Ids::ZOMBIE_HEAD
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
