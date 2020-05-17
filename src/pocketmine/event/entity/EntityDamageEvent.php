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
use function array_sum;

/**
 * Called when an entity takes damage.
 */
class EntityDamageEvent extends EntityEvent implements Cancellable{
	public const MODIFIER_ARMOR = 1;
	public const MODIFIER_STRENGTH = 2;
	public const MODIFIER_WEAKNESS = 3;
	public const MODIFIER_RESISTANCE = 4;
	public const MODIFIER_ABSORPTION = 5;
	public const MODIFIER_ARMOR_ENCHANTMENTS = 6;
	public const MODIFIER_CRITICAL = 7;
	public const MODIFIER_TOTEM = 8;
	public const MODIFIER_WEAPON_ENCHANTMENTS = 9;

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
	/** @var float */
	private $baseDamage;
	/** @var float */
	private $originalBase;

	/** @var float[] */
	private $modifiers;
	/** @var float[] */
	private $originals;

	/** @var int */
	private $attackCooldown = 10;

	/**
	 * @param float[] $modifiers
	 */
	public function __construct(Entity $entity, int $cause, float $damage, array $modifiers = []){
		$this->entity = $entity;
		$this->cause = $cause;
		$this->baseDamage = $this->originalBase = $damage;

		$this->modifiers = $modifiers;
		$this->originals = $this->modifiers;
	}

	public function getCause() : int{
		return $this->cause;
	}

	/**
	 * Returns the base amount of damage applied, before modifiers.
	 */
	public function getBaseDamage() : float{
		return $this->baseDamage;
	}

	/**
	 * Sets the base amount of damage applied, optionally recalculating modifiers.
	 *
	 * TODO: add ability to recalculate modifiers when this is set
	 */
	public function setBaseDamage(float $damage) : void{
		$this->baseDamage = $damage;
	}

	/**
	 * Returns the original base amount of damage applied, before alterations by plugins.
	 */
	public function getOriginalBaseDamage() : float{
		return $this->originalBase;
	}

	/**
	 * @return float[]
	 */
	public function getOriginalModifiers() : array{
		return $this->originals;
	}

	public function getOriginalModifier(int $type) : float{
		return $this->originals[$type] ?? 0.0;
	}

	/**
	 * @return float[]
	 */
	public function getModifiers() : array{
		return $this->modifiers;
	}

	public function getModifier(int $type) : float{
		return $this->modifiers[$type] ?? 0.0;
	}

	public function setModifier(float $damage, int $type) : void{
		$this->modifiers[$type] = $damage;
	}

	public function isApplicable(int $type) : bool{
		return isset($this->modifiers[$type]);
	}

	public function getFinalDamage() : float{
		return $this->baseDamage + array_sum($this->modifiers);
	}

	/**
	 * Returns whether an entity can use armour points to reduce this type of damage.
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

	/**
	 * Returns the cooldown in ticks before the target entity can be attacked again.
	 */
	public function getAttackCooldown() : int{
		return $this->attackCooldown;
	}

	/**
	 * Sets the cooldown in ticks before the target entity can be attacked again.
	 *
	 * NOTE: This value is not used in non-Living entities
	 */
	public function setAttackCooldown(int $attackCooldown) : void{
		$this->attackCooldown = $attackCooldown;
	}
}
