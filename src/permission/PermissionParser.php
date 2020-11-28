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

use function count;
use function is_array;
use function is_bool;
use function ksort;
use function strtolower;

class PermissionParser{

	public const DEFAULT_STRING_MAP = [
		"op" => Permission::DEFAULT_OP,
		"isop" => Permission::DEFAULT_OP,
		"operator" => Permission::DEFAULT_OP,
		"isoperator" => Permission::DEFAULT_OP,
		"admin" => Permission::DEFAULT_OP,
		"isadmin" => Permission::DEFAULT_OP,

		"!op" => Permission::DEFAULT_NOT_OP,
		"notop" => Permission::DEFAULT_NOT_OP,
		"!operator" => Permission::DEFAULT_NOT_OP,
		"notoperator" => Permission::DEFAULT_NOT_OP,
		"!admin" => Permission::DEFAULT_NOT_OP,
		"notadmin" => Permission::DEFAULT_NOT_OP,

		"true" => Permission::DEFAULT_TRUE,
		"false" => Permission::DEFAULT_FALSE,
	];

	/**
	 * @param bool|string $value
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function defaultFromString($value) : string{
		if(is_bool($value)){
			if($value){
				return "true";
			}else{
				return "false";
			}
		}
		$lower = strtolower($value);
		if(isset(self::DEFAULT_STRING_MAP[$lower])){
			return self::DEFAULT_STRING_MAP[$lower];
		}

		throw new \InvalidArgumentException("Unknown permission default name \"$value\"");
	}

	/**
	 * @param mixed[][] $data
	 * @phpstan-param array<string, array<string, mixed>> $data
	 *
	 * @return Permission[]
	 */
	public static function loadPermissions(array $data, string $default = Permission::DEFAULT_OP) : array{
		$result = [];
		foreach($data as $key => $entry){
			$result[] = self::loadPermission($key, $entry, $default, $result);
		}

		return $result;
	}

	/**
	 * @param mixed[]      $data
	 * @param Permission[] $output reference parameter
	 * @phpstan-param array<string, mixed> $data
	 *
	 * @throws \Exception
	 */
	public static function loadPermission(string $name, array $data, string $default = Permission::DEFAULT_OP, array &$output = []) : Permission{
		$desc = null;
		$children = [];
		if(isset($data["default"])){
			$default = PermissionParser::defaultFromString($data["default"]);
		}

		if(isset($data["children"])){
			if(is_array($data["children"])){
				foreach($data["children"] as $k => $v){
					if(is_array($v)){
						$output[] = self::loadPermission($k, $v, $default, $output);
					}
					$children[$k] = true;
				}
			}else{
				throw new \InvalidStateException("'children' key is of wrong type");
			}
		}

		if(isset($data["description"])){
			$desc = $data["description"];
		}

		return new Permission($name, $desc, $default, $children);
	}
}
