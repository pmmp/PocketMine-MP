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

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;

/**
 * Called when an entity takes damage.
 */
class EntityDamageEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	public const MODIFIER_BASE = 0;
	public const MODIFIER_ARMOR = 1;
	public const MODIFIER_STRENGTH = 2;
	public const MODIFIER_WEAKNESS = 3;
	public const MODIFIER_RESISTANCE = 4;
	public const MODIFIER_ABSORPTION = 5;
	public const MODIFIER_ARMOR_ENCHANTMENTS = 6;

	public const CAUSE_CONTACT = 0;
	public const CAUSE_ENTITY_ATTACK = 1;
	public const CAUSE_PROJECTILE = 2;
	public const CAUSE_SUFFOCATION = 3;
	public const CAUSE_FALL = 4;
	public const CAUSE_FIRE = 5;
	public const CAUSE_FIRE_TICK = 6;
	public const CAUSE_LAVA = 7;
	public const CAUSE_DROWNING = 8;
	public const CAUSE_BLOCK_EXPLOSION = 9;
	public const CAUSE_ENTITY_EXPLOSION = 10;
	public const CAUSE_VOID = 11;
	public const CAUSE_SUICIDE = 12;
	public const CAUSE_MAGIC = 13;
	public const CAUSE_CUSTOM = 14;
	public const CAUSE_STARVATION = 15;

	/** @var int */
	private $cause;
	/** @var float[] */
	private $modifiers;
	/** @var float[] */
	private $originals;


	/**
	 * @param Entity        $entity
	 * @param int           $cause
	 * @param float|float[] $damage
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
	 * @return float
	 */
	public function getOriginalDamage(int $type = self::MODIFIER_BASE) : float{
		return $this->originals[$type] ?? 0.0;
	}

	/**
	 * @param int $type
	 *
	 * @return float
	 */
	public function getDamage(int $type = self::MODIFIER_BASE) : float{
		return $this->modifiers[$type] ?? 0.0;
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
	public function isApplicable(int $type) : bool{
		return isset($this->modifiers[$type]);
	}

	/**
	 * @return float
	 */
	public function getFinalDamage() : float{
		return array_sum($this->modifiers);
	}

	/**
	 * Returns whether an entity can use armour points to reduce this type of damage.
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
				return false;

		}

		return true;
	}
}
