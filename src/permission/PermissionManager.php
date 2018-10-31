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

use pocketmine\timings\Timings;

class PermissionManager{
	/** @var PermissionManager|null */
	private static $instance = null;

	public static function getInstance() : PermissionManager{
		if(self::$instance === null){
			self::$instance = new self;
		}

		return self::$instance;
	}

	/** @var Permission[] */
	protected $permissions = [];
	/** @var Permission[] */
	protected $defaultPerms = [];
	/** @var Permission[] */
	protected $defaultPermsOp = [];
	/** @var Permissible[][] */
	protected $permSubs = [];
	/** @var Permissible[] */
	protected $defSubs = [];
	/** @var Permissible[] */
	protected $defSubsOp = [];

	/**
	 * @param string $name
	 *
	 * @return null|Permission
	 */
	public function getPermission(string $name){
		return $this->permissions[$name] ?? null;
	}

	/**
	 * @param Permission $permission
	 *
	 * @return bool
	 */
	public function addPermission(Permission $permission) : bool{
		if(!isset($this->permissions[$permission->getName()])){
			$this->permissions[$permission->getName()] = $permission;
			$this->calculatePermissionDefault($permission);

			return true;
		}

		return false;
	}

	/**
	 * @param string|Permission $permission
	 */
	public function removePermission($permission){
		if($permission instanceof Permission){
			unset($this->permissions[$permission->getName()]);
		}else{
			unset($this->permissions[$permission]);
		}
	}

	/**
	 * @param bool $op
	 *
	 * @return Permission[]
	 */
	public function getDefaultPermissions(bool $op) : array{
		if($op){
			return $this->defaultPermsOp;
		}else{
			return $this->defaultPerms;
		}
	}

	/**
	 * @param Permission $permission
	 */
	public function recalculatePermissionDefaults(Permission $permission){
		if(isset($this->permissions[$permission->getName()])){
			unset($this->defaultPermsOp[$permission->getName()]);
			unset($this->defaultPerms[$permission->getName()]);
			$this->calculatePermissionDefault($permission);
		}
	}

	/**
	 * @param Permission $permission
	 */
	private function calculatePermissionDefault(Permission $permission){
		Timings::$permissionDefaultTimer->startTiming();
		if($permission->getDefault() === Permission::DEFAULT_OP or $permission->getDefault() === Permission::DEFAULT_TRUE){
			$this->defaultPermsOp[$permission->getName()] = $permission;
			$this->dirtyPermissibles(true);
		}

		if($permission->getDefault() === Permission::DEFAULT_NOT_OP or $permission->getDefault() === Permission::DEFAULT_TRUE){
			$this->defaultPerms[$permission->getName()] = $permission;
			$this->dirtyPermissibles(false);
		}
		Timings::$permissionDefaultTimer->startTiming();
	}

	/**
	 * @param bool $op
	 */
	private function dirtyPermissibles(bool $op){
		foreach($this->getDefaultPermSubscriptions($op) as $p){
			$p->recalculatePermissions();
		}
	}

	/**
	 * @param string      $permission
	 * @param Permissible $permissible
	 */
	public function subscribeToPermission(string $permission, Permissible $permissible){
		if(!isset($this->permSubs[$permission])){
			$this->permSubs[$permission] = [];
		}
		$this->permSubs[$permission][spl_object_hash($permissible)] = $permissible;
	}

	/**
	 * @param string      $permission
	 * @param Permissible $permissible
	 */
	public function unsubscribeFromPermission(string $permission, Permissible $permissible){
		if(isset($this->permSubs[$permission])){
			unset($this->permSubs[$permission][spl_object_hash($permissible)]);
			if(count($this->permSubs[$permission]) === 0){
				unset($this->permSubs[$permission]);
			}
		}
	}

	/**
	 * @param Permissible $permissible
	 */
	public function unsubscribeFromAllPermissions(Permissible $permissible) : void{
		foreach($this->permSubs as $permission => &$subs){
			unset($subs[spl_object_hash($permissible)]);
			if(empty($subs)){
				unset($this->permSubs[$permission]);
			}
		}
	}

	/**
	 * @param string $permission
	 *
	 * @return array|Permissible[]
	 */
	public function getPermissionSubscriptions(string $permission) : array{
		return $this->permSubs[$permission] ?? [];
	}

	/**
	 * @param bool        $op
	 * @param Permissible $permissible
	 */
	public function subscribeToDefaultPerms(bool $op, Permissible $permissible){
		if($op){
			$this->defSubsOp[spl_object_hash($permissible)] = $permissible;
		}else{
			$this->defSubs[spl_object_hash($permissible)] = $permissible;
		}
	}

	/**
	 * @param bool        $op
	 * @param Permissible $permissible
	 */
	public function unsubscribeFromDefaultPerms(bool $op, Permissible $permissible){
		if($op){
			unset($this->defSubsOp[spl_object_hash($permissible)]);
		}else{
			unset($this->defSubs[spl_object_hash($permissible)]);
		}
	}

	/**
	 * @param bool $op
	 *
	 * @return Permissible[]
	 */
	public function getDefaultPermSubscriptions(bool $op) : array{
		if($op){
			return $this->defSubsOp;
		}

		return $this->defSubs;
	}

	/**
	 * @return Permission[]
	 */
	public function getPermissions() : array{
		return $this->permissions;
	}

	public function clearPermissions() : void{
		$this->permissions = [];
		$this->defaultPerms = [];
		$this->defaultPermsOp = [];
	}
}
