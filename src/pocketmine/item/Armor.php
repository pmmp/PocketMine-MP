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

use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\Color;

abstract class Armor extends Item{
	const TIER_LEATHER = 1;
	const TIER_GOLDEN = 2;
	const TIER_CHAIN = 3;
	const TIER_IRON = 4;
	const TIER_DIAMOND = 5;

	protected $maxStackSize = 1;

	protected $defensePoints;
	protected $durability;
	protected $tier;

	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown", int $tier, int $durability, int $defensePoints){
		parent::__construct($id, $meta, $count, $name);
		$this->tier = $tier;
		$this->durability = $durability;
		$this->defensePoints = $defensePoints;
	}

	/**
	 * Returns the amount of defense this armour piece provides.
	 * @return int
	 */
	public function getDefensePoints() : int{
		return $this->defensePoints;
	}

	/**
	 * Returns the armor tier (leather, gold, chain, iron, diamond)
	 * @return int
	 */
	public function getArmorTier() : int{
		return $this->tier;
	}

	public function getMaxDurability() : int{
		return $this->durability;
	}

	/**
	 * Returns the custom colour of the armour item, if it has one. This generally only applies to leather armour.
	 * @return Color|null
	 */
	public function getCustomColor(){
		$tag = $this->getNamedTagEntry("customColor");
		if($tag instanceof IntTag){
			return Color::fromARGB($tag->getValue());
		}
		return null;
	}

	/**
	 * Sets the custom colour of the armour item.
	 * @param Color $color
	 */
	public function setCustomColor(Color $color){
		$tag = $this->getNamedTag();
		$tag->customColor = new IntTag("customColor", $color->toARGB());
		$this->setNamedTag($tag);
	}

	protected static function fromJsonTypeData(array $data){
		$properties = $data["properties"] ?? [];
		if(!isset($properties["durability"]) or !isset($properties["defense_points"]) or !isset($properties["tier"])){
			throw new \RuntimeException("Missing " . static::class . " properties in supplied data for " . $data["fallback_name"]);
		}

		return new static(
			$data["id"],
			$data["meta"] ?? 0,
			1,
			$data["fallback_name"],
			Armor::armorTierFromString($properties["tier"]),
			$properties["durability"],
			$properties["defense_points"]
		);
	}

	/**
	 * Returns an int armor tier from a string tier name.
	 *
	 * @param string $name
	 * @return int
	 *
	 * @throws \InvalidArgumentException if the name could not be converted to a tier constant
	 */
	public static function armorTierFromString(string $name) : int{
		switch($name){
			case "leather":
				return Armor::TIER_LEATHER;
			case "gold":
			case "golden":
				return Armor::TIER_GOLDEN;
			case "chain":
			case "chainmail":
				return Armor::TIER_CHAIN;
			case "iron":
				return Armor::TIER_IRON;
			case "diamond":
				return Armor::TIER_DIAMOND;
			default:
				throw new \InvalidArgumentException("Unknown armor tier \"$name\"");
		}
	}
}