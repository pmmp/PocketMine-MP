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

namespace pocketmine\data\bedrock;

use pocketmine\item\PotionType;
use pocketmine\utils\SingletonTrait;

final class PotionTypeIdMap{
	use SingletonTrait;

	/**
	 * @var PotionType[]
	 * @phpstan-var array<int, PotionType>
	 */
	private array $idToEnum;

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId;

	private function __construct(){
		$this->register(PotionTypeIds::WATER, PotionType::WATER());
		$this->register(PotionTypeIds::MUNDANE, PotionType::MUNDANE());
		$this->register(PotionTypeIds::LONG_MUNDANE, PotionType::LONG_MUNDANE());
		$this->register(PotionTypeIds::THICK, PotionType::THICK());
		$this->register(PotionTypeIds::AWKWARD, PotionType::AWKWARD());
		$this->register(PotionTypeIds::NIGHT_VISION, PotionType::NIGHT_VISION());
		$this->register(PotionTypeIds::LONG_NIGHT_VISION, PotionType::LONG_NIGHT_VISION());
		$this->register(PotionTypeIds::INVISIBILITY, PotionType::INVISIBILITY());
		$this->register(PotionTypeIds::LONG_INVISIBILITY, PotionType::LONG_INVISIBILITY());
		$this->register(PotionTypeIds::LEAPING, PotionType::LEAPING());
		$this->register(PotionTypeIds::LONG_LEAPING, PotionType::LONG_LEAPING());
		$this->register(PotionTypeIds::STRONG_LEAPING, PotionType::STRONG_LEAPING());
		$this->register(PotionTypeIds::FIRE_RESISTANCE, PotionType::FIRE_RESISTANCE());
		$this->register(PotionTypeIds::LONG_FIRE_RESISTANCE, PotionType::LONG_FIRE_RESISTANCE());
		$this->register(PotionTypeIds::SWIFTNESS, PotionType::SWIFTNESS());
		$this->register(PotionTypeIds::LONG_SWIFTNESS, PotionType::LONG_SWIFTNESS());
		$this->register(PotionTypeIds::STRONG_SWIFTNESS, PotionType::STRONG_SWIFTNESS());
		$this->register(PotionTypeIds::SLOWNESS, PotionType::SLOWNESS());
		$this->register(PotionTypeIds::LONG_SLOWNESS, PotionType::LONG_SLOWNESS());
		$this->register(PotionTypeIds::WATER_BREATHING, PotionType::WATER_BREATHING());
		$this->register(PotionTypeIds::LONG_WATER_BREATHING, PotionType::LONG_WATER_BREATHING());
		$this->register(PotionTypeIds::HEALING, PotionType::HEALING());
		$this->register(PotionTypeIds::STRONG_HEALING, PotionType::STRONG_HEALING());
		$this->register(PotionTypeIds::HARMING, PotionType::HARMING());
		$this->register(PotionTypeIds::STRONG_HARMING, PotionType::STRONG_HARMING());
		$this->register(PotionTypeIds::POISON, PotionType::POISON());
		$this->register(PotionTypeIds::LONG_POISON, PotionType::LONG_POISON());
		$this->register(PotionTypeIds::STRONG_POISON, PotionType::STRONG_POISON());
		$this->register(PotionTypeIds::REGENERATION, PotionType::REGENERATION());
		$this->register(PotionTypeIds::LONG_REGENERATION, PotionType::LONG_REGENERATION());
		$this->register(PotionTypeIds::STRONG_REGENERATION, PotionType::STRONG_REGENERATION());
		$this->register(PotionTypeIds::STRENGTH, PotionType::STRENGTH());
		$this->register(PotionTypeIds::LONG_STRENGTH, PotionType::LONG_STRENGTH());
		$this->register(PotionTypeIds::STRONG_STRENGTH, PotionType::STRONG_STRENGTH());
		$this->register(PotionTypeIds::WEAKNESS, PotionType::WEAKNESS());
		$this->register(PotionTypeIds::LONG_WEAKNESS, PotionType::LONG_WEAKNESS());
		$this->register(PotionTypeIds::WITHER, PotionType::WITHER());
		$this->register(PotionTypeIds::TURTLE_MASTER, PotionType::TURTLE_MASTER());
		$this->register(PotionTypeIds::LONG_TURTLE_MASTER, PotionType::LONG_TURTLE_MASTER());
		$this->register(PotionTypeIds::STRONG_TURTLE_MASTER, PotionType::STRONG_TURTLE_MASTER());
		$this->register(PotionTypeIds::SLOW_FALLING, PotionType::SLOW_FALLING());
		$this->register(PotionTypeIds::LONG_SLOW_FALLING, PotionType::LONG_SLOW_FALLING());
	}

	private function register(int $id, PotionType $type) : void{
		$this->idToEnum[$id] = $type;
		$this->enumToId[$type->id()] = $id;
	}

	public function fromId(int $id) : ?PotionType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(PotionType $type) : int{
		if(!isset($this->enumToId[$type->id()])){
			throw new \InvalidArgumentException("Type does not have a mapped ID");
		}
		return $this->enumToId[$type->id()];
	}
}
