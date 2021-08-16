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

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\world\World;
use function max;
use function min;

class HungerManager{

	/** @var Human */
	private $entity;

	/** @var Attribute */
	private $hungerAttr;
	/** @var Attribute */
	private $saturationAttr;
	/** @var Attribute */
	private $exhaustionAttr;

	/** @var int */
	private $foodTickTimer = 0;

	/** @var bool */
	private $enabled = true;

	public function __construct(Human $entity){
		$this->entity = $entity;

		$this->hungerAttr = self::fetchAttribute($entity, Attribute::HUNGER);
		$this->saturationAttr = self::fetchAttribute($entity, Attribute::SATURATION);
		$this->exhaustionAttr = self::fetchAttribute($entity, Attribute::EXHAUSTION);
	}

	private static function fetchAttribute(Entity $entity, string $attributeId) : Attribute{
		$attribute = AttributeFactory::getInstance()->mustGet($attributeId);
		$entity->getAttributeMap()->add($attribute);
		return $attribute;
	}

	public function getFood() : float{
		return $this->hungerAttr->getValue();
	}

	/**
	 * WARNING: This method does not check if full and may throw an exception if out of bounds.
	 * @see HungerManager::addFood()
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setFood(float $new) : void{
		$old = $this->hungerAttr->getValue();
		$this->hungerAttr->setValue($new);

		// ranges: 18-20 (regen), 7-17 (none), 1-6 (no sprint), 0 (health depletion)
		foreach([17, 6, 0] as $bound){
			if(($old > $bound) !== ($new > $bound)){
				$this->foodTickTimer = 0;
				break;
			}
		}
	}

	public function getMaxFood() : float{
		return $this->hungerAttr->getMaxValue();
	}

	public function addFood(float $amount) : void{
		$amount += $this->hungerAttr->getValue();
		$amount = max(min($amount, $this->hungerAttr->getMaxValue()), $this->hungerAttr->getMinValue());
		$this->setFood($amount);
	}

	/**
	 * Returns whether this Human may consume objects requiring hunger.
	 */
	public function isHungry() : bool{
		return $this->getFood() < $this->getMaxFood();
	}

	public function getSaturation() : float{
		return $this->saturationAttr->getValue();
	}

	/**
	 * WARNING: This method does not check if saturated and may throw an exception if out of bounds.
	 * @see HungerManager::addSaturation()
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setSaturation(float $saturation) : void{
		$this->saturationAttr->setValue($saturation);
	}

	public function addSaturation(float $amount) : void{
		$this->saturationAttr->setValue($this->saturationAttr->getValue() + $amount, true);
	}

	public function getExhaustion() : float{
		return $this->exhaustionAttr->getValue();
	}

	/**
	 * WARNING: This method does not check if exhausted and does not consume saturation/food.
	 * @see HungerManager::exhaust()
	 */
	public function setExhaustion(float $exhaustion) : void{
		$this->exhaustionAttr->setValue($exhaustion);
	}

	/**
	 * Increases exhaustion level.
	 *
	 * @return float the amount of exhaustion level increased
	 */
	public function exhaust(float $amount, int $cause = PlayerExhaustEvent::CAUSE_CUSTOM) : float{
		if(!$this->enabled){
			return 0;
		}
		$ev = new PlayerExhaustEvent($this->entity, $amount, $cause);
		$ev->call();
		if($ev->isCancelled()){
			return 0.0;
		}

		$exhaustion = $this->getExhaustion();
		$exhaustion += $ev->getAmount();

		while($exhaustion >= 4.0){
			$exhaustion -= 4.0;

			$saturation = $this->getSaturation();
			if($saturation > 0){
				$saturation = max(0, $saturation - 1.0);
				$this->setSaturation($saturation);
			}else{
				$food = $this->getFood();
				if($food > 0){
					$food--;
					$this->setFood(max($food, 0));
				}
			}
		}
		$this->setExhaustion($exhaustion);

		return $ev->getAmount();
	}

	public function getFoodTickTimer() : int{
		return $this->foodTickTimer;
	}

	public function setFoodTickTimer(int $foodTickTimer) : void{
		if($foodTickTimer < 0){
			throw new \InvalidArgumentException("Expected a non-negative value");
		}
		$this->foodTickTimer = $foodTickTimer;
	}

	public function tick(int $tickDiff = 1) : void{
		if(!$this->entity->isAlive() or !$this->enabled){
			return;
		}
		$food = $this->getFood();
		$health = $this->entity->getHealth();
		$difficulty = $this->entity->getWorld()->getDifficulty();

		$this->foodTickTimer += $tickDiff;
		if($this->foodTickTimer >= 80){
			$this->foodTickTimer = 0;
		}

		if($difficulty === World::DIFFICULTY_PEACEFUL and $this->foodTickTimer % 10 === 0){
			if($food < $this->getMaxFood()){
				$this->addFood(1.0);
				$food = $this->getFood();
			}
			if($this->foodTickTimer % 20 === 0 and $health < $this->entity->getMaxHealth()){
				$this->entity->heal(new EntityRegainHealthEvent($this->entity, 1, EntityRegainHealthEvent::CAUSE_SATURATION));
			}
		}

		if($this->foodTickTimer === 0){
			if($food >= 18){
				if($health < $this->entity->getMaxHealth()){
					$this->entity->heal(new EntityRegainHealthEvent($this->entity, 1, EntityRegainHealthEvent::CAUSE_SATURATION));
					$this->exhaust(3.0, PlayerExhaustEvent::CAUSE_HEALTH_REGEN);
				}
			}elseif($food <= 0){
				if(($difficulty === World::DIFFICULTY_EASY and $health > 10) or ($difficulty === World::DIFFICULTY_NORMAL and $health > 1) or $difficulty === World::DIFFICULTY_HARD){
					$this->entity->attack(new EntityDamageEvent($this->entity, EntityDamageEvent::CAUSE_STARVATION, 1));
				}
			}
		}

		if($food <= 6){
			$this->entity->setSprinting(false);
		}
	}

	public function isEnabled() : bool{
		return $this->enabled;
	}

	public function setEnabled(bool $enabled) : void{
		$this->enabled = $enabled;
	}
}
