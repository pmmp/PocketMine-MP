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

class PermissionAttachmentInfo{
	/** @var Permissible */
	private $permissible;

	/** @var string */
	private $permission;

	/** @var PermissionAttachment|null */
	private $attachment;

	/** @var bool */
	private $value;

	public function __construct(Permissible $permissible, string $permission, PermissionAttachment $attachment = null, bool $value){
		$this->permissible = $permissible;
		$this->permission = $permission;
		$this->attachment = $attachment;
		$this->value = $value;
	}

	public function getPermissible() : Permissible{
		return $this->permissible;
	}

	public function getPermission() : string{
		return $this->permission;
	}

	/**
	 * @return PermissionAttachment|null
	 */
	public function getAttachment(){
		return $this->attachment;
	}

	public function getValue() : bool{
		return $this->value;
	}
}
