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

abstract class DefaultPermissions{
	/**
	 * @param Permission $perm
	 * @param Permission $parent
	 *
	 * @return Permission
	 */
	public static function registerPermission(Permission $perm, Permission $parent = null) : Permission{
		if($parent instanceof Permission){
			$parent->getChildren()[$perm->getName()] = true;
		}

		PermissionManager::getInstance()->addPermission($perm);

		return PermissionManager::getInstance()->getPermission($perm->getName());
	}

	public static function registerCorePermissions() : void{
		$tree = yaml_parse(file_get_contents(\pocketmine\RESOURCE_PATH . "permissions.yml"));
		self::registerTree(null, $tree);
	}

	private static function registerTree(?Permission $parent, array $children) : void{
		foreach($children as $name => $contents){
			$default = $contents["default"] ?? "op";
			if(is_bool($default)){
				$default = $default ? "true" : "false";
			}
			$description = $contents["description"];
			$name = ($parent !== null ? $parent->getName() : "") . $name;
			$node = self::registerPermission(new Permission($name, $description, $default), $parent);
			self::registerTree($node, $contents["children"] ?? []);
			$node->recalculatePermissibles();
		}
	}
}
