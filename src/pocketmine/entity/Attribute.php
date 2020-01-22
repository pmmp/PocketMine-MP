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

use function max;
use function min;

class Attribute{

	public const ABSORPTION = 0;
	public const SATURATION = 1;
	public const EXHAUSTION = 2;
	public const KNOCKBACK_RESISTANCE = 3;
	public const HEALTH = 4;
	public const MOVEMENT_SPEED = 5;
	public const FOLLOW_RANGE = 6;
	public const HUNGER = 7;
	public const FOOD = 7;
	public const ATTACK_DAMAGE = 8;
	public const EXPERIENCE_LEVEL = 9;
	public const EXPERIENCE = 10;
	public const UNDERWATER_MOVEMENT = 11;
	public const LUCK = 12;
	public const FALL_DAMAGE = 13;
	public const HORSE_JUMP_STRENGTH = 14;
	public const ZOMBIE_SPAWN_REINFORCEMENTS = 15;

	/** @var int */
	private $id;
	/** @var float */
	protected $minValue;
	/** @var float */
	protected $maxValue;
	/** @var float */
	protected $defaultValue;
	/** @var float */
	protected $currentValue;
	/** @var string */
	protected $name;
	/** @var bool */
	protected $shouldSend;

	/** @var bool */
	protected $desynchronized = true;

	/** @var Attribute[] */
	protected static $attributes = [];

	public static function init() : void{
		self::addAttribute(self::ABSORPTION, "minecraft:absorption", 0.00, 340282346638528859811704183484516925440.00, 0.00);
		self::addAttribute(self::SATURATION, "minecraft:player.saturation", 0.00, 20.00, 20.00);
		self::addAttribute(self::EXHAUSTION, "minecraft:player.exhaustion", 0.00, 5.00, 0.0, false);
		self::addAttribute(self::KNOCKBACK_RESISTANCE, "minecraft:knockback_resistance", 0.00, 1.00, 0.00);
		self::addAttribute(self::HEALTH, "minecraft:health", 0.00, 20.00, 20.00);
		self::addAttribute(self::MOVEMENT_SPEED, "minecraft:movement", 0.00, 340282346638528859811704183484516925440.00, 0.10);
		self::addAttribute(self::FOLLOW_RANGE, "minecraft:follow_range", 0.00, 2048.00, 16.00, false);
		self::addAttribute(self::HUNGER, "minecraft:player.hunger", 0.00, 20.00, 20.00);
		self::addAttribute(self::ATTACK_DAMAGE, "minecraft:attack_damage", 0.00, 340282346638528859811704183484516925440.00, 1.00, false);
		self::addAttribute(self::EXPERIENCE_LEVEL, "minecraft:player.level", 0.00, 24791.00, 0.00);
		self::addAttribute(self::EXPERIENCE, "minecraft:player.experience", 0.00, 1.00, 0.00);
		self::addAttribute(self::UNDERWATER_MOVEMENT, "minecraft:underwater_movement", 0.0, 340282346638528859811704183484516925440.0, 0.02);
		self::addAttribute(self::LUCK, "minecraft:luck", -1024.0, 1024.0, 0.0);
		self::addAttribute(self::FALL_DAMAGE, "minecraft:fall_damage", 0.0, 340282346638528859811704183484516925440.0, 1.0);
		self::addAttribute(self::HORSE_JUMP_STRENGTH, "minecraft:horse.jump_strength", 0.0, 2.0, 0.7);
		self::addAttribute(self::ZOMBIE_SPAWN_REINFORCEMENTS, "minecraft:zombie.spawn_reinforcements", 0.0, 1.0, 0.0);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public static function addAttribute(int $id, string $name, float $minValue, float $maxValue, float $defaultValue, bool $shouldSend = true) : Attribute{
		if($minValue > $maxValue or $defaultValue > $maxValue or $defaultValue < $minValue){
			throw new \InvalidArgumentException("Invalid ranges: min value: $minValue, max value: $maxValue, $defaultValue: $defaultValue");
		}

		return self::$attributes[$id] = new Attribute($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend);
	}

	public static function getAttribute(int $id) : ?Attribute{
		return isset(self::$attributes[$id]) ? clone self::$attributes[$id] : null;
	}

	public static function getAttributeByName(string $name) : ?Attribute{
		foreach(self::$attributes as $a){
			if($a->getName() === $name){
				return clone $a;
			}
		}

		return null;
	}

	private function __construct(int $id, string $name, float $minValue, float $maxValue, float $defaultValue, bool $shouldSend = true){
		$this->id = $id;
		$this->name = $name;
		$this->minValue = $minValue;
		$this->maxValue = $maxValue;
		$this->defaultValue = $defaultValue;
		$this->shouldSend = $shouldSend;

		$this->currentValue = $this->defaultValue;
	}

	public function getMinValue() : float{
		return $this->minValue;
	}

	/**
	 * @return $this
	 */
	public function setMinValue(float $minValue){
		if($minValue > ($max = $this->getMaxValue())){
			throw new \InvalidArgumentException("Minimum $minValue is greater than the maximum $max");
		}

		if($this->minValue != $minValue){
			$this->desynchronized = true;
			$this->minValue = $minValue;
		}
		return $this;
	}

	public function getMaxValue() : float{
		return $this->maxValue;
	}

	/**
	 * @return $this
	 */
	public function setMaxValue(float $maxValue){
		if($maxValue < ($min = $this->getMinValue())){
			throw new \InvalidArgumentException("Maximum $maxValue is less than the minimum $min");
		}

		if($this->maxValue != $maxValue){
			$this->desynchronized = true;
			$this->maxValue = $maxValue;
		}
		return $this;
	}

	public function getDefaultValue() : float{
		return $this->defaultValue;
	}

	/**
	 * @return $this
	 */
	public function setDefaultValue(float $defaultValue){
		if($defaultValue > $this->getMaxValue() or $defaultValue < $this->getMinValue()){
			throw new \InvalidArgumentException("Default $defaultValue is outside the range " . $this->getMinValue() . " - " . $this->getMaxValue());
		}

		if($this->defaultValue !== $defaultValue){
			$this->desynchronized = true;
			$this->defaultValue = $defaultValue;
		}
		return $this;
	}

	public function resetToDefault() : void{
		$this->setValue($this->getDefaultValue(), true);
	}

	public function getValue() : float{
		return $this->currentValue;
	}

	/**
	 * @return $this
	 */
	public function setValue(float $value, bool $fit = false, bool $forceSend = false){
		if($value > $this->getMaxValue() or $value < $this->getMinValue()){
			if(!$fit){
				throw new \InvalidArgumentException("Value $value is outside the range " . $this->getMinValue() . " - " . $this->getMaxValue());
			}
			$value = min(max($value, $this->getMinValue()), $this->getMaxValue());
		}

		if($this->currentValue != $value){
			$this->desynchronized = true;
			$this->currentValue = $value;
		}elseif($forceSend){
			$this->desynchronized = true;
		}

		return $this;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getId() : int{
		return $this->id;
	}

	public function isSyncable() : bool{
		return $this->shouldSend;
	}

	public function isDesynchronized() : bool{
		return $this->shouldSend and $this->desynchronized;
	}

	public function markSynchronized(bool $synced = true) : void{
		$this->desynchronized = !$synced;
	}
}
