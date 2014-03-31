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

/**
 * Permission related classes
 */
namespace PocketMine\Permission;

use PocketMine\Server;

/**
 * Represents a permission
 */
class Permission{
	const DEFAULT_OP = "op";
	const DEFAULT_NOT_OP = "notop";
	const DEFAULT_TRUE = "true";
	const DEFAULT_FALSE = "false";

	public static $DEFAULT_PERMISSION = self::DEFAULT_OP;

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public static function getByName($value){
		if(is_bool($value)){
			if($value === true){
				return "true";
			}else{
				return "false";
			}
		}
		switch(strtolower($value)){
			case "op":
			case "isop":
			case "operator":
			case "isoperator":
			case "admin":
			case "isadmin":
				return self::DEFAULT_OP;

			case "!op":
			case "notop":
			case "!operator":
			case "notoperator":
			case "!admin":
			case "notadmin":
				return self::DEFAULT_NOT_OP;

			case "true":
				return self::DEFAULT_TRUE;

			default:
				return self::DEFAULT_FALSE;
		}
	}

	/** @var string */
	private $name;

	/** @var string */
	private $description;

	/**
	 * @var string[]
	 */
	private $children = array();

	/** @var string */
	private $defaultValue;

	/**
	 * Creates a new Permission object to be attached to Permissible objects
	 *
	 * @param string       $name
	 * @param string       $description
	 * @param string       $defaultValue
	 * @param Permission[] $children
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
	 * @return string[]
	 */
	public function &getChildren(){
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

	/**
	 * @return Permissible[]
	 */
	public function getPermissibles(){
		return Server::getInstance()->getPluginManager()->getPermissionSubscriptions($this->name);
	}

	public function recalculatePermissibles(){
		$perms = $this->getPermissibles();

		Server::getInstance()->getPluginManager()->recalculatePermissionDefaults($this);

		foreach($perms as $p){
			$p->recalculatePermissions();
		}
	}


	/**
	 * @param string|Permission $name
	 * @param                   $value
	 *
	 * @return Permission|void
	 */
	public function addParent($name, $value){
		if($name instanceof Permission){
			$name->getChildren()[$this->getName()] = $value;
			$name->recalculatePermissibles();
		}else{
			$perm = Server::getInstance()->getPluginManager()->getPermission($name);
			if($perm === null){
				$perm = new Permission($name);
				Server::getInstance()->getPluginManager()->addPermission($perm);
			}

			$this->addParent($perm, $value);

			return $perm;
		}
	}

	/**
	 * @param array $data
	 * @param       $default
	 *
	 * @return Permission[]
	 */
	public static function loadPermissions(array $data, $default = self::DEFAULT_OP){
		$result = array();
		foreach($data as $key => $entry){
			$result[] = self::loadPermission($key, $entry, $default, $result);
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @param array  $data
	 * @param string $default
	 * @param array  $output
	 *
	 * @return Permission
	 */
	public static function loadPermission($name, array $data, $default = self::DEFAULT_OP, &$output = array()){
		$desc = null;
		$children = array();
		if(isset($data["default"])){
			$value = Permission::getByName($data["default"]);
			if($value !== null){
				$default = $value;
			}else{
				trigger_error("'default' key contained unknown value", E_USER_WARNING);
			}
		}

		if(isset($data["children"])){
			if(is_array($data["children"])){
				foreach($data["children"] as $k => $v){
					if(is_array($v)){
						if(($perm = self::loadPermission($k, $v, $default, $output)) !== null){
							$output[] = $perm;
						}
					}
					$children[$k] = true;
				}
			}else{
				trigger_error("'children' key is of wrong type", E_USER_WARNING);
			}
		}

		if(isset($data["description"])){
			$desc = $data["description"];
		}

		return new Permission($name, $desc, $default, $children);

	}


}