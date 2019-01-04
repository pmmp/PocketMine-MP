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

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use function assert;
use function is_float;
use function is_int;
use function is_string;

class DataPropertyManager{

	private $properties = [];

	private $dirtyProperties = [];

	public function __construct(){

	}

	/**
	 * @param int $key
	 *
	 * @return int|null
	 */
	public function getByte(int $key) : ?int{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_BYTE);
		assert(is_int($value) or $value === null);
		return $value;
	}

	/**
	 * @param int  $key
	 * @param int  $value
	 * @param bool $force
	 */
	public function setByte(int $key, int $value, bool $force = false) : void{
		$this->setPropertyValue($key, Entity::DATA_TYPE_BYTE, $value, $force);
	}

	/**
	 * @param int $key
	 *
	 * @return int|null
	 */
	public function getShort(int $key) : ?int{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_SHORT);
		assert(is_int($value) or $value === null);
		return $value;
	}

	/**
	 * @param int  $key
	 * @param int  $value
	 * @param bool $force
	 */
	public function setShort(int $key, int $value, bool $force = false) : void{
		$this->setPropertyValue($key, Entity::DATA_TYPE_SHORT, $value, $force);
	}

	/**
	 * @param int $key
	 *
	 * @return int|null
	 */
	public function getInt(int $key) : ?int{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_INT);
		assert(is_int($value) or $value === null);
		return $value;
	}

	/**
	 * @param int  $key
	 * @param int  $value
	 * @param bool $force
	 */
	public function setInt(int $key, int $value, bool $force = false) : void{
		$this->setPropertyValue($key, Entity::DATA_TYPE_INT, $value, $force);
	}

	/**
	 * @param int $key
	 *
	 * @return float|null
	 */
	public function getFloat(int $key) : ?float{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_FLOAT);
		assert(is_float($value) or $value === null);
		return $value;
	}

	/**
	 * @param int   $key
	 * @param float $value
	 * @param bool  $force
	 */
	public function setFloat(int $key, float $value, bool $force = false) : void{
		$this->setPropertyValue($key, Entity::DATA_TYPE_FLOAT, $value, $force);
	}

	/**
	 * @param int $key
	 *
	 * @return null|string
	 */
	public function getString(int $key) : ?string{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_STRING);
		assert(is_string($value) or $value === null);
		return $value;
	}

	/**
	 * @param int    $key
	 * @param string $value
	 * @param bool   $force
	 */
	public function setString(int $key, string $value, bool $force = false) : void{
		$this->setPropertyValue($key, Entity::DATA_TYPE_STRING, $value, $force);
	}

	/**
	 * @param int $key
	 *
	 * @return null|Item
	 */
	public function getItem(int $key) : ?Item{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_SLOT);
		assert($value instanceof Item or $value === null);

		return $value;
	}

	/**
	 * @param int  $key
	 * @param Item $value
	 * @param bool $force
	 */
	public function setItem(int $key, Item $value, bool $force = false) : void{
		$this->setPropertyValue($key, Entity::DATA_TYPE_SLOT, $value, $force);
	}

	/**
	 * @param int $key
	 *
	 * @return null|Vector3
	 */
	public function getBlockPos(int $key) : ?Vector3{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_POS);
		assert($value instanceof Vector3 or $value === null);
		return $value;
	}

	/**
	 * @param int          $key
	 * @param null|Vector3 $value
	 * @param bool         $force
	 */
	public function setBlockPos(int $key, ?Vector3 $value, bool $force = false) : void{
		$this->setPropertyValue($key, Entity::DATA_TYPE_POS, $value ? $value->floor() : null, $force);
	}

	/**
	 * @param int $key
	 *
	 * @return int|null
	 */
	public function getLong(int $key) : ?int{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_LONG);
		assert(is_int($value) or $value === null);
		return $value;
	}

	/**
	 * @param int  $key
	 * @param int  $value
	 * @param bool $force
	 */
	public function setLong(int $key, int $value, bool $force = false) : void{
		$this->setPropertyValue($key, Entity::DATA_TYPE_LONG, $value, $force);
	}

	/**
	 * @param int $key
	 *
	 * @return null|Vector3
	 */
	public function getVector3(int $key) : ?Vector3{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_VECTOR3F);
		assert($value instanceof Vector3 or $value === null);
		return $value;
	}

	/**
	 * @param int          $key
	 * @param null|Vector3 $value
	 * @param bool         $force
	 */
	public function setVector3(int $key, ?Vector3 $value, bool $force = false) : void{
		$this->setPropertyValue($key, Entity::DATA_TYPE_VECTOR3F, $value ? $value->asVector3() : null, $force);
	}

	/**
	 * @param int $key
	 */
	public function removeProperty(int $key) : void{
		unset($this->properties[$key]);
	}

	/**
	 * @param int $key
	 *
	 * @return bool
	 */
	public function hasProperty(int $key) : bool{
		return isset($this->properties[$key]);
	}

	/**
	 * @param int $key
	 *
	 * @return int
	 */
	public function getPropertyType(int $key) : int{
		if(isset($this->properties[$key])){
			return $this->properties[$key][0];
		}

		return -1;
	}

	private function checkType(int $key, int $type) : void{
		if(isset($this->properties[$key]) and $this->properties[$key][0] !== $type){
			throw new \RuntimeException("Expected type $type, but have " . $this->properties[$key][0]);
		}
	}

	/**
	 * @param int $key
	 * @param int $type
	 *
	 * @return mixed
	 */
	public function getPropertyValue(int $key, int $type){
		if($type !== -1){
			$this->checkType($key, $type);
		}
		return isset($this->properties[$key]) ? $this->properties[$key][1] : null;
	}

	/**
	 * @param int   $key
	 * @param int   $type
	 * @param mixed $value
	 * @param bool  $force
	 */
	public function setPropertyValue(int $key, int $type, $value, bool $force = false) : void{
		if(!$force){
			$this->checkType($key, $type);
		}
		$this->properties[$key] = $this->dirtyProperties[$key] = [$type, $value];
	}

	/**
	 * Returns all properties.
	 *
	 * @return array
	 */
	public function getAll() : array{
		return $this->properties;
	}

	/**
	 * Returns properties that have changed and need to be broadcasted.
	 *
	 * @return array
	 */
	public function getDirty() : array{
		return $this->dirtyProperties;
	}

	/**
	 * Clears records of dirty properties.
	 */
	public function clearDirtyProperties() : void{
		$this->dirtyProperties = [];
	}
}
