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

use function strval;
use function is_bool;

class GameRules{

	public const RULE_COMMAND_BLOCK_OUTPUT = "commandBlockOutput";
	public const RULE_DO_DAYLIGHT_CYCLE = "doDaylightCycle";
	public const RULE_DO_ENTITY_DROPS = "doEntityDrops";
	public const RULE_DO_FIRE_TICK = "doFireTick";
	public const RULE_DO_INSOMNIA = "doInsomnia";
	public const RULE_DO_MOB_LOOT = "doMobLoot";
	public const RULE_DO_MOB_SPAWNING = "doMobSpawning";
	public const RULE_DO_TILE_DROPS = "doTileDrops";
	public const RULE_DO_IMMEDIATE_RESPAWN = "doimmediaterespawn";
	public const RULE_DO_WEATHER_CYCLE = "doWeatherCycle";
	public const RULE_DROWNING_DAMAGE = "drowningdamage";
	public const RULE_FALL_DAMAGE = "falldamage";
	public const RULE_FIRE_DAMAGE = "firedamage";
	public const RULE_KEEP_INVENTORY = "keepInventory";
	public const RULE_MAX_COMMAND_CHAIN_LENGTH = "maxCommandChainLength";
	public const RULE_MOB_GRIEFING = "mobGriefing";
	public const RULE_PVP = "pvp";
	public const RULE_SEND_COMMAND_FEEDBACK = "sendCommandFeedback";
	public const RULE_SHOW_COORDINATES = "showcoordinates";
	public const RULE_TNT_EXPLODES = "tntexplodes";
	public const RULE_NATURAL_REGENERATION = "naturalRegeneration";
	public const RULE_RANDOM_TICK_SPEED = "randomtickspeed";

	public const RULE_TYPE_BOOL = 1;
	public const RULE_TYPE_INT = 2;
	public const RULE_TYPE_FLOAT = 3;

	/** @var int[][] */
	public $rules = [];
	/** @var int[][] */
	public $dirtyRules = [];

	public function __construct(){
		// default bedrock edition game rules
		$this->setBool(self::RULE_COMMAND_BLOCK_OUTPUT, true);
		$this->setBool(self::RULE_DO_DAYLIGHT_CYCLE, true);
		$this->setBool(self::RULE_DO_ENTITY_DROPS, true);
		$this->setBool(self::RULE_DO_FIRE_TICK, true);
		$this->setBool(self::RULE_DO_INSOMNIA, true);
		$this->setBool(self::RULE_DO_MOB_LOOT, true);
		$this->setBool(self::RULE_DO_MOB_SPAWNING, false);
		$this->setBool(self::RULE_DO_TILE_DROPS, true);
		$this->setBool(self::RULE_DO_IMMEDIATE_RESPAWN, false);
		$this->setBool(self::RULE_DO_WEATHER_CYCLE, true);
		$this->setBool(self::RULE_DROWNING_DAMAGE, true);
		$this->setBool(self::RULE_FALL_DAMAGE, true);
		$this->setBool(self::RULE_FIRE_DAMAGE, true);
		$this->setBool(self::RULE_KEEP_INVENTORY, false);
		$this->setInt(self::RULE_MAX_COMMAND_CHAIN_LENGTH, 65536);
		$this->setBool(self::RULE_MOB_GRIEFING, true);
		$this->setBool(self::RULE_NATURAL_REGENERATION, true);
		$this->setBool(self::RULE_PVP, true);
		$this->setBool(self::RULE_SEND_COMMAND_FEEDBACK, true);
		$this->setBool(self::RULE_SHOW_COORDINATES, false);
		$this->setBool(self::RULE_TNT_EXPLODES, true);
		$this->setInt(self::RULE_RANDOM_TICK_SPEED, 3);
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
				$valueType, $value
			];
			return true;
		}
		return false;
	}

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return bool
	 */
	public function setRuleWithMatching(string $name, $value) : bool{
		if($this->hasRule($name)){
			$type = $this->rules[$name][0];
			$value = $this->convertType($value, $type);

			return $this->setRule($name, $value, $type);
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
			}
		}
		return $defaultValue;
	}

	/**
	 * @param string $name
	 *
	 * @return bool|int|null
	 */
	public function getRuleValue(string $name){
		return isset($this->rules[$name]) ? $this->rules[$name][1] : null;
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
	 * @param $value
	 *
	 * @return string
	 */
	public function toStringValue($value) : string{
		if(is_bool($value)){
			return $value ? "true" : "false";
		}
		return strval($value);
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
	 * @return int
	 */
	public function getInt(string $name, int $defaultValue = 0) : int{
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
	 * @return float
	 */
	public function getFloat(string $name, float $defaultValue = 0.0) : float{
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
				$this->setRuleWithMatching($tag->getName(), $tag->getValue());
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
			$nbt->setString($name, $this->toStringValue($rule[1]));
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