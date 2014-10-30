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

namespace pocketmine\permission;


class PermissionAttachmentInfo{
	/** @var Permissible */
	private $permissible;

	/** @var string */
	private $permission;

	/** @var PermissionAttachment */
	private $attachment;

	/** @var bool */
	private $value;

	/**
	 * @param Permissible          $permissible
	 * @param string               $permission
	 * @param PermissionAttachment $attachment
	 * @param bool                 $value
	 *
	 * @throws \InvalidStateException
	 */
	public function __construct(Permissible $permissible, $permission, $attachment, $value){
		if($permission === null){
			throw new \InvalidStateException("Permission may not be null");
		}

		$this->permissible = $permissible;
		$this->permission = $permission;
		$this->attachment = $attachment;
		$this->value = $value;
	}

	/**
	 * @return Permissible
	 */
	public function getPermissible(){
		return $this->permissible;
	}

	/**
	 * @return string
	 */
	public function getPermission(){
		return $this->permission;
	}

	/**
	 * @return PermissionAttachment
	 */
	public function getAttachment(){
		return $this->attachment;
	}

	/**
	 * @return bool
	 */
	public function getValue(){
		return $this->value;
	}
}