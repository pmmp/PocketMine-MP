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

namespace pocketmine\command\utils;

class NoSelectorMatchException extends CommandException{
	public const NO_TARGET_MATCH = 0;
	public const TARGET_NO_PLAYER = 1;

	public const MESSAGES = [
		self::NO_TARGET_MATCH => "commands.generic.noTargetMatch",
		self::TARGET_NO_PLAYER => "commands.generic.targetNotPlayer"
	];

	public function __construct(int $type){
		$message = self::MESSAGES[$type] ?? self::MESSAGES[self::NO_TARGET_MATCH];
		parent::__construct($message, 0, null);
	}

}