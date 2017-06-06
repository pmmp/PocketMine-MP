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


use pocketmine\item\enchantment\Enchantment;

abstract class Tool extends Durable{

	//TODO: fix this mess
	//Block-breaking tools
	const TYPE_NONE = 0;
	const TYPE_SWORD = 1;
	const TYPE_SHOVEL = 2;
	const TYPE_PICKAXE = 3;
	const TYPE_AXE = 4;
	const TYPE_SHEARS = 5;

	//Not a block-breaking tool
	const TYPE_HOE = 6;
	const TYPE_BOW = 7;

	protected $attackPoints;

	protected $maxStackSize = 1;

	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown", int $durability, int $attackPoints = 1){
		$this->attackPoints = $attackPoints;
		parent::__construct($id, $meta, $count, $name, $durability);
	}

	abstract public function getToolType() : int;

	public function getAttackPoints() : float{
		$points = $this->attackPoints;

		if(($ench = $this->getEnchantment(Enchantment::SHARPNESS)) !== null){
			$points += ($ench->getLevel() + 1); //In PC this is multiplied by 0.5
		}

		//TODO: Bane of Arthropods and Smite

		return $points;
	}

	//TODO: remove this mess

	public function isPickaxe(){
		return false;
	}

	public function isAxe(){
		return false;
	}

	public function isSword(){
		return false;
	}

	public function isShovel(){
		return false;
	}

	public function isHoe(){
		return false;
	}

	public function isShears(){
		return false;
	}

	public function isTool(){
		return true;
	}

	protected static function fromJsonTypeData(array $data){
		$properties = $data["properties"] ?? [];
		if(!isset($properties["durability"])){
			throw new \RuntimeException("Missing " . static::class . " properties in supplied data for " . $data["fallback_name"]);
		}
		return new static(
			$data["id"],
			$data["meta"] ?? 0,
			1,
			$data["fallback_name"],
			$properties["durability"],
			$properties["attack_damage"] ?? 1
		);
	}
}