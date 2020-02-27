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

namespace pocketmine\permission;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginException;

class PermissionAttachment{
	/** @var PermissionRemovedExecutor|null */
	private $removed = null;

	/** @var bool[] */
	private $permissions = [];

	/** @var Permissible */
	private $permissible;

	/** @var Plugin */
	private $plugin;

	/**
	 * @throws PluginException
	 */
	public function __construct(Plugin $plugin, Permissible $permissible){
		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin " . $plugin->getDescription()->getName() . " is disabled");
		}

		$this->permissible = $permissible;
		$this->plugin = $plugin;
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

	public function setRemovalCallback(PermissionRemovedExecutor $ex) : void{
		$this->removed = $ex;
	}

	public function getRemovalCallback() : ?PermissionRemovedExecutor{
		return $this->removed;
	}

	public function getPermissible() : Permissible{
		return $this->permissible;
	}

	/**
	 * @return bool[]
	 */
	public function getPermissions() : array{
		return $this->permissions;
	}

	public function clearPermissions() : void{
		$this->permissions = [];
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param bool[] $permissions
	 */
	public function setPermissions(array $permissions) : void{
		foreach($permissions as $key => $value){
			$this->permissions[$key] = $value;
		}
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string[] $permissions
	 */
	public function unsetPermissions(array $permissions) : void{
		foreach($permissions as $node){
			unset($this->permissions[$node]);
		}
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string|Permission $name
	 */
	public function setPermission($name, bool $value) : void{
		$name = $name instanceof Permission ? $name->getName() : $name;
		if(isset($this->permissions[$name])){
			if($this->permissions[$name] === $value){
				return;
			}
			unset($this->permissions[$name]); //Fixes children getting overwritten
		}
		$this->permissions[$name] = $value;
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string|Permission $name
	 */
	public function unsetPermission($name) : void{
		$name = $name instanceof Permission ? $name->getName() : $name;
		if(isset($this->permissions[$name])){
			unset($this->permissions[$name]);
			$this->permissible->recalculatePermissions();
		}
	}

	public function remove() : void{
		$this->permissible->removeAttachment($this);
	}
}
