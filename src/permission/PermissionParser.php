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

use function is_bool;
use function strtolower;

class PermissionParser{

	public const DEFAULT_OP = "op";
	public const DEFAULT_NOT_OP = "notop";
	public const DEFAULT_TRUE = "true";
	public const DEFAULT_FALSE = "false";

	public const DEFAULT_STRING_MAP = [
		"op" => self::DEFAULT_OP,
		"isop" => self::DEFAULT_OP,
		"operator" => self::DEFAULT_OP,
		"isoperator" => self::DEFAULT_OP,
		"admin" => self::DEFAULT_OP,
		"isadmin" => self::DEFAULT_OP,

		"!op" => self::DEFAULT_NOT_OP,
		"notop" => self::DEFAULT_NOT_OP,
		"!operator" => self::DEFAULT_NOT_OP,
		"notoperator" => self::DEFAULT_NOT_OP,
		"!admin" => self::DEFAULT_NOT_OP,
		"notadmin" => self::DEFAULT_NOT_OP,

		"true" => self::DEFAULT_TRUE,
		"false" => self::DEFAULT_FALSE,
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
	 * @return Permission[][]
	 * @phpstan-return array<string, list<Permission>>
	 */
	public static function loadPermissions(array $data, string $default = self::DEFAULT_FALSE) : array{
		$result = [];
		foreach($data as $key => $entry){
			self::loadPermission($key, $entry, $default, $result);
		}

		return $result;
	}

	/**
	 * @param mixed[]        $data
	 * @param Permission[][] $output reference parameter
	 * @phpstan-param array<string, mixed> $data
	 * @phpstan-param array<string, list<Permission>> $output
	 *
	 * @throws \Exception
	 */
	public static function loadPermission(string $name, array $data, string $default = self::DEFAULT_FALSE, array &$output = []) : void{
		$desc = null;
		if(isset($data["default"])){
			$default = PermissionParser::defaultFromString($data["default"]);
		}

		if(isset($data["children"])){
			throw new \InvalidArgumentException("Nested permission declarations are no longer supported. Declare each permission separately.");
		}

		if(isset($data["description"])){
			$desc = $data["description"];
		}

		$output[$default][] = new Permission($name, $desc);
	}
}
