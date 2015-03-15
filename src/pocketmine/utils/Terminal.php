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

namespace pocketmine\utils;

abstract class Terminal{
	public static $FORMAT_BOLD = "";
	public static $FORMAT_OBFUSCATED = "";
	public static $FORMAT_ITALIC = "";
	public static $FORMAT_UNDERLINE = "";
	public static $FORMAT_STRIKETHROUGH = "";

	public static $FORMAT_RESET = "";

	public static $COLOR_BLACK = "";
	public static $COLOR_DARK_BLUE = "";
	public static $COLOR_DARK_GREEN = "";
	public static $COLOR_DARK_AQUA = "";
	public static $COLOR_DARK_RED = "";
	public static $COLOR_PURPLE = "";
	public static $COLOR_GOLD = "";
	public static $COLOR_GRAY = "";
	public static $COLOR_DARK_GRAY = "";
	public static $COLOR_BLUE = "";
	public static $COLOR_GREEN = "";
	public static $COLOR_AQUA = "";
	public static $COLOR_RED = "";
	public static $COLOR_LIGHT_PURPLE = "";
	public static $COLOR_YELLOW = "";
	public static $COLOR_WHITE = "";

	private static $formattingCodes = null;

	public static function hasFormattingCodes(){
		if(self::$formattingCodes === null){
			$opts = getopt("", ["enable-ansi", "disable-ansi"]);
			if(isset($opts["disable-ansi"])){
				self::$hasFormattingCodes = false;
			}
			self::$formattingCodes = ((Utils::getOS() !== "win" and getenv("TERM") !== "") or isset($opts["enable-ansi"]));
		}

		return self::$formattingCodes;
	}

	protected static function getEscapeCodes(){
		self::$FORMAT_BOLD = `tput bold`;
		self::$FORMAT_OBFUSCATED = `tput smacs`;
		self::$FORMAT_ITALIC = `tput sitm`;
		self::$FORMAT_UNDERLINE = `tput smul`;
		self::$FORMAT_STRIKETHROUGH = "\x1b[9m"; //`tput `;

		self::$FORMAT_RESET = `tput sgr0`;

		$colors = (int) `tput colors`;
		if($colors > 8){
			self::$COLOR_BLACK = $colors >= 256 ? `tput setaf 16` : `tput setaf 0`;
			self::$COLOR_DARK_BLUE = $colors >= 256 ? `tput setaf 19` : `tput setaf 4`;
			self::$COLOR_DARK_GREEN = $colors >= 256 ? `tput setaf 34` : `tput setaf 2`;
			self::$COLOR_DARK_AQUA = $colors >= 256 ? `tput setaf 37` : `tput setaf 6`;
			self::$COLOR_DARK_RED = $colors >= 256 ? `tput setaf 124` : `tput setaf 1`;
			self::$COLOR_PURPLE = $colors >= 256 ? `tput setaf 127` : `tput setaf 5`;
			self::$COLOR_GOLD = $colors >= 256 ? `tput setaf 214` : `tput setaf 3`;
			self::$COLOR_GRAY = $colors >= 256 ? `tput setaf 145` : `tput setaf 7`;
			self::$COLOR_DARK_GRAY = $colors >= 256 ? `tput setaf 59` : `tput setaf 8`;
			self::$COLOR_BLUE = $colors >= 256 ? `tput setaf 63` : `tput setaf 12`;
			self::$COLOR_GREEN = $colors >= 256 ? `tput setaf 83` : `tput setaf 10`;
			self::$COLOR_AQUA = $colors >= 256 ? `tput setaf 87` : `tput setaf 14`;
			self::$COLOR_RED = $colors >= 256 ? `tput setaf 203` : `tput setaf 9`;
			self::$COLOR_LIGHT_PURPLE = $colors >= 256 ? `tput setaf 207` : `tput setaf 13`;
			self::$COLOR_YELLOW = $colors >= 256 ? `tput setaf 227` : `tput setaf 11`;
			self::$COLOR_WHITE = $colors >= 256 ? `tput setaf 231` : `tput setaf 15`;
		}else{
			self::$COLOR_BLACK = self::$COLOR_DARK_GRAY = `tput setaf 0`;
			self::$COLOR_RED = self::$COLOR_DARK_RED = `tput setaf 1`;
			self::$COLOR_GREEN = self::$COLOR_DARK_GREEN = `tput setaf 2`;
			self::$COLOR_YELLOW = self::$COLOR_GOLD = `tput setaf 3`;
			self::$COLOR_BLUE = self::$COLOR_DARK_BLUE = `tput setaf 4`;
			self::$COLOR_LIGHT_PURPLE = self::$COLOR_PURPLE = `tput setaf 5`;
			self::$COLOR_AQUA = self::$COLOR_DARK_AQUA = `tput setaf 6`;
			self::$COLOR_GRAY = self::$COLOR_WHITE = `tput setaf 7`;
		}
	}

	public static function init(){
		switch(Utils::getOS()){
			case "linux":
			case "mac":
			case "bsd":
				self::getEscapeCodes();
				return;
		}

		//TODO: Android, Windows iOS
	}

}