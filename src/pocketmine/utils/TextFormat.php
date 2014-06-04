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


/**
 * Class used to handle Minecraft chat format, and convert it to other formats like ANSI or HTML
 */
class TextFormat{
	const BLACK = "§0";
	const DARK_BLUE = "§1";
	const DARK_GREEN = "§2";
	const DARK_AQUA = "§3";
	const DARK_RED = "§4";
	const DARK_PURPLE = "§5";
	const GOLD = "§6";
	const GRAY = "§7";
	const DARK_GRAY = "§8";
	const BLUE = "§9";
	const GREEN = "§a";
	const AQUA = "§b";
	const RED = "§c";
	const LIGHT_PURPLE = "§d";
	const YELLOW = "§e";
	const WHITE = "§f";

	const OBFUSCATED = "§k";
	const BOLD = "§l";
	const STRIKETHROUGH = "§m";
	const UNDERLINE = "§n";
	const ITALIC = "§o";
	const RESET = "§r";

	/**
	 * Splits the string by Format tokens
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	public static function tokenize($string){
		return preg_split("/(§[0123456789abcdefklmnor])/", $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	}

	/**
	 * Cleans the string from Minecraft codes and ANSI Escape Codes
	 *
	 * @param string $string
	 *
	 * @return mixed
	 */
	public static function clean($string){
		return preg_replace(array("/§[0123456789abcdefklmnor]/", "/\\x1b*/"), "", $string);
	}

	/**
	 * Returns an HTML-formatted string with colors/markup
	 *
	 * @param string|array $string
	 *
	 * @return string
	 */
	public static function toHTML($string){
		if(!is_array($string)){
			$string = self::tokenize($string);
		}
		$newString = "";
		$tokens = 0;
		foreach($string as $token){
			switch($token){
				case TextFormat::BOLD:
					$newString .= "<span style=font-weight:bold>";
					++$tokens;
					break;
				case TextFormat::OBFUSCATED:
					//$newString .= "<span style=text-decoration:line-through>";
					//++$tokens;
					break;
				case TextFormat::ITALIC:
					$newString .= "<span style=font-style:italic>";
					++$tokens;
					break;
				case TextFormat::UNDERLINE:
					$newString .= "<span style=text-decoration:underline>";
					++$tokens;
					break;
				case TextFormat::STRIKETHROUGH:
					$newString .= "<span style=text-decoration:line-through>";
					++$tokens;
					break;
				case TextFormat::RESET:
					$newString .= str_repeat("</span>", $tokens);
					$tokens = 0;
					break;

				//Colors
				case TextFormat::BLACK:
					$newString .= "<span style=color:#000>";
					++$tokens;
					break;
				case TextFormat::DARK_BLUE:
					$newString .= "<span style=color:#00A>";
					++$tokens;
					break;
				case TextFormat::DARK_GREEN:
					$newString .= "<span style=color:#0A0>";
					++$tokens;
					break;
				case TextFormat::DARK_AQUA:
					$newString .= "<span style=color:#0AA>";
					++$tokens;
					break;
				case TextFormat::DARK_RED:
					$newString .= "<span style=color:#A00>";
					++$tokens;
					break;
				case TextFormat::DARK_PURPLE:
					$newString .= "<span style=color:#A0A>";
					++$tokens;
					break;
				case TextFormat::GOLD:
					$newString .= "<span style=color:#FA0>";
					++$tokens;
					break;
				case TextFormat::GRAY:
					$newString .= "<span style=color:#AAA>";
					++$tokens;
					break;
				case TextFormat::DARK_GRAY:
					$newString .= "<span style=color:#555>";
					++$tokens;
					break;
				case TextFormat::BLUE:
					$newString .= "<span style=color:#55F>";
					++$tokens;
					break;
				case TextFormat::GREEN:
					$newString .= "<span style=color:#5F5>";
					++$tokens;
					break;
				case TextFormat::AQUA:
					$newString .= "<span style=color:#5FF>";
					++$tokens;
					break;
				case TextFormat::RED:
					$newString .= "<span style=color:#F55>";
					++$tokens;
					break;
				case TextFormat::LIGHT_PURPLE:
					$newString .= "<span style=color:#F5F>";
					++$tokens;
					break;
				case TextFormat::YELLOW:
					$newString .= "<span style=color:#FF5>";
					++$tokens;
					break;
				case TextFormat::WHITE:
					$newString .= "<span style=color:#FFF>";
					++$tokens;
					break;
				default:
					$newString .= $token;
					break;
			}
		}

		$newString .= str_repeat("</span>", $tokens);

		return $newString;
	}

	/**
	 * Returns a string with colorized ANSI Escape codes
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public static function toANSI($string){
		if(!is_array($string)){
			$string = self::tokenize($string);
		}
		$newString = "";
		foreach($string as $token){
			switch($token){
				case TextFormat::BOLD:
					break;
				case TextFormat::OBFUSCATED:
					$newString .= "\x1b[8m";
					break;
				case TextFormat::ITALIC:
					$newString .= "\x1b[3m";
					break;
				case TextFormat::UNDERLINE:
					$newString .= "\x1b[4m";
					break;
				case TextFormat::STRIKETHROUGH:
					$newString .= "\x1b[9m";
					break;
				case TextFormat::RESET:
					$newString .= "\x1b[0m";
					break;

				//Colors
				case TextFormat::BLACK:
					$newString .= "\x1b[0;30m";
					break;
				case TextFormat::DARK_BLUE:
					$newString .= "\x1b[0;34m";
					break;
				case TextFormat::DARK_GREEN:
					$newString .= "\x1b[0;32m";
					break;
				case TextFormat::DARK_AQUA:
					$newString .= "\x1b[0;36m";
					break;
				case TextFormat::DARK_RED:
					$newString .= "\x1b[0;31m";
					break;
				case TextFormat::DARK_PURPLE:
					$newString .= "\x1b[0;35m";
					break;
				case TextFormat::GOLD:
					$newString .= "\x1b[0;33m";
					break;
				case TextFormat::GRAY:
					$newString .= "\x1b[0;37m";
					break;
				case TextFormat::DARK_GRAY:
					$newString .= "\x1b[30;1m";
					break;
				case TextFormat::BLUE:
					$newString .= "\x1b[34;1m";
					break;
				case TextFormat::GREEN:
					$newString .= "\x1b[32;1m";
					break;
				case TextFormat::AQUA:
					$newString .= "\x1b[36;1m";
					break;
				case TextFormat::RED:
					$newString .= "\x1b[31;1m";
					break;
				case TextFormat::LIGHT_PURPLE:
					$newString .= "\x1b[35;1m";
					break;
				case TextFormat::YELLOW:
					$newString .= "\x1b[33;1m";
					break;
				case TextFormat::WHITE:
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