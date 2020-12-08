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

	/**
	 * @return void
	 */
	public function setRemovalCallback(PermissionRemovedExecutor $ex){
		$this->removed = $ex;
	}

	/**
	 * @return PermissionRemovedExecutor|null
	 */
	public function getRemovalCallback(){
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

	/**
	 * @return void
	 */
	public function clearPermissions(){
		$this->permissions = [];
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param bool[] $permissions
	 *
	 * @return void
	 */
	public function setPermissions(array $permissions){
		foreach($permissions as $key => $value){
			$this->permissions[$key] = $value;
		}
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string[] $permissions
	 *
	 * @return void
	 */
	public function unsetPermissions(array $permissions){
		foreach($permissions as $node){
			unset($this->permissions[$node]);
		}
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string|Permission $name
	 *
	 * @return void
	 */
	public function setPermission($name, bool $value){
		$name = $name instanceof Permission ? $name->getName() : $name;
		if(isset($this->permissions[$name])){
			if($this->permissions[$name] === $value){
				return;
			}
			/* Because of the way child permissions are calculated, permissions which were set later in time are
			 * preferred over earlier ones when conflicts in inherited permission values occur.
			 * Here's the kicker: This behaviour depends on PHP's internal array ordering, which maintains insertion
			 * order -- BUT -- assigning to an existing index replaces the old value WITHOUT changing the order.
			 * (what crazy person thought relying on this this was a good idea?!?!?!?!?!)
			 *
			 * This removes the old value so that the new value will be added at the end of the array's internal order
			 * instead of directly taking the place of the older value.
			 */
			unset($this->permissions[$name]);
		}
		$this->permissions[$name] = $value;
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string|Permission $name
	 *
	 * @return void
	 */
	public function unsetPermission($name){
		$name = $name instanceof Permission ? $name->getName() : $name;
		if(isset($this->permissions[$name])){
			unset($this->permissions[$name]);
			$this->permissible->recalculatePermissions();
		}
	}

	/**
	 * @return void
	 */
	public function remove(){
		$this->permissible->removeAttachment($this);
	}
}
