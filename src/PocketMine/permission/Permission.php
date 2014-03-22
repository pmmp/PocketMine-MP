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

namespace PocketMine\Permission;

use PocketMine;

/**
 * Represents a permission
 */
class Permission{
	const DEFAULT_OP = "op";
	const DEFAULT_NOT_OP = "notop";
	const DEFAULT_TRUE = "true";
	const DEFAULT_FALSE = "false";

	public static $DEFAULT_PERMISSION = self::DEFAULT_OP;

	private $name;
	private $description;
	private $children = array();
	private $defaultValue;

	/**
	 * Creates a new Permission object to be attached to Permissible objects
	 *
	 * @param string $name
	 * @param string $description
	 * @param string $defaultValue
	 * @param array  $children
	 */
	public function __construct($name, $description = null, $defaultValue = null, array $children = array()){
		$this->name = $name;
		$this->description = $description !== null ? $description : "";
		$this->defaultValue = $defaultValue !== null ? $defaultValue : self::$DEFAULT_PERMISSION;
		$this->children = $children;

		$this->recalculatePermissibles();
	}

	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getChildren(){
		return $this->children;
	}

	/**
	 * @return string
	 */
	public function getDefault(){
		return $this->defaultValue;
	}

	/**
	 * @param string $value
	 */
	public function setDefault($value){
		if($value !== $this->defaultValue){
			$this->defaultValue = $value;
			$this->recalculatePermissibles();
		}
	}

	/**
	 * @return string
	 */
	public function getDescription(){
		return $this->description;
	}

	/**
	 * @param string $value
	 */
	public function setDescription($value){
		$this->description = $value;
	}

	public function getPermissibles(){
		//TODO: get from plugin manager
		//plugin handler -> getPermissionSubscriptions($this->name);
		return array();
	}

	public function recalculatePermissibles(){
		//TODO: recalculate
		$perms = $this->getPermissibles();

		//plugin handler -> recalculatePermissionDefaults($this);

		foreach($perms as $p){
			$p->recalculatePermissions();
		}
	}


	public function addParent($name){
		if($name instanceof Permission){

		}else{

		}
	}

}