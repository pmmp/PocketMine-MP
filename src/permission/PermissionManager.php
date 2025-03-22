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

use pocketmine\Server;
use pocketmine\utils\Utils;
use function count;
use function spl_object_id;

class PermissionManager{
	private static ?self $instance = null;

	public static function getInstance() : PermissionManager{
		if(self::$instance === null){
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @var Permission[]
	 * @phpstan-var array<string, Permission>
	 */
	protected array $permissions = [];
	/**
	 * @var PermissibleInternal[][]
	 * @phpstan-var array<string, array<int, PermissibleInternal>>
	 */
	protected array $permSubs = [];

	public function getPermission(string $name) : ?Permission{
		return $this->permissions[$name] ?? null;
	}

	public function addPermission(Permission $permission) : bool{
		if(!isset($this->permissions[$permission->getName()])){
			$this->permissions[$permission->getName()] = $permission;

			return true;
		}

		return false;
	}

	public function removePermission(Permission|string $permission) : void{
		if($permission instanceof Permission){
			unset($this->permissions[$permission->getName()]);
		}else{
			unset($this->permissions[$permission]);
		}
	}

	/**
	 * @deprecated Superseded by server chat broadcast channels
	 * @see Server::subscribeToBroadcastChannel()
	 */
	public function subscribeToPermission(string $permission, PermissibleInternal $permissible) : void{
		if(!isset($this->permSubs[$permission])){
			$this->permSubs[$permission] = [];
		}
		$this->permSubs[$permission][spl_object_id($permissible)] = $permissible;
	}

	/**
	 * @deprecated Superseded by server chat broadcast channels
	 * @see Server::unsubscribeFromBroadcastChannel()
	 */
	public function unsubscribeFromPermission(string $permission, PermissibleInternal $permissible) : void{
		if(isset($this->permSubs[$permission][spl_object_id($permissible)])){
			if(count($this->permSubs[$permission]) === 1){
				unset($this->permSubs[$permission]);
			}else{
				unset($this->permSubs[$permission][spl_object_id($permissible)]);
			}
		}
	}

	/**
	 * @deprecated Superseded by server chat broadcast channels
	 * @see Server::unsubscribeFromAllBroadcastChannels()
	 */
	public function unsubscribeFromAllPermissions(PermissibleInternal $permissible) : void{
		foreach(Utils::promoteKeys($this->permSubs) as $permission => $subs){
			if(count($subs) === 1 && isset($subs[spl_object_id($permissible)])){
				unset($this->permSubs[$permission]);
			}else{
				unset($this->permSubs[$permission][spl_object_id($permissible)]);
			}
		}
	}

	/**
	 * @deprecated Superseded by server chat broadcast channels
	 * @see Server::getBroadcastChannelSubscribers()
	 * @return PermissibleInternal[]
	 */
	public function getPermissionSubscriptions(string $permission) : array{
		return $this->permSubs[$permission] ?? [];
	}

	/**
	 * @return Permission[]
	 */
	public function getPermissions() : array{
		return $this->permissions;
	}

	public function clearPermissions() : void{
		$this->permissions = [];
	}
}
