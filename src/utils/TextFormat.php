<?php

/**
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

define("FORMAT_BLACK", "§0");
define("FORMAT_DARK_BLUE", "§1");
define("FORMAT_DARK_GREEN", "§2");
define("FORMAT_DARK_AQUA", "§3");
define("FORMAT_DARK_RED", "§4");
define("FORMAT_DARK_PURPLE", "§5");
define("FORMAT_GOLD", "§6");
define("FORMAT_GRAY", "§7");
define("FORMAT_DARK_GRAY", "§8");
define("FORMAT_BLUE", "§9");
define("FORMAT_GREEN", "§a");
define("FORMAT_AQUA", "§b");
define("FORMAT_RED", "§c");
define("FORMAT_LIGHT_PURPLE", "§d");
define("FORMAT_YELLOW", "§e");
define("FORMAT_WHITE", "§f");

define("FORMAT_OBFUSCATED", "§k");
define("FORMAT_BOLD", "§l");
define("FORMAT_STRIKETHROUGH", "§m");
define("FORMAT_UNDERLINE", "§n");
define("FORMAT_ITALIC", "§o");
define("FORMAT_RESET", "§r");


class TextFormat{
	public static function tokenize($string){
		return preg_split("/(§[0123456789abcdefklmnor])/", $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	}

	public static function clean($string){
		return preg_replace("/§[0123456789abcdefklmnor]/", "", $string);
	}
	
	public static function toANSI($string){
		if(!is_array($string)){
			$string = self::tokenize($string);
		}
		$newString = "";
		foreach($string as $token){
			switch($token){
				case FORMAT_BOLD:
					break;
				case FORMAT_OBFUSCATED:
					$newString .= "\x1b[8m";
					break;
				case FORMAT_ITALIC:
					$newString .= "\x1b[3m";
					break;
				case FORMAT_UNDERLINE:
					$newString .= "\x1b[4m";
					break;
				case FORMAT_STRIKETHROUGH:
					$newString .= "\x1b[9m";
					break;
				case FORMAT_RESET:
					$newString .= "\x1b[0m";
					break;
				//Colors
				case FORMAT_BLACK:
					$newString .= "\x1b[30m";
					break;
				case FORMAT_DARK_BLUE:
					$newString .= "\x1b[34m";
					break;
				case FORMAT_DARK_GREEN:
					$newString .= "\x1b[32m";
					break;
				case FORMAT_DARK_AQUA:
					$newString .= "\x1b[36m";
					break;
				case FORMAT_DARK_RED:
					$newString .= "\x1b[31m";
					break;
				case FORMAT_DARK_PURPLE:
					$newString .= "\x1b[35m";
					break;
				case FORMAT_GOLD:
					$newString .= "\x1b[33m";
					break;
				case FORMAT_GRAY:
					$newString .= "\x1b[37m";
					break;
				case FORMAT_DARK_GRAY:
					$newString .= "\x1b[30;1m";
					break;
				case FORMAT_BLUE:
					$newString .= "\x1b[34;1m";
					break;
				case FORMAT_GREEN:
					$newString .= "\x1b[32;1m";
					break;
				case FORMAT_AQUA:
					$newString .= "\x1b[36;1m";
					break;
				case FORMAT_RED:
					$newString .= "\x1b[31;1m";
					break;
				case FORMAT_LIGHT_PURPLE:
					$newString .= "\x1b[35;1m";
					break;
				case FORMAT_YELLOW:
					$newString .= "\x1b[33;1m";
					break;
				case FORMAT_WHITE:
					$newString .= "\x1b[37;1m";
					break;
				default:
					$newString .= $token;
					break;
			}
		}
		return $newString;
	}

}