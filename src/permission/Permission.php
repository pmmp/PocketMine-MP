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
	private string $description;

	/**
	 * Creates a new Permission object to be attached to Permissible objects
	 *
	 * @param bool[] $children
	 * @phpstan-param array<string, bool> $children
	 */
	public function __construct(
		private string $name,
		?string $description = null,
		private array $children = []
	){
		$this->description = $description ?? ""; //TODO: wtf ????

		$this->recalculatePermissibles();
	}

	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return bool[]
	 * @phpstan-return array<string, bool>
	 */
	public function getChildren() : array{
		return $this->children;
	}

	public function getDescription() : string{
		return $this->description;
	}

	public function setDescription(string $value) : void{
		$this->description = $value;
	}

	/**
	 * @return PermissibleInternal[]
	 */
	public function getPermissibles() : array{
		return PermissionManager::getInstance()->getPermissionSubscriptions($this->name);
	}

	public function recalculatePermissibles() : void{
		$perms = $this->getPermissibles();

		foreach($perms as $p){
			$p->recalculatePermissions();
		}
	}

	public function addChild(string $name, bool $value) : void{
		$this->children[$name] = $value;
		$this->recalculatePermissibles();
	}

	public function removeChild(string $name) : void{
		unset($this->children[$name]);
		$this->recalculatePermissibles();

	}
}
