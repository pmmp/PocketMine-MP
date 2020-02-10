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

/**
 * Permission related classes
 */

namespace pocketmine\permission;

use function is_array;
use function is_bool;
use function strtolower;

/**
 * Represents a permission
 */
class Permission{
	public const DEFAULT_OP = "op";
	public const DEFAULT_NOT_OP = "notop";
	public const DEFAULT_TRUE = "true";
	public const DEFAULT_FALSE = "false";

	/** @var string */
	public static $DEFAULT_PERMISSION = self::DEFAULT_OP;

	/**
	 * @param bool|string $value
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function getByName($value) : string{
		if(is_bool($value)){
			if($value){
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
			case "false":
				return self::DEFAULT_FALSE;
		}

		throw new \InvalidArgumentException("Unknown permission default name \"$value\"");
	}

	/**
	 * @param mixed[][] $data
	 * @phpstan-param array<string, array<string, mixed>> $data
	 *
	 * @return Permission[]
	 */
	public static function loadPermissions(array $data, string $default = self::DEFAULT_OP) : array{
		$result = [];
		foreach($data as $key => $entry){
			$result[] = self::loadPermission($key, $entry, $default, $result);
		}

		return $result;
	}

	/**
	 * @param mixed[]      $data
	 * @param Permission[] $output reference parameter
	 * @phpstan-param array<string, mixed> $data
	 *
	 * @throws \Exception
	 */
	public static function loadPermission(string $name, array $data, string $default = self::DEFAULT_OP, array &$output = []) : Permission{
		$desc = null;
		$children = [];
		if(isset($data["default"])){
			$default = Permission::getByName($data["default"]);
		}

		if(isset($data["children"])){
			if(is_array($data["children"])){
				foreach($data["children"] as $k => $v){
					if(is_array($v)){
						$output[] = self::loadPermission($k, $v, $default, $output);
					}
					$children[$k] = true;
				}
			}else{
				throw new \InvalidStateException("'children' key is of wrong type");
			}
		}

		if(isset($data["description"])){
			$desc = $data["description"];
		}

		return new Permission($name, $desc, $default, $children);
	}

	/** @var string */
	private $name;

	/** @var string */
	private $description;

	/**
	 * @var bool[]
	 * @phpstan-var array<string, bool>
	 */
	private $children;

	/** @var string */
	private $defaultValue;

	/**
	 * Creates a new Permission object to be attached to Permissible objects
	 *
	 * @param bool[] $children
	 * @phpstan-param array<string, bool> $children
	 */
	public function __construct(string $name, string $description = null, string $defaultValue = null, array $children = []){
		$this->name = $name;
		$this->description = $description ?? "";
		$this->defaultValue = $defaultValue ?? self::$DEFAULT_PERMISSION;
		$this->children = $children;

		$this->recalculatePermissibles();
	}

	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return bool[]
	 * @phpstan-return array<string, bool>
	 */
	public function &getChildren() : array{
		return $this->children;
	}

	public function getDefault() : string{
		return $this->defaultValue;
	}

	/**
	 * @return void
	 */
	public function setDefault(string $value){
		if($value !== $this->defaultValue){
			$this->defaultValue = $value;
			$this->recalculatePermissibles();
		}
	}

	public function getDescription() : string{
		return $this->description;
	}

	/**
	 * @return void
	 */
	public function setDescription(string $value){
		$this->description = $value;
	}

	/**
	 * @return Permissible[]
	 */
	public function getPermissibles() : array{
		return PermissionManager::getInstance()->getPermissionSubscriptions($this->name);
	}

	/**
	 * @return void
	 */
	public function recalculatePermissibles(){
		$perms = $this->getPermissibles();

		PermissionManager::getInstance()->recalculatePermissionDefaults($this);

		foreach($perms as $p){
			$p->recalculatePermissions();
		}
	}

	/**
	 * @param string|Permission $name
	 *
	 * @return Permission|null Permission if $name is a string, null if it's a Permission
	 */
	public function addParent($name, bool $value){
		if($name instanceof Permission){
			$name->getChildren()[$this->getName()] = $value;
			$name->recalculatePermissibles();
			return null;
		}else{
			$perm = PermissionManager::getInstance()->getPermission($name);
			if($perm === null){
				$perm = new Permission($name);
				PermissionManager::getInstance()->addPermission($perm);
			}

			$this->addParent($perm, $value);

			return $perm;
		}
	}
}
