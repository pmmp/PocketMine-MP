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


namespace pocketmine\item;


abstract class TieredTool extends Tool{

	const TIER_ANY = 0;
	const TIER_WOODEN = 1;
	const TIER_GOLD = 2;
	const TIER_STONE = 3;
	const TIER_IRON = 4;
	const TIER_DIAMOND = 5;

	protected $tier;

	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown", int $tier, int $durability, int $attackPoints = 1){
		parent::__construct($id, $meta, $count, $name, $durability, $attackPoints);
		$this->tier = $tier;
	}

	public function getToolTier() : int{
		return $this->tier;
	}

	/**
	 * Returns an integer tool tier number from a string tier name.
	 *
	 * @param string $name
	 * @return int
	 *
	 * @throw \InvalidArgumentException if an unknown name is given
	 */
	public static function toolTierFromString(string $name) : int{
		switch($name){
			case "diamond":
				return self::TIER_DIAMOND;
			case "golden":
			case "gold":
				return self::TIER_GOLD;
			case "iron":
				return self::TIER_IRON;
			case "stone":
				return self::TIER_STONE;
			case "wood":
			case "wooden":
				return self::TIER_WOODEN;
			default:
				throw new \InvalidArgumentException("Unknown tool tier type $name");
		}
	}

	protected static function fromJsonTypeData(array $data){
		$properties = $data["properties"] ?? [];
		if(!isset($properties["durability"]) or !isset($properties["tier"])){
			throw new \RuntimeException("Missing " . static::class . " properties from supplied data for " . $data["fallback_name"]);
		}

		return new static(
			$data["id"],
			$data["meta"] ?? 0,
			1,
			$data["fallback_name"],
			TieredTool::toolTierFromString($properties["tier"]),
			$properties["durability"],
			$properties["attack_damage"] ?? 1
		);
	}
}