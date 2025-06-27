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

use function mb_scrub;
use function preg_last_error;
use function preg_quote;
use function preg_replace;
use function preg_split;
use function str_repeat;
use function str_replace;
use const PREG_BACKTRACK_LIMIT_ERROR;
use const PREG_BAD_UTF8_ERROR;
use const PREG_BAD_UTF8_OFFSET_ERROR;
use const PREG_INTERNAL_ERROR;
use const PREG_JIT_STACKLIMIT_ERROR;
use const PREG_RECURSION_LIMIT_ERROR;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

/**
 * Class used to handle Minecraft chat format, and convert it to other formats like HTML
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
	public const MINECOIN_GOLD = TextFormat::ESCAPE . "g";
	public const MATERIAL_QUARTZ = TextFormat::ESCAPE . "h";
	public const MATERIAL_IRON = TextFormat::ESCAPE . "i";
	public const MATERIAL_NETHERITE = TextFormat::ESCAPE . "j";
	public const MATERIAL_REDSTONE = TextFormat::ESCAPE . "m";
	public const MATERIAL_COPPER = TextFormat::ESCAPE . "n";
	public const MATERIAL_GOLD = TextFormat::ESCAPE . "p";
	public const MATERIAL_EMERALD = TextFormat::ESCAPE . "q";
	public const MATERIAL_DIAMOND = TextFormat::ESCAPE . "s";
	public const MATERIAL_LAPIS = TextFormat::ESCAPE . "t";
	public const MATERIAL_AMETHYST = TextFormat::ESCAPE . "u";
	public const MATERIAL_RESIN = TextFormat::ESCAPE . "v";

	public const COLORS = [
		self::BLACK => self::BLACK,
		self::DARK_BLUE => self::DARK_BLUE,
		self::DARK_GREEN => self::DARK_GREEN,
		self::DARK_AQUA => self::DARK_AQUA,
		self::DARK_RED => self::DARK_RED,
		self::DARK_PURPLE => self::DARK_PURPLE,
		self::GOLD => self::GOLD,
		self::GRAY => self::GRAY,
		self::DARK_GRAY => self::DARK_GRAY,
		self::BLUE => self::BLUE,
		self::GREEN => self::GREEN,
		self::AQUA => self::AQUA,
		self::RED => self::RED,
		self::LIGHT_PURPLE => self::LIGHT_PURPLE,
		self::YELLOW => self::YELLOW,
		self::WHITE => self::WHITE,
		self::MINECOIN_GOLD => self::MINECOIN_GOLD,
		self::MATERIAL_QUARTZ => self::MATERIAL_QUARTZ,
		self::MATERIAL_IRON => self::MATERIAL_IRON,
		self::MATERIAL_NETHERITE => self::MATERIAL_NETHERITE,
		self::MATERIAL_REDSTONE => self::MATERIAL_REDSTONE,
		self::MATERIAL_COPPER => self::MATERIAL_COPPER,
		self::MATERIAL_GOLD => self::MATERIAL_GOLD,
		self::MATERIAL_EMERALD => self::MATERIAL_EMERALD,
		self::MATERIAL_DIAMOND => self::MATERIAL_DIAMOND,
		self::MATERIAL_LAPIS => self::MATERIAL_LAPIS,
		self::MATERIAL_AMETHYST => self::MATERIAL_AMETHYST,
		self::MATERIAL_RESIN => self::MATERIAL_RESIN,
	];

	public const OBFUSCATED = TextFormat::ESCAPE . "k";
	public const BOLD = TextFormat::ESCAPE . "l";
	public const ITALIC = TextFormat::ESCAPE . "o";

	public const FORMATS = [
		self::OBFUSCATED => self::OBFUSCATED,
		self::BOLD => self::BOLD,
		self::ITALIC => self::ITALIC,
	];

	public const RESET = TextFormat::ESCAPE . "r";

	private static function makePcreError() : \InvalidArgumentException{
		$errorCode = preg_last_error();
		$message = [
			PREG_INTERNAL_ERROR => "Internal error",
			PREG_BACKTRACK_LIMIT_ERROR => "Backtrack limit reached",
			PREG_RECURSION_LIMIT_ERROR => "Recursion limit reached",
			PREG_BAD_UTF8_ERROR => "Malformed UTF-8",
			PREG_BAD_UTF8_OFFSET_ERROR => "Bad UTF-8 offset",
			PREG_JIT_STACKLIMIT_ERROR => "PCRE JIT stack limit reached"
		][$errorCode] ?? "Unknown (code $errorCode)";
		throw new \InvalidArgumentException("PCRE error: $message");
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private static function preg_replace(string $pattern, string $replacement, string $string) : string{
		$result = preg_replace($pattern, $replacement, $string);
		if($result === null){
			throw self::makePcreError();
		}
		return $result;
	}

	/**
	 * Splits the string by Format tokens
	 *
	 * @return string[]
	 */
	public static function tokenize(string $string) : array{
		$result = preg_split("/(" . TextFormat::ESCAPE . "[0-9a-v])/u", $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		if($result === false) throw self::makePcreError();
		return $result;
	}

	/**
	 * Cleans the string from Minecraft codes, ANSI Escape Codes and invalid UTF-8 characters
	 *
	 * @return string valid clean UTF-8
	 */
	public static function clean(string $string, bool $removeFormat = true) : string{
		$string = mb_scrub($string, 'UTF-8');
		$string = self::preg_replace("/[\x{E000}-\x{F8FF}]/u", "", $string); //remove unicode private-use-area characters (they might break the console)
		if($removeFormat){
			$string = str_replace(TextFormat::ESCAPE, "", self::preg_replace("/" . TextFormat::ESCAPE . "[0-9a-v]/u", "", $string));
		}
		return str_replace("\x1b", "", self::preg_replace("/\x1b[\\(\\][[0-9;\\[\\(]+[Bm]/u", "", $string));
	}

	/**
	 * Replaces placeholders of § with the correct character. Only valid codes (as in the constants of the TextFormat class) will be converted.
	 *
	 * @param string $placeholder default "&"
	 */
	public static function colorize(string $string, string $placeholder = "&") : string{
		return self::preg_replace('/' . preg_quote($placeholder, "/") . '([0-9a-v])/u', TextFormat::ESCAPE . '$1', $string);
	}

	/**
	 * Adds base formatting to the string. The given format codes will be inserted directly after any RESET (§r) codes.
	 *
	 * This is useful for log messages, where a RESET code should return to the log message's original colour (e.g.
	 * blue for NOTICE), rather than whatever the terminal's base text colour is (usually some off-white colour).
	 *
	 * Example behaviour:
	 * - Base format "§c" (red) + "Hello" (no format) = "§r§cHello"
	 * - Base format "§c" + "Hello §rWorld" = "§r§cHello §r§cWorld"
	 *
	 * Note: Adding base formatting to the output string a second time will result in a combination of formats from both
	 * calls. This is not by design, but simply a consequence of the way the function is implemented.
	 */
	public static function addBase(string $baseFormat, string $string) : string{
		$baseFormatParts = self::tokenize($baseFormat);
		foreach($baseFormatParts as $part){
			if(!isset(self::FORMATS[$part]) && !isset(self::COLORS[$part])){
				throw new \InvalidArgumentException("Unexpected base format token \"$part\", expected only color and format tokens");
			}
		}
		$baseFormat = self::RESET . $baseFormat;

		return $baseFormat . str_replace(TextFormat::RESET, $baseFormat, $string);
	}

	/**
	 * Converts any Java formatting codes in the given string to Bedrock.
	 *
	 * As of 1.21.50, strikethrough (§m) and underline (§n) are not supported by Bedrock, and these symbols are instead
	 * used to represent additional colours in Bedrock. To avoid unintended formatting, this function currently strips
	 * those formatting codes to prevent unintended colour display in formatted text.
	 *
	 * If Bedrock starts to support these formats in the future, this function will be updated to translate them rather
	 * than removing them.
	 */
	public static function javaToBedrock(string $string) : string{
		return str_replace([TextFormat::ESCAPE . "m", TextFormat::ESCAPE . "n"], "", $string);
	}

	/**
	 * Returns an HTML-formatted string with colors/markup
	 */
	public static function toHTML(string $string) : string{
		$newString = "";
		$tokens = 0;
		foreach(self::tokenize($string) as $token){
			$formatString = match($token){
				TextFormat::BLACK => "color:#000",
				TextFormat::DARK_BLUE => "color:#00A",
				TextFormat::DARK_GREEN => "color:#0A0",
				TextFormat::DARK_AQUA => "color:#0AA",
				TextFormat::DARK_RED => "color:#A00",
				TextFormat::DARK_PURPLE => "color:#A0A",
				TextFormat::GOLD => "color:#FA0",
				TextFormat::GRAY => "color:#AAA",
				TextFormat::DARK_GRAY => "color:#555",
				TextFormat::BLUE => "color:#55F",
				TextFormat::GREEN => "color:#5F5",
				TextFormat::AQUA => "color:#5FF",
				TextFormat::RED => "color:#F55",
				TextFormat::LIGHT_PURPLE => "color:#F5F",
				TextFormat::YELLOW => "color:#FF5",
				TextFormat::WHITE => "color:#FFF",
				TextFormat::MINECOIN_GOLD => "color:#dd0",
				TextFormat::MATERIAL_QUARTZ => "color:#e2d3d1",
				TextFormat::MATERIAL_IRON => "color:#cec9c9",
				TextFormat::MATERIAL_NETHERITE => "color:#44393a",
				TextFormat::MATERIAL_REDSTONE => "color:#961506",
				TextFormat::MATERIAL_COPPER => "color:#b4684d",
				TextFormat::MATERIAL_GOLD => "color:#deb02c",
				TextFormat::MATERIAL_EMERALD => "color:#119f36",
				TextFormat::MATERIAL_DIAMOND => "color:#2cb9a8",
				TextFormat::MATERIAL_LAPIS => "color:#20487a",
				TextFormat::MATERIAL_AMETHYST => "color:#9a5cc5",
				TextFormat::MATERIAL_RESIN => "color:#fc7812",
				TextFormat::BOLD => "font-weight:bold",
				TextFormat::ITALIC => "font-style:italic",
				default => null
			};
			if($formatString !== null){
				$newString .= "<span style=\"$formatString\">";
				++$tokens;
			}elseif($token === TextFormat::RESET){
				$newString .= str_repeat("</span>", $tokens);
				$tokens = 0;
			}else{
				$newString .= $token;
			}
		}

		$newString .= str_repeat("</span>", $tokens);

		return $newString;
	}
}
