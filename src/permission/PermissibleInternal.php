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
use pocketmine\timings\Timings;
use pocketmine\utils\ObjectSet;
use function count;
use function spl_object_id;

/**
 * @internal
 * This class SHOULD NOT be instantiated directly. Its constructor may create references to the object, which will never
 * be cleaned up unless destroyCycles() is called.
 * PermissibleBase automates this cleanup, and should be used instead of this class.
 *
 * @see PermissibleBase
 */
class PermissibleInternal implements Permissible{
	/**
	 * @var bool[]
	 * @phpstan-var array<string, bool>
	 */
	private $rootPermissions;

	/** @var PermissionAttachment[] */
	private $attachments = [];

	/** @var PermissionAttachmentInfo[] */
	private $permissions = [];

	/**
	 * @var ObjectSet|\Closure[]
	 * @phpstan-var ObjectSet<\Closure(array<string, bool> $changedPermissionsOldValues) : void>
	 */
	private $permissionRecalculationCallbacks;

	/**
	 * @param bool[] $basePermissions
	 * @phpstan-param array<string, bool> $basePermissions
	 */
	public function __construct(array $basePermissions){
		$this->permissionRecalculationCallbacks = new ObjectSet();

		$this->rootPermissions = $basePermissions;
		$this->recalculatePermissions();
	}

	public function setBasePermission($name, bool $grant) : void{
		if($name instanceof Permission){
			$name = $name->getName();
		}
		$this->rootPermissions[$name] = $grant;
		$this->recalculatePermissions();
	}

	public function unsetBasePermission($name) : void{
		unset($this->rootPermissions[$name instanceof Permission ? $name->getName() : $name]);
		$this->recalculatePermissions();
	}

	/**
	 * @param Permission|string $name
	 */
	public function isPermissionSet($name) : bool{
		return isset($this->permissions[$name instanceof Permission ? $name->getName() : $name]);
	}

	/**
	 * @param Permission|string $name
	 */
	public function hasPermission($name) : bool{
		if($name instanceof Permission){
			$name = $name->getName();
		}

		if($this->isPermissionSet($name)){
			return $this->permissions[$name]->getValue();
		}

		return false;
	}

	/**
	 * //TODO: tick scheduled attachments
	 */
	public function addAttachment(Plugin $plugin, ?string $name = null, ?bool $value = null) : PermissionAttachment{
		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin " . $plugin->getDescription()->getName() . " is disabled");
		}

		$result = new PermissionAttachment($plugin);
		$this->attachments[spl_object_id($result)] = $result;
		if($name !== null and $value !== null){
			$result->setPermission($name, $value);
		}

		$result->subscribePermissible($this);

		$this->recalculatePermissions();

		return $result;
	}

	public function removeAttachment(PermissionAttachment $attachment) : void{
		if(isset($this->attachments[spl_object_id($attachment)])){
			unset($this->attachments[spl_object_id($attachment)]);
			$attachment->unsubscribePermissible($this);

			$this->recalculatePermissions();

		}

	}

	public function recalculatePermissions() : array{
		Timings::$permissibleCalculation->startTiming();

		$permManager = PermissionManager::getInstance();
		$permManager->unsubscribeFromAllPermissions($this);
		$oldPermissions = $this->permissions;
		$this->permissions = [];

		foreach($this->rootPermissions as $name => $isGranted){
			$perm = $permManager->getPermission($name);
			if($perm === null){
				throw new \LogicException("Unregistered root permission $name");
			}
			$this->permissions[$name] = new PermissionAttachmentInfo($name, null, $isGranted, null);
			$permManager->subscribeToPermission($name, $this);
			$this->calculateChildPermissions($perm->getChildren(), !$isGranted, null, $this->permissions[$name]);
		}

		foreach($this->attachments as $attachment){
			$this->calculateChildPermissions($attachment->getPermissions(), false, $attachment, null);
		}

		$diff = [];
		Timings::$permissibleCalculationDiff->time(function() use ($oldPermissions, &$diff) : void{
			foreach($this->permissions as $permissionAttachmentInfo){
				$name = $permissionAttachmentInfo->getPermission();
				if(!isset($oldPermissions[$name])){
					$diff[$name] = false;
				}elseif($oldPermissions[$name]->getValue() !== $permissionAttachmentInfo->getValue()){
					continue;
				}
				unset($oldPermissions[$name]);
			}
			//oldPermissions now only contains permissions that changed or are no longer set
			foreach($oldPermissions as $permissionAttachmentInfo){
				$diff[$permissionAttachmentInfo->getPermission()] = $permissionAttachmentInfo->getValue();
			}
		});

		Timings::$permissibleCalculationCallback->time(function() use ($diff) : void{
			if(count($diff) > 0){
				foreach($this->permissionRecalculationCallbacks as $closure){
					$closure($diff);
				}
			}
		});

		Timings::$permissibleCalculation->stopTiming();
		return $diff;
	}

	/**
	 * @param bool[]                    $children
	 */
	private function calculateChildPermissions(array $children, bool $invert, ?PermissionAttachment $attachment, ?PermissionAttachmentInfo $parent) : void{
		$permManager = PermissionManager::getInstance();
		foreach($children as $name => $v){
			$perm = $permManager->getPermission($name);
			$value = ($v xor $invert);
			$this->permissions[$name] = new PermissionAttachmentInfo($name, $attachment, $value, $parent);
			$permManager->subscribeToPermission($name, $this);

			if($perm instanceof Permission){
				$this->calculateChildPermissions($perm->getChildren(), !$value, $attachment, $this->permissions[$name]);
			}
		}
	}

	/**
	 * @return \Closure[]|ObjectSet
	 * @phpstan-return ObjectSet<\Closure(array<string, bool> $changedPermissionsOldValues) : void>
	 */
	public function getPermissionRecalculationCallbacks() : ObjectSet{ return $this->permissionRecalculationCallbacks; }

	/**
	 * @return PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions() : array{
		return $this->permissions;
	}

	public function destroyCycles() : void{
		PermissionManager::getInstance()->unsubscribeFromAllPermissions($this);
		$this->permissions = []; //PermissionAttachmentInfo doesn't reference Permissible anymore, but it references PermissionAttachment which does
		foreach($this->attachments as $attachment){
			$attachment->unsubscribePermissible($this);
		}
		$this->attachments = [];
		$this->permissionRecalculationCallbacks->clear();
	}
}
