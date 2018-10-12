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

namespace pocketmine\network\mcpe\protocol\types;

class GameRules{

	public const RULE_TYPE_BOOL = 1;
	public const RULE_TYPE_INT = 2;
	public const RULE_TYPE_FLOAT = 3;

	/** @var int[][] */
	public $rules = [];

	/**
	 * GameRules constructor.
	 *
	 * @param array $rules
	 */
	public function __construct(array $rules = []){
		foreach($rules as $name => $rule){
			if(is_string($name)){
				if(is_array($rule)){
					if(isset($rule[0]) and isset($rule[1])){
						switch($rule[0]){
							case self::RULE_TYPE_INT:
								$this->setInt($name, $rule[0]);
								break;
							case self::RULE_TYPE_FLOAT:
								$this->setFloat($name, $rule[0]);
								break;
							case self::RULE_TYPE_BOOL:
								$this->setBool($name, $rule[0]);
								break;
						}
					}
				}
			}
		}
	}

	/**
	 * @param string $name
	 * @param        $value
	 * @param int    $valueType
	 */
	public function setRule(string $name, $value, int $valueType) : void{
		if($this->checkType($value, $valueType)){
			$this->rules[$name] = [
				$valueType,
				$value
			];
		}
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
	public function getAll() : array{
		return $this->rules;
	}
}