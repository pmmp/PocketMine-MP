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

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;

class Food extends Item implements FoodSource{

	/** @var int */
	protected $foodRestore;
	/** @var float */
	protected $saturationRestore;
	/** @var bool */
	protected $requiresHunger;
	/** @var ItemChanceEffect[] */
	protected $effects = [];
	/** @var string */
	protected $result;

	/**
	 * @param int      $id
	 * @param int      $meta
	 * @param int      $count
	 * @param string   $name
	 * @param int      $nutrition How much hunger the food type will restore when eaten.
	 * @param float    $saturation How much saturation this food type will give the eater.
	 * @param bool     $requiresHunger Whether this food type can be eaten with a full hunger bar.
	 * @param Effect[] $effects Effects to apply to the eater.
	 * @param string   $result Unique name ID of the result item that the food changes into after being eaten. This may produce peculiar behaviour for items that have a stack size greater than 1.
	 */
	public function __construct(int $id, int $meta = 0, int $count = 1, string $name = "Unknown", int $nutrition, float $saturation, bool $requiresHunger = true, array $effects = [], string $result = "air"){
		parent::__construct($id, $meta, $count, $name);
		$this->foodRestore = $nutrition;
		$this->saturationRestore = $saturation;
		$this->requiresHunger = $requiresHunger;
		$this->effects = $effects;
		$this->result = $result;
	}

	protected static function fromJsonTypeData(array $data){
		$properties = $data["properties"] ?? [];

		if(!isset($properties["nutrition"]) or !isset($properties["saturation"])){
			throw new \RuntimeException("Food properties missing from supplied data for " . $data["fallback_name"]);
		}

		/** @var ItemChanceEffect[] $effects */
		$effects = [];
		if(isset($properties["effects"])){
			foreach($properties["effects"] as $effectData){
				//This will skip any effects it doesn't recognize.
				$newEffect = Effect::fromJsonData($effectData);
				if($newEffect instanceof Effect){
					$effects[] = new ItemChanceEffect($newEffect, $effectData["chance"] ?? 1.0);
				}else{
					continue;
				}
			}
		}
		$result = $properties["using_converts_to"] ?? "air";

		return new static(
			$data["id"],
			$data["meta"] ?? 0,
			1,
			$data["fallback_name"],
			$properties["nutrition"],
			$properties["saturation"],
			$properties["requires_hunger"] ?? true,
			$effects,
			$result
		);
	}

	public function canBeConsumed() : bool{
		return true;
	}

	public function canBeConsumedBy(Entity $entity) : bool{
		return $entity instanceof Human and (!$this->requiresHunger or ($entity->getFood() < $entity->getMaxFood()));
	}

	public function getFoodRestore() : int{
		return $this->foodRestore;
	}

	public function getSaturationRestore() : float{
		return $this->saturationRestore;
	}

	public function requiresHunger() : bool{
		return $this->requiresHunger;
	}

	/**
	 * Returns the result item from eating this food type.
	 *
	 * @return Item
	 */
	public function getResidue(){
		if($this->result !== "air"){
			return Item::fromString($this->result);
		}elseif($this->count === 1){
			return Item::get(Item::AIR, 0, 0);
		}else{
			$new = clone $this;
			$new->count--;
			return $new;
		}
	}

	public function getAdditionalEffects() : array{
		$effects = [];
		foreach($this->effects as $chanceEffect){
			if($chanceEffect->shouldApply()){
				$effects[] = $chanceEffect->getEffect();
			}
		}

		return $effects;
	}

	public function onConsume(Entity $human){

	}

	public function __clone(){
		foreach($this->effects as $i => $effect){
			/** @var Effect $effect */
			$this->effects[$i] = clone $effect;
		}
	}
}
