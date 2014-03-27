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

use PocketMine\Plugin\Plugin;

class PermissionAttachment{
	/**
	 * @var PermissionRemovedExecutor
	 */
	private $removed = null;

	/**
	 * @var bool[]
	 */
	private $permissions = array();

	/**
	 * @var Permissible
	 */
	private $permissible;

	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @param Plugin      $plugin
	 * @param Permissible $permissible
	 */
	public function __construct(Plugin $plugin, Permissible $permissible){
		if($plugin === null){
			trigger_error("Plugin cannot be null", E_USER_WARNING);
			return;
		}elseif(!$plugin->isEnabled()){
			trigger_error("Plugin ".$plugin->getDescription()->getName()." is disabled", E_USER_WARNING);
			return;
		}

		$this->permissible = $permissible;
		$this->plugin = $plugin;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(){
		return $this->plugin;
	}

	/**
	 * @param PermissionRemovedExecutor $ex
	 */
	public function setRemovalCallback(PermissionRemovedExecutor $ex){
		$this->removed = $ex;
	}

	/**
	 * @return PermissionRemovedExecutor
	 */
	public function getRemovalCallback(){
		return $this->removed;
	}

	/**
	 * @return Permissible
	 */
	public function getPermissible(){
		return $this->permissible;
	}

	/**
	 * @return bool[]
	 */
	public function getPermissions(){
		return $this->permissions;
	}

	/**
	 * @param string|Permission $name
	 * @param bool $value
	 */
	public function setPermission($name, $value){
		$this->permissions[$name instanceof Permission ? $name->getName() : $name] = $value;
		$this->permissible->recalculatePermissions();
	}

	/**
	 * @param string|Permission $name
	 */
	public function unsetPermission($name){
		unset($this->permissions[$name instanceof Permission ? $name->getName() : $name]);
	}

	/**
	 * @return void
	 */
	public function remove(){
		$this->permissible->removeAttachment($this);
	}
}