<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class CommandParameter{
	public const FLAG_FORCE_COLLAPSE_ENUM = 0x1;
	public const FLAG_HAS_ENUM_CONSTRAINT = 0x2;

	/** @var string */
	public $paramName;
	/** @var int */
	public $paramType;
	/** @var bool */
	public $isOptional;
	/** @var int */
	public $flags = 0; //shows enum name if 1, always zero except for in /gamerule command
	/** @var CommandEnum|null */
	public $enum;
	/** @var string|null */
	public $postfix;

	public function __construct(string $name = "args", int $type = AvailableCommandsPacket::ARG_TYPE_RAWTEXT, bool $optional = true, $extraData = null, int $flags = 0){
		$this->paramName = $name;
		$this->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | $type;
		$this->isOptional = $optional;
		if($extraData instanceof CommandEnum){
			$this->enum = $extraData;
		}elseif(is_string($extraData)){
			$this->postfix = $extraData;
		}
		$this->flags = $flags;
	}
}