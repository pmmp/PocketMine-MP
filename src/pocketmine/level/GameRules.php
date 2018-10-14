<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\level;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

class GameRules{

	public const RULE_TYPE_UNKNOWN = 0;
	public const RULE_TYPE_BOOL = 1;
	public const RULE_TYPE_INT = 2;
	public const RULE_TYPE_FLOAT = 3;

	/** @var int[][] */
	public $rules = [];
	/** @var int[][] */
	public $dirtyRules = [];

	public function __construct(){
		// bedrock edition game rules
		$this->setRule("commandBlockOutput", true, self::RULE_TYPE_BOOL);
		$this->setRule("doDaylightCycle", true, self::RULE_TYPE_BOOL);
		$this->setRule("doEntityDrops", true, self::RULE_TYPE_BOOL);
		$this->setRule("doFireTick", true, self::RULE_TYPE_BOOL);
		$this->setRule("doInsomnia", true, self::RULE_TYPE_BOOL);
		$this->setRule("doMobLoot", true, self::RULE_TYPE_BOOL);
		$this->setRule("doMobSpawning", false, self::RULE_TYPE_BOOL);
		$this->setRule("doTileDrops", true, self::RULE_TYPE_BOOL);
		$this->setRule("doWeatherCycle", true, self::RULE_TYPE_BOOL);
		$this->setRule("drowningdamage", true, self::RULE_TYPE_BOOL);
		$this->setRule("falldamage", true, self::RULE_TYPE_BOOL);
		$this->setRule("firedamage", true, self::RULE_TYPE_BOOL);
		$this->setRule("keepInventory", false, self::RULE_TYPE_BOOL);
		$this->setRule("maxCommandChainLength", 65536, self::RULE_TYPE_INT);
		$this->setRule("mobGriefing", true, self::RULE_TYPE_BOOL);
		$this->setRule("naturalRegeneration", true, self::RULE_TYPE_BOOL);
		$this->setRule("pvp", true, self::RULE_TYPE_BOOL);
		$this->setRule("sendCommandFeedback", true, self::RULE_TYPE_BOOL);
		$this->setRule("showcoordinates", false, self::RULE_TYPE_BOOL);
		$this->setRule("tntexplodes", true, self::RULE_TYPE_BOOL);
	}

	/**
	 * @param string $name
	 * @param        $value
	 * @param int    $valueType
	 *
	 * @return bool
	 */
	public function setRule(string $name, $value, int $valueType) : bool{
		if($this->checkType($value, $valueType)){
			$this->rules[$name] = $this->dirtyRules[$name] = [
				$valueType,
				$value
			];
			return true;
		}
		return false;
	}

	/**
	 * @param string $name
	 * @param        $value
	 * @param bool   $force
	 *
	 * @return bool
	 */
	public function setRuleWithMatching(string $name, $value, bool $force = false) : bool{
		if($this->hasRule($name)){
			$type = $this->rules[$name][0];
			$value = $this->convertType($value, $type);

			return $this->setRule($name, $value, $type);
		}elseif($force){
			return $this->setRule($name, $value, self::RULE_TYPE_UNKNOWN);
		}

		return false;
	}

	/**
	 * @param string $name
	 * @param int    $expectedType
	 * @param        $defaultValue
	 *
	 * @return int|float|bool|null
	 */
	public function getRule(string $name, int $expectedType, $defaultValue){
		if($this->hasRule($name)){
			$rule = $this->rules[$name];

			if($this->checkType($rule[1], $expectedType)){
				return $rule[1];
			}else{
				return $defaultValue;
			}
		}
		return null;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasRule(string $name) : bool{
		return isset($this->rules[$name]) and isset($this->rules[$name][0]) and isset($this->rules[$name][1]);
	}

	/**
	 * @param     $input
	 * @param int $wantedType
	 *
	 * @return bool
	 */
	public function checkType($input, int $wantedType) : bool{
		switch($wantedType){
			default:
				return false;
			case self::RULE_TYPE_INT:
				return is_int($input);
			case self::RULE_TYPE_FLOAT:
				return is_float($input);
			case self::RULE_TYPE_BOOL:
				return is_bool($input);
			case self::RULE_TYPE_UNKNOWN:
				return true;
		}
	}

	/**
	 * @param string $input
	 * @param int    $wantedType
	 *
	 * @return bool|float|int|string
	 */
	public function convertType(string $input, int $wantedType){
		switch($wantedType){
			default:
				return $input;
			case self::RULE_TYPE_INT:
				return intval($input);
			case self::RULE_TYPE_FLOAT:
				return floatval($input);
			case self::RULE_TYPE_BOOL:
				return strtolower($input) === "true" ? true : false;
		}
	}

	/**
	 * @param string $name
	 * @param bool   $value
	 */
	public function setBool(string $name, bool $value) : void{
		$this->setRule($name, $value, self::RULE_TYPE_BOOL);
	}

	/**
	 * @param string $name
	 * @param bool   $defaultValue
	 *
	 * @return bool
	 */
	public function getBool(string $name, bool $defaultValue = false) : bool{
		return $this->getRule($name, self::RULE_TYPE_BOOL, $defaultValue);
	}

	/**
	 * @param string $name
	 * @param int    $value
	 */
	public function setInt(string $name, int $value) : void{
		$this->setRule($name, $value, self::RULE_TYPE_INT);
	}

	/**
	 * @param string $name
	 * @param int    $defaultValue
	 *
	 * @return bool
	 */
	public function getInt(string $name, int $defaultValue = 0) : bool{
		return $this->getRule($name, self::RULE_TYPE_INT, $defaultValue);
	}

	/**
	 * @param string $name
	 * @param float  $value
	 */
	public function setFloat(string $name, float $value) : void{
		$this->setRule($name, $value, self::RULE_TYPE_FLOAT);
	}

	/**
	 * @param string $name
	 * @param float  $defaultValue
	 *
	 * @return bool
	 */
	public function getFloat(string $name, float $defaultValue = 0.0) : bool{
		return $this->getRule($name, self::RULE_TYPE_FLOAT, $defaultValue);
	}

	/**
	 * @return array
	 */
	public function getRules() : array{
		return $this->rules;
	}

	/**
	 * @param CompoundTag $nbt
	 */
	public function readSaveData(CompoundTag $nbt) : void{
		foreach($nbt->getValue() as $tag){
			if($tag instanceof StringTag){
				$this->setRuleWithMatching($tag->getName(), $tag->getValue(), true);
			}
		}

		$this->clearDirtyRules();
	}

	/**
	 * @return CompoundTag
	 */
	public function writeSaveData() : CompoundTag{
		$nbt = new CompoundTag("GameRules");

		foreach($this->rules as $name => $rule){
			$nbt->setString($name, strval($rule[1]));
		}

		return $nbt;
	}

	public function clearDirtyRules() : void{
		$this->dirtyRules = [];
	}

	public function getDirtyRules() : array{
		return $this->dirtyRules;
	}
}