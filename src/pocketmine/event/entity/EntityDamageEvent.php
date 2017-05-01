<?php

/**
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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;

/**
 * Called when an entity takes damage.
 */
class EntityDamageEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	const MODIFIER_BASE = 0;
	const MODIFIER_ARMOR = 1;
	const MODIFIER_STRENGTH = 2;
	const MODIFIER_WEAKNESS = 3;
	const MODIFIER_RESISTANCE = 4;
	const MODIFIER_ABSORPTION = 5;

	const CAUSE_CONTACT = 0;
	const CAUSE_ENTITY_ATTACK = 1;
	const CAUSE_PROJECTILE = 2;
	const CAUSE_SUFFOCATION = 3;
	const CAUSE_FALL = 4;
	const CAUSE_FIRE = 5;
	const CAUSE_FIRE_TICK = 6;
	const CAUSE_LAVA = 7;
	const CAUSE_DROWNING = 8;
	const CAUSE_BLOCK_EXPLOSION = 9;
	const CAUSE_ENTITY_EXPLOSION = 10;
	const CAUSE_VOID = 11;
	const CAUSE_SUICIDE = 12;
	const CAUSE_MAGIC = 13;
	const CAUSE_CUSTOM = 14;
	const CAUSE_STARVATION = 15;


	private $cause;
	/** @var array */
	private $modifiers;
	private $originals;


	/**
	 * @param Entity    $entity
	 * @param int       $cause
	 * @param int|int[] $damage
	 *
	 * @throws \Exception
	 */
	public function __construct(Entity $entity, int $cause, $damage){
		$this->entity = $entity;
		$this->cause = $cause;
		if(is_array($damage)){
			$this->modifiers = $damage;
		}else{
			$this->modifiers = [
				self::MODIFIER_BASE => $damage
			];
		}

		$this->originals = $this->modifiers;

		if(!isset($this->modifiers[self::MODIFIER_BASE])){
			throw new \InvalidArgumentException("BASE Damage modifier missing");
		}
	}

	/**
	 * @return int
	 */
	public function getCause() : int{
		return $this->cause;
	}

	/**
	 * @param int $type
	 *
	 * @return int
	 */
	public function getOriginalDamage(int $type = self::MODIFIER_BASE) : int{
		return $this->originals[$type] ?? 0;
	}

	/**
	 * @param int $type
	 *
	 * @return int
	 */
	public function getDamage(int $type = self::MODIFIER_BASE) : int{
		return $this->modifiers[$type] ?? 0;
	}

	/**
	 * @param float $damage
	 * @param int   $type
	 */
	public function setDamage(float $damage, int $type = self::MODIFIER_BASE){
		$this->modifiers[$type] = $damage;
	}

	/**
	 * @param int $type
	 *
	 * @return bool
	 */
	public function isApplicable($type){
		return isset($this->modifiers[$type]);
	}

	/**
	 * @return int
	 */
	public function getFinalDamage(){
		return array_sum($this->modifiers);
	}

	/**
	 * Returns whether this type of damage can be reduced by an entity's armor.
	 * @return bool
	 */
	public function canBeReducedByArmor() : bool{
		switch($this->cause){
			case self::CAUSE_FIRE_TICK:
			case self::CAUSE_SUFFOCATION:
			case self::CAUSE_DROWNING:
			case self::CAUSE_STARVATION:
			case self::CAUSE_FALL:
			case self::CAUSE_VOID:
			case self::CAUSE_MAGIC:
			case self::CAUSE_SUICIDE:
				//TODO: lightning
				return false;
			default:
				return true;
		}
	}

}