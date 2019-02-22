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

use function file_get_contents;

abstract class DefaultPermissions{
	public const ROOT = "pocketmine";

	/**
	 * @param Permission $perm
	 * @param Permission $parent
	 *
	 * @return Permission
	 */
	public static function registerPermission(Permission $perm, ?Permission $parent = null) : Permission{
		if($parent instanceof Permission){
			$parent->getChildren()[$perm->getName()] = true;

			return self::registerPermission($perm);
		}
		PermissionManager::getInstance()->addPermission($perm);

		return PermissionManager::getInstance()->getPermission($perm->getName());
	}

	public static function registerCorePermissions(){
		$manager = PermissionManager::getInstance();
		foreach(PermissionParser::loadPermissions(yaml_parse(file_get_contents(\pocketmine\RESOURCE_PATH . 'default_permissions.yml'))) as $permission){
			$manager->addPermission($permission);
		}
	}
}
