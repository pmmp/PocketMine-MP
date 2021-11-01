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

use function fclose;
use function fopen;
use function function_exists;
use function getenv;
use function is_array;
use function is_string;
use function sapi_windows_vt100_support;
use function shell_exec;
use function stream_isatty;

abstract class Terminal{
	/** @var string */
	public static $FORMAT_BOLD = "";
	/** @var string */
	public static $FORMAT_OBFUSCATED = "";
	/** @var string */
	public static $FORMAT_ITALIC = "";
	/** @var string */
	public static $FORMAT_UNDERLINE = "";
	/** @var string */
	public static $FORMAT_STRIKETHROUGH = "";

	/** @var string */
	public static $FORMAT_RESET = "";

	/** @var string */
	public static $COLOR_BLACK = "";
	/** @var string */
	public static $COLOR_DARK_BLUE = "";
	/** @var string */
	public static $COLOR_DARK_GREEN = "";
	/** @var string */
	public static $COLOR_DARK_AQUA = "";
	/** @var string */
	public static $COLOR_DARK_RED = "";
	/** @var string */
	public static $COLOR_PURPLE = "";
	/** @var string */
	public static $COLOR_GOLD = "";
	/** @var string */
	public static $COLOR_GRAY = "";
	/** @var string */
	public static $COLOR_DARK_GRAY = "";
	/** @var string */
	public static $COLOR_BLUE = "";
	/** @var string */
	public static $COLOR_GREEN = "";
	/** @var string */
	public static $COLOR_AQUA = "";
	/** @var string */
	public static $COLOR_RED = "";
	/** @var string */
	public static $COLOR_LIGHT_PURPLE = "";
	/** @var string */
	public static $COLOR_YELLOW = "";
	/** @var string */
	public static $COLOR_WHITE = "";

	/** @var bool|null */
	private static $formattingCodes = null;

	public static function hasFormattingCodes() : bool{
		if(self::$formattingCodes === null){
			throw new \InvalidStateException("Formatting codes have not been initialized");
		}
		return self::$formattingCodes;
	}

	private static function detectFormattingCodesSupport() : bool{
		$stdout = fopen("php://stdout", "w");
		if($stdout === false) throw new AssumptionFailedError("Opening php://stdout should never fail");
		$result = (
			stream_isatty($stdout) and //STDOUT isn't being piped
			(
				getenv('TERM') !== false or //Console says it supports colours
				(function_exists('sapi_windows_vt100_support') and sapi_windows_vt100_support($stdout)) //we're on windows and have vt100 support
			)
		);
		fclose($stdout);
		return $result;
	}

	/**
	 * @return void
	 */
	protected static function getFallbackEscapeCodes(){
		self::$FORMAT_BOLD = "\x1b[1m";
		self::$FORMAT_OBFUSCATED = "";
		self::$FORMAT_ITALIC = "\x1b[3m";
		self::$FORMAT_UNDERLINE = "\x1b[4m";
		self::$FORMAT_STRIKETHROUGH = "\x1b[9m";

		self::$FORMAT_RESET = "\x1b[m";

		self::$COLOR_BLACK = "\x1b[38;5;16m";
		self::$COLOR_DARK_BLUE = "\x1b[38;5;19m";
		self::$COLOR_DARK_GREEN = "\x1b[38;5;34m";
		self::$COLOR_DARK_AQUA = "\x1b[38;5;37m";
		self::$COLOR_DARK_RED = "\x1b[38;5;124m";
		self::$COLOR_PURPLE = "\x1b[38;5;127m";
		self::$COLOR_GOLD = "\x1b[38;5;214m";
		self::$COLOR_GRAY = "\x1b[38;5;145m";
		self::$COLOR_DARK_GRAY = "\x1b[38;5;59m";
		self::$COLOR_BLUE = "\x1b[38;5;63m";
		self::$COLOR_GREEN = "\x1b[38;5;83m";
		self::$COLOR_AQUA = "\x1b[38;5;87m";
		self::$COLOR_RED = "\x1b[38;5;203m";
		self::$COLOR_LIGHT_PURPLE = "\x1b[38;5;207m";
		self::$COLOR_YELLOW = "\x1b[38;5;227m";
		self::$COLOR_WHITE = "\x1b[38;5;231m";
	}

	/**
	 * @return void
	 */
	protected static function getEscapeCodes(){
		$tput = fn(string $args) => is_string($result = shell_exec("tput $args")) ? $result : "";
		$setaf = fn(int $code) => $tput("setaf $code");

		self::$FORMAT_BOLD = $tput("bold");
		self::$FORMAT_OBFUSCATED = $tput("smacs");
		self::$FORMAT_ITALIC = $tput("sitm");
		self::$FORMAT_UNDERLINE = $tput("smul");
		self::$FORMAT_STRIKETHROUGH = "\x1b[9m"; //`tput `;

		self::$FORMAT_RESET = $tput("sgr0");

		$colors = (int) $tput("colors");
		if($colors > 8){
			self::$COLOR_BLACK = $colors >= 256 ? $setaf(16) : $setaf(0);
			self::$COLOR_DARK_BLUE = $colors >= 256 ? $setaf(19) : $setaf(4);
			self::$COLOR_DARK_GREEN = $colors >= 256 ? $setaf(34) : $setaf(2);
			self::$COLOR_DARK_AQUA = $colors >= 256 ? $setaf(37) : $setaf(6);
			self::$COLOR_DARK_RED = $colors >= 256 ? $setaf(124) : $setaf(1);
			self::$COLOR_PURPLE = $colors >= 256 ? $setaf(127) : $setaf(5);
			self::$COLOR_GOLD = $colors >= 256 ? $setaf(214) : $setaf(3);
			self::$COLOR_GRAY = $colors >= 256 ? $setaf(145) : $setaf(7);
			self::$COLOR_DARK_GRAY = $colors >= 256 ? $setaf(59) : $setaf(8);
			self::$COLOR_BLUE = $colors >= 256 ? $setaf(63) : $setaf(12);
			self::$COLOR_GREEN = $colors >= 256 ? $setaf(83) : $setaf(10);
			self::$COLOR_AQUA = $colors >= 256 ? $setaf(87) : $setaf(14);
			self::$COLOR_RED = $colors >= 256 ? $setaf(203) : $setaf(9);
			self::$COLOR_LIGHT_PURPLE = $colors >= 256 ? $setaf(207) : $setaf(13);
			self::$COLOR_YELLOW = $colors >= 256 ? $setaf(227) : $setaf(11);
			self::$COLOR_WHITE = $colors >= 256 ? $setaf(231) : $setaf(15);
		}else{
			self::$COLOR_BLACK = self::$COLOR_DARK_GRAY = $setaf(0);
			self::$COLOR_RED = self::$COLOR_DARK_RED = $setaf(1);
			self::$COLOR_GREEN = self::$COLOR_DARK_GREEN = $setaf(2);
			self::$COLOR_YELLOW = self::$COLOR_GOLD = $setaf(3);
			self::$COLOR_BLUE = self::$COLOR_DARK_BLUE = $setaf(4);
			self::$COLOR_LIGHT_PURPLE = self::$COLOR_PURPLE = $setaf(5);
			self::$COLOR_AQUA = self::$COLOR_DARK_AQUA = $setaf(6);
			self::$COLOR_GRAY = self::$COLOR_WHITE = $setaf(7);
		}
	}

	public static function init(?bool $enableFormatting = null) : void{
		self::$formattingCodes = $enableFormatting ?? self::detectFormattingCodesSupport();
		if(!self::$formattingCodes){
			return;
		}

		switch(Utils::getOS()){
			case Utils::OS_LINUX:
			case Utils::OS_MACOS:
			case Utils::OS_BSD:
				self::getEscapeCodes();
				return;

			case Utils::OS_WINDOWS:
			case Utils::OS_ANDROID:
				self::getFallbackEscapeCodes();
				return;
		}

		//TODO: iOS
	}

	public static function isInit() : bool{
		return self::$formattingCodes !== null;
	}

	/**
	 * Returns a string with colorized ANSI Escape codes for the current terminal
	 * Note that this is platform-dependent and might produce different results depending on the terminal type and/or OS.
	 *
	 * @param string|string[] $string
	 */
	public static function toANSI($string) : string{
		if(!is_array($string)){
			$string = TextFormat::tokenize($string);
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

	/**
	 * Emits a string containing Minecraft colour codes to the console formatted with native colours.
	 */
	public static function write(string $line) : void{
		echo self::toANSI($line);
	}

	/**
	 * Emits a string containing Minecraft colour codes to the console formatted with native colours, followed by a
	 * newline character.
	 */
	public static function writeLine(string $line) : void{
		echo self::toANSI($line) . self::$FORMAT_RESET . PHP_EOL;
	}
}
