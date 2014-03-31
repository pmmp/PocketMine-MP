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
use PocketMine\Server;

class PermissibleBase implements Permissible{
	/** @var ServerOperator */
	private $opable = null;

	/** @var Permissible */
	private $parent;

	/**
	 * @var PermissionAttachment[]
	 */
	private $attachments = array();

	/**
	 * @var PermissionAttachmentInfo[]
	 */
	private $permissions = array();

	/**
	 * @param ServerOperator $opable
	 */
	public function __construct(ServerOperator $opable){
		$this->opable = $opable;
		if($opable instanceof Permissible){
			$this->parent = $opable;
		}else{
			$this->parent = $this;
		}
	}

	/**
	 * @return bool
	 */
	public function isOp(){
		if($this->opable === null){
			return false;
		}else{
			return $this->opable->isOp();
		}
	}

	/**
	 * @param bool $value
	 */
	public function setOp($value){
		if($this->opable === null){
			trigger_error("Cannot change ip value as no ServerOperator is set", E_USER_WARNING);

			return;
		}else{
			$this->opable->setOp($value);
		}
	}

	/**
	 * @param Permission|string $name
	 *
	 * @return bool
	 */
	public function isPermissionSet($name){
		return isset($this->permissions[$name instanceof Permission ? $name->getName() : $name]);
	}

	/**
	 * @param Permission|string $name
	 *
	 * @return bool
	 */
	public function hasPermission($name){
		if($name instanceof Permission){
			$name = $name->getName();
		}

		if($this->isPermissionSet($name)){
			return $this->permissions[$name]->getValue();
		}

		if(($perm = Server::getInstance()->getPluginManager()->getPermission($name)) !== null){
			$perm = $perm->getDefault();
			return $perm === Permission::DEFAULT_TRUE or ($this->isOp() and $perm === Permission::DEFAULT_OP) or (!$this->isOp() and $perm === Permission::DEFAULT_NOT_OP);
		}else{
			return Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_TRUE or ($this->isOp() and Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_OP) or (!$this->isOp() and Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_NOT_OP);
		}

	}

	/**
	 * //TODO: tick scheduled attachments
	 *
	 * @param Plugin $plugin
	 * @param string $name
	 * @param bool   $value
	 *
	 * @return PermissionAttachment
	 */
	public function addAttachment(Plugin $plugin, $name = null, $value = null){
		if($plugin === null){
			trigger_error("Plugin cannot be null", E_USER_WARNING);

			return null;
		}elseif(!$plugin->isEnabled()){
			trigger_error("Plugin " . $plugin->getDescription()->getName() . " is disabled", E_USER_WARNING);

			return null;
		}

		$result = new PermissionAttachment($plugin, $this->parent);
		$this->attachments[spl_object_hash($result)] = $result;
		if($name !== null and $value !== null){
			$result->setPermission($name, $value);
		}

		$this->recalculatePermissions();

		return $result;
	}

	/**
	 * @param PermissionAttachment $attachment
	 *
	 * @return void
	 */
	public function removeAttachment(PermissionAttachment $attachment){
		if($attachment === null){
			trigger_error("Attachment cannot be null", E_USER_WARNING);

			return;
		}

		if(isset($this->attachments[spl_object_hash($attachment)])){
			unset($this->attachments[spl_object_hash($attachment)]);
			if(($ex = $attachment->getRemovalCallback()) !== null){
				$ex->attachmentRemoved($attachment);
			}

			$this->recalculatePermissions();

		}

	}

	public function recalculatePermissions(){
		$this->clearPermissions();
		$defaults = Server::getInstance()->getPluginManager()->getDefaultPermissions($this->isOp());
		Server::getInstance()->getPluginManager()->subscribeToDefaultPerms($this->isOp(), $this->parent);

		foreach($defaults as $perm){
			$name = $perm->getName();
			$this->permissions[$name] = new PermissionAttachmentInfo($this->parent, $name, null, true);
			Server::getInstance()->getPluginManager()->subscribeToPermission($name, $this->parent);
			$this->calculateChildPermissions($perm->getChildren(), false, null);
		}

		foreach($this->attachments as $attachment){
			$this->calculateChildPermissions($attachment->getPermissions(), false, $attachment);
		}
	}

	public function clearPermissions(){
		foreach(array_keys($this->permissions) as $name){
			Server::getInstance()->getPluginManager()->unsubscribeFromPermission($name, $this->parent);
		}

		Server::getInstance()->getPluginManager()->unsubscribeFromDefaultPerms(false, $this->parent);
		Server::getInstance()->getPluginManager()->unsubscribeFromDefaultPerms(true, $this->parent);

		$this->permissions = array();
	}

	/**
	 * @param bool[]               $children
	 * @param bool                 $invert
	 * @param PermissionAttachment $attachment
	 */
	public function calculateChildPermissions(array $children, $invert, $attachment){
		foreach(array_keys($children) as $name){
			$perm = Server::getInstance()->getPluginManager()->getPermission($name);
			$value = $invert === true ? !$children[$name] : $children[$name];
			$this->permissions[$name] = new PermissionAttachmentInfo($this->parent, $name, $attachment, $value);
			Server::getInstance()->getPluginManager()->subscribeToPermission($name, $this->parent);

			if($perm instanceof Permission){
				$this->calculateChildPermissions($perm->getChildren(), !$value, $attachment);
			}
		}
	}

	/**
	 * @return PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions(){
		return $this->permissions;
	}
}