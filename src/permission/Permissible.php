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

use Ds\Set;
use pocketmine\plugin\Plugin;

interface Permissible{

	/**
	 * Assigns a baseline permission to the permissible. This is **always** calculated before anything else, which means
	 * that permissions set using addAttachment() will always override base permissions.
	 * You probably don't want to use this if you're not assigning (denying) operator permissions.
	 *
	 * @internal
	 * @see Permissible::addAttachment() for normal permission assignments
	 * @param Permission|string $name
	 */
	public function setBasePermission($name, bool $grant) : void;

	/**
	 * Unsets a baseline permission previously set. If it wasn't already set, this will have no effect.
	 * Note that this might have different results than setting the permission to false.
	 *
	 * @internal
	 * @param Permission|string $name
	 */
	public function unsetBasePermission($name) : void;

	/**
	 * Checks if this instance has a permission overridden
	 *
	 * @param string|Permission $name
	 */
	public function isPermissionSet($name) : bool;

	/**
	 * Returns the permission value if overridden, or the default value if not
	 *
	 * @param string|Permission $name
	 */
	public function hasPermission($name) : bool;

	public function addAttachment(Plugin $plugin, ?string $name = null, ?bool $value = null) : PermissionAttachment;

	public function removeAttachment(PermissionAttachment $attachment) : void;

	/**
	 * @return bool[] changed permission name => old value
	 * @phpstan-return array<string, bool>
	 */
	public function recalculatePermissions() : array;

	/**
	 * @return Set|\Closure[]
	 * @phpstan-return Set<\Closure(array<string, bool> $changedPermissionsOldValues) : void>
	 */
	public function getPermissionRecalculationCallbacks() : Set;

	/**
	 * @return PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions() : array;

}
