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
use function spl_object_id;

class PermissibleBase implements Permissible{
	/** @var bool */
	private $op;

	/** @var Permissible|null */
	private $parent;

	/** @var PermissionAttachment[] */
	private $attachments = [];

	/** @var PermissionAttachmentInfo[] */
	private $permissions = [];

	public function __construct(?Permissible $permissible, bool $isOp){
		$this->parent = $permissible;
		$this->op = $isOp;
		//TODO: permissions need to be recalculated here, or inherited permissions won't work
	}

	private function getRootPermissible() : Permissible{
		return $this->parent ?? $this;
	}

	public function isOp() : bool{
		return $this->op;
	}

	public function onOpStatusChange(bool $value) : void{
		$this->op = $value;
		$this->getRootPermissible()->recalculatePermissions();
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

		$result = new PermissionAttachment($plugin, $this->getRootPermissible());
		$this->attachments[spl_object_id($result)] = $result;
		if($name !== null and $value !== null){
			$result->setPermission($name, $value);
		}

		$this->getRootPermissible()->recalculatePermissions();

		return $result;
	}

	public function removeAttachment(PermissionAttachment $attachment) : void{
		if(isset($this->attachments[spl_object_id($attachment)])){
			unset($this->attachments[spl_object_id($attachment)]);
			if(($ex = $attachment->getRemovalCallback()) !== null){
				$ex->attachmentRemoved($attachment);
			}

			$this->getRootPermissible()->recalculatePermissions();

		}

	}

	public function recalculatePermissions() : void{
		Timings::$permissibleCalculationTimer->startTiming();

		$this->clearPermissions();
		$permManager = PermissionManager::getInstance();
		$defaults = $permManager->getDefaultPermissions($this->isOp());
		$permManager->subscribeToDefaultPerms($this->isOp(), $this->getRootPermissible());

		foreach($defaults as $perm){
			$name = $perm->getName();
			$this->permissions[$name] = new PermissionAttachmentInfo($this->getRootPermissible(), $name, null, true);
			$permManager->subscribeToPermission($name, $this->getRootPermissible());
			$this->calculateChildPermissions($perm->getChildren(), false, null);
		}

		foreach($this->attachments as $attachment){
			$this->calculateChildPermissions($attachment->getPermissions(), false, $attachment);
		}

		Timings::$permissibleCalculationTimer->stopTiming();
	}

	public function clearPermissions() : void{
		$permManager = PermissionManager::getInstance();
		$permManager->unsubscribeFromAllPermissions($this->getRootPermissible());

		$permManager->unsubscribeFromDefaultPerms(false, $this->getRootPermissible());
		$permManager->unsubscribeFromDefaultPerms(true, $this->getRootPermissible());

		$this->permissions = [];
	}

	/**
	 * @param bool[]                    $children
	 */
	private function calculateChildPermissions(array $children, bool $invert, ?PermissionAttachment $attachment) : void{
		$permManager = PermissionManager::getInstance();
		foreach($children as $name => $v){
			$perm = $permManager->getPermission($name);
			$value = ($v xor $invert);
			$this->permissions[$name] = new PermissionAttachmentInfo($this->getRootPermissible(), $name, $attachment, $value);
			$permManager->subscribeToPermission($name, $this->getRootPermissible());

			if($perm instanceof Permission){
				$this->calculateChildPermissions($perm->getChildren(), !$value, $attachment);
			}
		}
	}

	/**
	 * @return PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions() : array{
		return $this->permissions;
	}
}
