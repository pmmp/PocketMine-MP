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

/**
 * Represents a permission
 */
class Permission{
	public const DEFAULT_OP = "op";
	public const DEFAULT_NOT_OP = "notop";
	public const DEFAULT_TRUE = "true";
	public const DEFAULT_FALSE = "false";

	public static $DEFAULT_PERMISSION = self::DEFAULT_OP;

	/** @var string */
	private $name;

	/** @var string */
	private $description;

	/**
	 * @var bool[]
	 */
	private $children;

	/** @var string */
	private $defaultValue;

	/**
	 * Creates a new Permission object to be attached to Permissible objects
	 *
	 * @param string $name
	 * @param string $description
	 * @param string $defaultValue
	 * @param bool[] $children
	 */
	public function __construct(string $name, ?string $description = null, ?string $defaultValue = null, array $children = []){
		$this->name = $name;
		$this->description = $description ?? "";
		$this->defaultValue = $defaultValue ?? self::$DEFAULT_PERMISSION;
		$this->children = $children;

		$this->recalculatePermissibles();
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return bool[]
	 */
	public function &getChildren() : array{
		return $this->children;
	}

	/**
	 * @return string
	 */
	public function getDefault() : string{
		return $this->defaultValue;
	}

	/**
	 * @param string $value
	 */
	public function setDefault(string $value) : void{
		if($value !== $this->defaultValue){
			$this->defaultValue = $value;
			$this->recalculatePermissibles();
		}
	}

	/**
	 * @return string
	 */
	public function getDescription() : string{
		return $this->description;
	}

	/**
	 * @param string $value
	 */
	public function setDescription(string $value) : void{
		$this->description = $value;
	}

	/**
	 * @return Permissible[]
	 */
	public function getPermissibles() : array{
		return PermissionManager::getInstance()->getPermissionSubscriptions($this->name);
	}

	public function recalculatePermissibles() : void{
		$perms = $this->getPermissibles();

		PermissionManager::getInstance()->recalculatePermissionDefaults($this);

		foreach($perms as $p){
			$p->recalculatePermissions();
		}
	}


	/**
	 * @param string|Permission $name
	 * @param bool              $value
	 *
	 * @return Permission|null Permission if $name is a string, null if it's a Permission
	 */
	public function addParent($name, bool $value) : ?Permission{
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
