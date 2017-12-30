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

namespace pocketmine\utils;

/**
 * Class used to handle Minecraft chat format, and convert it to other formats like ANSI or HTML
 */
abstract class TextFormat{
	public const ESCAPE = "\xc2\xa7"; //§
	public const EOL = "\n";

	public const BLACK = TextFormat::ESCAPE . "0";
	public const DARK_BLUE = TextFormat::ESCAPE . "1";
	public const DARK_GREEN = TextFormat::ESCAPE . "2";
	public const DARK_AQUA = TextFormat::ESCAPE . "3";
	public const DARK_RED = TextFormat::ESCAPE . "4";
	public const DARK_PURPLE = TextFormat::ESCAPE . "5";
	public const GOLD = TextFormat::ESCAPE . "6";
	public const GRAY = TextFormat::ESCAPE . "7";
	public const DARK_GRAY = TextFormat::ESCAPE . "8";
	public const BLUE = TextFormat::ESCAPE . "9";
	public const GREEN = TextFormat::ESCAPE . "a";
	public const AQUA = TextFormat::ESCAPE . "b";
	public const RED = TextFormat::ESCAPE . "c";
	public const LIGHT_PURPLE = TextFormat::ESCAPE . "d";
	public const YELLOW = TextFormat::ESCAPE . "e";
	public const WHITE = TextFormat::ESCAPE . "f";

	public const OBFUSCATED = TextFormat::ESCAPE . "k";
	public const BOLD = TextFormat::ESCAPE . "l";
	public const STRIKETHROUGH = TextFormat::ESCAPE . "m";
	public const UNDERLINE = TextFormat::ESCAPE . "n";
	public const ITALIC = TextFormat::ESCAPE . "o";
	public const RESET = TextFormat::ESCAPE . "r";

	/**
	 * Splits the string by Format tokens
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	public static function tokenize(string $string) : array{
		return preg_split("/(" . TextFormat::ESCAPE . "[0123456789abcdefklmnor])/", $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	}

	/**
	 * Cleans the string from Minecraft codes and ANSI Escape Codes
	 *
	 * @param string $string
	 * @param bool   $removeFormat
	 *
	 * @return string
	 */
	public static function clean(string $string, bool $removeFormat = true) : string{
		if($removeFormat){
			return str_replace(TextFormat::ESCAPE, "", preg_replace(["/" . TextFormat::ESCAPE . "[0123456789abcdefklmnor]/", "/\x1b[\\(\\][[0-9;\\[\\(]+[Bm]/"], "", $string));
		}
		return str_replace("\x1b", "", preg_replace("/\x1b[\\(\\][[0-9;\\[\\(]+[Bm]/", "", $string));
	}

	/**
	 * Replaces placeholders of § with the correct character. Only valid codes (as in the constants of the TextFormat class) will be converted.
	 *
	 * @param string $string
	 * @param string $placeholder default "&"
	 *
	 * @return string
	 */
	public static function colorize(string $string, string $placeholder = "&") : string{
		return preg_replace('/' . preg_quote($placeholder, "/") . '([0-9a-fk-or])/u', TextFormat::ESCAPE . '$1', $string);
	}

	/**
	 * Returns an JSON-formatted string with colors/markup
	 *
	 * @param string|array $string
	 *
	 * @return string
	 */
	public static function toJSON($string) : string{
		if(!is_array($string)){
			$string = self::tokenize($string);
		}
		$newString = [];
		$pointer =& $newString;
		$color = "white";
		$bold = false;
		$italic = false;
		$underlined = false;
		$strikethrough = false;
		$obfuscated = false;
		$index = 0;

		foreach($string as $token){
			if(isset($pointer["text"])){
				if(!isset($newString["extra"])){
					$newString["extra"] = [];
				}
				$newString["extra"][$index] = [];
				$pointer =& $newString["extra"][$index];
				if($color !== "white"){
					$pointer["color"] = $color;
				}
				if($bold !== false){
					$pointer["bold"] = true;
				}
				if($italic !== false){
					$pointer["italic"] = true;
				}
				if($underlined !== false){
					$pointer["underlined"] = true;
				}
				if($strikethrough !== false){
					$pointer["strikethrough"] = true;
				}
				if($obfuscated !== false){
					$pointer["obfuscated"] = true;
				}
				++$index;
			}
			switch($token){
				case TextFormat::BOLD:
					if($bold === false){
						$pointer["bold"] = true;
						$bold = true;
					}
					break;
				case TextFormat::OBFUSCATED:
					if($obfuscated === false){
						$pointer["obfuscated"] = true;
						$obfuscated = true;
					}
					break;
				case TextFormat::ITALIC:
					if($italic === false){
						$pointer["italic"] = true;
						$italic = true;
					}
					break;
				case TextFormat::UNDERLINE:
					if($underlined === false){
						$pointer["underlined"] = true;
						$underlined = true;
					}
					break;
				case TextFormat::STRIKETHROUGH:
					if($strikethrough === false){
						$pointer["strikethrough"] = true;
						$strikethrough = true;
					}
					break;
				case TextFormat::RESET:
					if($color !== "white"){
						$pointer["color"] = "white";
						$color = "white";
					}
					if($bold !== false){
						$pointer["bold"] = false;
						$bold = false;
					}
					if($italic !== false){
						$pointer["italic"] = false;
						$italic = false;
					}
					if($underlined !== false){
						$pointer["underlined"] = false;
						$underlined = false;
					}
					if($strikethrough !== false){
						$pointer["strikethrough"] = false;
						$strikethrough = false;
					}
					if($obfuscated !== false){
						$pointer["obfuscated"] = false;
						$obfuscated = false;
					}
					break;

				//Colors
				case TextFormat::BLACK:
					$pointer["color"] = "black";
					$color = "black";
					break;
				case TextFormat::DARK_BLUE:
					$pointer["color"] = "dark_blue";
					$color = "dark_blue";
					break;
				case TextFormat::DARK_GREEN:
					$pointer["color"] = "dark_green";
					$color = "dark_green";
					break;
				case TextFormat::DARK_AQUA:
					$pointer["color"] = "dark_aqua";
					$color = "dark_aqua";
					break;
				case TextFormat::DARK_RED:
					$pointer["color"] = "dark_red";
					$color = "dark_red";
					break;
				case TextFormat::DARK_PURPLE:
					$pointer["color"] = "dark_purple";
					$color = "dark_purple";
					break;
				case TextFormat::GOLD:
					$pointer["color"] = "gold";
					$color = "gold";
					break;
				case TextFormat::GRAY:
					$pointer["color"] = "gray";
					$color = "gray";
					break;
				case TextFormat::DARK_GRAY:
					$pointer["color"] = "dark_gray";
					$color = "dark_gray";
					break;
				case TextFormat::BLUE:
					$pointer["color"] = "blue";
					$color = "blue";
					break;
				case TextFormat::GREEN:
					$pointer["color"] = "green";
					$color = "green";
					break;
				case TextFormat::AQUA:
					$pointer["color"] = "aqua";
					$color = "aqua";
					break;
				case TextFormat::RED:
					$pointer["color"] = "red";
					$color = "red";
					break;
				case TextFormat::LIGHT_PURPLE:
					$pointer["color"] = "light_purple";
					$color = "light_purple";
					break;
				case TextFormat::YELLOW:
					$pointer["color"] = "yellow";
					$color = "yellow";
					break;
				case TextFormat::WHITE:
					$pointer["color"] = "white";
					$color = "white";
					break;
				default:
					$pointer["text"] = $token;
					break;
			}
		}

		if(isset($newString["extra"])){
			foreach($newString["extra"] as $k => $d){
				if(!isset($d["text"])){
					unset($newString["extra"][$k]);
				}
			}
		}

		return json_encode($newString, JSON_UNESCAPED_SLASHES);
	}

	/**
	 * Returns an HTML-formatted string with colors/markup
	 *
	 * @param string|array $string
	 *
	 * @return string
	 */
	public static function toHTML($string) : string{
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
	 * @param string|array $string
	 *
	 * @return string
	 */
	public static function toANSI($string) : string{
		if(!is_array($string)){
			$string = self::tokenize($string);
		}

		$newString = "";
		foreach($string as $token){
			switch($token){
				case TextFormat::BOLD:
					$newString .= Terminal::$FORMAT_BOLD;
					break;
				case TextFormat::OBFUSCATED:
					$newString .= Terminal::$FORMAT_OBFUSCATED;
					break;
				case TextFormat::ITALIC:
					$newString .= Terminal::$FORMAT_ITALIC;
					break;
				case TextFormat::UNDERLINE:
					$newString .= Terminal::$FORMAT_UNDERLINE;
					break;
				case TextFormat::STRIKETHROUGH:
					$newString .= Terminal::$FORMAT_STRIKETHROUGH;
					break;
				case TextFormat::RESET:
					$newString .= Terminal::$FORMAT_RESET;
					break;

				//Colors
				case TextFormat::BLACK:
					$newString .= Terminal::$COLOR_BLACK;
					break;
				case TextFormat::DARK_BLUE:
					$newString .= Terminal::$COLOR_DARK_BLUE;
					break;
				case TextFormat::DARK_GREEN:
					$newString .= Terminal::$COLOR_DARK_GREEN;
					break;
				case TextFormat::DARK_AQUA:
					$newString .= Terminal::$COLOR_DARK_AQUA;
					break;
				case TextFormat::DARK_RED:
					$newString .= Terminal::$COLOR_DARK_RED;
					break;
				case TextFormat::DARK_PURPLE:
					$newString .= Terminal::$COLOR_PURPLE;
					break;
				case TextFormat::GOLD:
					$newString .= Terminal::$COLOR_GOLD;
					break;
				case TextFormat::GRAY:
					$newString .= Terminal::$COLOR_GRAY;
					break;
				case TextFormat::DARK_GRAY:
					$newString .= Terminal::$COLOR_DARK_GRAY;
					break;
				case TextFormat::BLUE:
					$newString .= Terminal::$COLOR_BLUE;
					break;
				case TextFormat::GREEN:
					$newString .= Terminal::$COLOR_GREEN;
					break;
				case TextFormat::AQUA:
					$newString .= Terminal::$COLOR_AQUA;
					break;
				case TextFormat::RED:
					$newString .= Terminal::$COLOR_RED;
					break;
				case TextFormat::LIGHT_PURPLE:
					$newString .= Terminal::$COLOR_LIGHT_PURPLE;
					break;
				case TextFormat::YELLOW:
					$newString .= Terminal::$COLOR_YELLOW;
					break;
				case TextFormat::WHITE:
					$newString .= Terminal::$COLOR_WHITE;
					break;
				default:
					$newString .= $token;
					break;
			}
		}

		return $newString;
	}

}
