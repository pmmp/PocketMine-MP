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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class CommandParameter{
	/** @var string */
	public $paramName;
	/** @var int */
	public $paramType;
	/** @var bool */
	public $isOptional;
	/** @var int */
	public $byte1 = 0; //unknown, always zero except for in /gamerule command
	/** @var CommandEnum|null */
	public $enum;
	/** @var string|null */
	public $postfix;

	private static function baseline(string $name, int $type, bool $optional) : self{
		$result = new self;
		$result->paramName = $name;
		$result->paramType = $type;
		$result->isOptional = $optional;
		return $result;
	}

	public static function standard(string $name, int $type, bool $optional = false) : self{
		return self::baseline($name, AvailableCommandsPacket::ARG_FLAG_VALID | $type, $optional);
	}

	public static function postfixed(string $name, string $postfix, bool $optional = false) : self{
		$result = self::baseline($name, AvailableCommandsPacket::ARG_FLAG_POSTFIX, $optional);
		$result->postfix = $postfix;
		return $result;
	}

	public static function enum(string $name, CommandEnum $enum, int $flags, bool $optional = false) : self{
		$result = self::baseline($name, AvailableCommandsPacket::ARG_FLAG_ENUM | AvailableCommandsPacket::ARG_FLAG_VALID, $optional);
		$result->enum = $enum;
		$result->byte1 = $flags;
		return $result;
	}
}
