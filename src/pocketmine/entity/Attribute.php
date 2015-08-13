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

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\network\Network;
use pocketmine\network\protocol\MobEffectPacket;
use pocketmine\Player;


class Attribute{

	const MAX_HEALTH = 0;


	const EXPERIENCE = 1;
	const EXPERIENCE_LEVEL = 2;

	private $id;
	protected $minValue;
	protected $maxValue;
	protected $defaultValue;
	protected $currentValue;
	protected $name;
	protected $shouldSend;

	/** @var Attribute[] */
	protected static $attributes = [];

	public static function init(){
		self::addAttribute(self::MAX_HEALTH, "generic.health", 0, 0x7fffffff, 20, true);
		self::addAttribute(self::EXPERIENCE, "player.experience", 0, 1, 0, true);
		self::addAttribute(self::EXPERIENCE_LEVEL, "player.level", 0, 24791, 0, true);
	}

	/**
	 * @param int    $id
	 * @param string $name
	 * @param float  $minValue
	 * @param float  $maxValue
	 * @param float  $defaultValue
	 * @param bool   $shouldSend
	 * @return Attribute
	 */
	public static function addAttribute($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend = false){
		if($minValue > $maxValue or $defaultValue > $maxValue or $defaultValue < $minValue){
			throw new \InvalidArgumentException("Invalid ranges: min value: $minValue, max value: $maxValue, $defaultValue: $defaultValue");
		}

		return self::$attributes[(int) $id] = new Attribute($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend);
	}

	/**
	 * @param $id
	 * @return null|Attribute
	 */
	public static function getAttribute($id){
		return isset(self::$attributes[$id]) ? clone self::$attributes[$id] : null;
	}

	/**
	 * @param $name
	 * @return null|Attribute
	 */
	public static function getAttributeByName($name){
		foreach(self::$attributes as $a){
			if($a->getName() === $name){
				return clone $a;
			}
		}
		
		return null;
	}

	private function __construct($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend = false){
		$this->id = (int) $id;
		$this->name = (string) $name;
		$this->minValue = (float) $minValue;
		$this->maxValue = (float) $maxValue;
		$this->defaultValue = (float) $defaultValue;
		$this->shouldSend = (float) $shouldSend;

		$this->currentValue = $this->defaultValue;
	}

	public function getMinValue(){
		return $this->minValue;
	}
	
	public function setMinValue($minValue){
		if($minValue > $this->getMaxValue()){
			throw new \InvalidArgumentException("Value $minValue is bigger than the maxValue!");
		}

		$this->minValue = $minValue;
		return $this;
	}

	public function getMaxValue(){
		return $this->maxValue;
	}

	public function setMaxValue($maxValue){
		if($maxValue < $this->getMinValue()){
			throw new \InvalidArgumentException("Value $maxValue is bigger than the minValue!");
		}

		$this->maxValue = $maxValue;
		return $this;
	}

	public function getDefaultValue(){
		return $this->defaultValue;
	}

	public function setDefaultValue($defaultValue){
		if($defaultValue > $this->getMaxValue() or $defaultValue < $this->getMinValue()){
			throw new \InvalidArgumentException("Value $defaultValue exceeds the range!");
		}

		$this->defaultValue = $defaultValue;
		return $this;
	}

	public function getValue(){
		return $this->currentValue;
	}

	public function setValue($value){
		if($value > $this->getMaxValue() or $value < $this->getMinValue()){
			throw new \InvalidArgumentException("Value $value exceeds the range!");
		}

		$this->currentValue = $value;

		return $this;
	}

	public function getName(){
		return $this->name;
	}

	public function getId(){
		return $this->id;
	}

	public function isSyncable(){
		return $this->shouldSend;
	}

}
