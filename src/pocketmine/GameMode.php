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

namespace pocketmine;

final class GameMode{
	public const SURVIVAL = 0;
	public const CREATIVE = 1;
	public const ADVENTURE = 2;
	public const SPECTATOR = 3;
	public const VIEW = GameMode::SPECTATOR;

	private function __construct(){
		//NOOP
	}

	/**
	 * Parses a string and returns a gamemode integer, -1 if not found
	 *
	 * @param string $str
	 *
	 * @return int
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function fromString(string $str) : int{
		switch(strtolower(trim($str))){
			case (string) self::SURVIVAL:
			case "survival":
			case "s":
				return self::SURVIVAL;

			case (string) self::CREATIVE:
			case "creative":
			case "c":
				return self::CREATIVE;

			case (string) self::ADVENTURE:
			case "adventure":
			case "a":
				return self::ADVENTURE;

			case (string) self::SPECTATOR:
			case "spectator":
			case "view":
			case "v":
				return self::SPECTATOR;
		}

		throw new \InvalidArgumentException("Unknown gamemode string \"$str\"");
	}

	/**
	 * Returns the gamemode text name
	 *
	 * @param int $mode
	 *
	 * @return string
	 */
	public static function toTranslation(int $mode) : string{
		switch($mode){
			case self::SURVIVAL:
				return "%gameMode.survival";
			case self::CREATIVE:
				return "%gameMode.creative";
			case self::ADVENTURE:
				return "%gameMode.adventure";
			case self::SPECTATOR:
				return "%gameMode.spectator";
		}

		return "UNKNOWN";
	}

	public static function toString(int $mode) : string{
		switch($mode){
			case self::SURVIVAL:
				return "Survival";
			case self::CREATIVE:
				return "Creative";
			case self::ADVENTURE:
				return "Adventure";
			case self::SPECTATOR:
				return "Spectator";
			default:
				throw new \InvalidArgumentException("Invalid gamemode $mode");
		}
	}

	//TODO: ability sets per gamemode
}
