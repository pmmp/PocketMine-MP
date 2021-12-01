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
use function is_string;
use function sapi_windows_vt100_support;
use function shell_exec;
use function stream_isatty;
use const PHP_EOL;

abstract class Terminal{
	public static string $FORMAT_BOLD = "";
	public static string $FORMAT_OBFUSCATED = "";
	public static string $FORMAT_ITALIC = "";
	public static string $FORMAT_UNDERLINE = "";
	public static string $FORMAT_STRIKETHROUGH = "";

	public static string $FORMAT_RESET = "";

	public static string $COLOR_BLACK = "";
	public static string $COLOR_DARK_BLUE = "";
	public static string $COLOR_DARK_GREEN = "";
	public static string $COLOR_DARK_AQUA = "";
	public static string $COLOR_DARK_RED = "";
	public static string $COLOR_PURPLE = "";
	public static string $COLOR_GOLD = "";
	public static string $COLOR_GRAY = "";
	public static string $COLOR_DARK_GRAY = "";
	public static string $COLOR_BLUE = "";
	public static string $COLOR_GREEN = "";
	public static string $COLOR_AQUA = "";
	public static string $COLOR_RED = "";
	public static string $COLOR_LIGHT_PURPLE = "";
	public static string $COLOR_YELLOW = "";
	public static string $COLOR_WHITE = "";

	/** @var bool|null */
	private static $formattingCodes = null;

	public static function hasFormattingCodes() : bool{
		if(self::$formattingCodes === null){
			throw new \LogicException("Formatting codes have not been initialized");
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

	protected static function getFallbackEscapeCodes() : void{
		self::$FORMAT_BOLD = "\x1b[1m";
		self::$FORMAT_OBFUSCATED = "";
		self::$FORMAT_ITALIC = "\x1b[3m";
		self::$FORMAT_UNDERLINE = "\x1b[4m";
		self::$FORMAT_STRIKETHROUGH = "\x1b[9m";

		self::$FORMAT_RESET = "\x1b[m";

		$color = fn(int $code) => "\x1b[38;5;${code}m";

		self::$COLOR_BLACK = $color(16);
		self::$COLOR_DARK_BLUE = $color(19);
		self::$COLOR_DARK_GREEN = $color(34);
		self::$COLOR_DARK_AQUA = $color(37);
		self::$COLOR_DARK_RED = $color(124);
		self::$COLOR_PURPLE = $color(127);
		self::$COLOR_GOLD = $color(214);
		self::$COLOR_GRAY = $color(145);
		self::$COLOR_DARK_GRAY = $color(59);
		self::$COLOR_BLUE = $color(63);
		self::$COLOR_GREEN = $color(83);
		self::$COLOR_AQUA = $color(87);
		self::$COLOR_RED = $color(203);
		self::$COLOR_LIGHT_PURPLE = $color(207);
		self::$COLOR_YELLOW = $color(227);
		self::$COLOR_WHITE = $color(231);
	}

	protected static function getEscapeCodes() : void{
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
	 */
	public static function toANSI(string $string) : string{
		$newString = "";
		foreach(TextFormat::tokenize($string) as $token){
			$newString .= match($token){
				TextFormat::BOLD => Terminal::$FORMAT_BOLD,
				TextFormat::OBFUSCATED => Terminal::$FORMAT_OBFUSCATED,
				TextFormat::ITALIC => Terminal::$FORMAT_ITALIC,
				TextFormat::UNDERLINE => Terminal::$FORMAT_UNDERLINE,
				TextFormat::STRIKETHROUGH => Terminal::$FORMAT_STRIKETHROUGH,
				TextFormat::RESET => Terminal::$FORMAT_RESET,
				TextFormat::BLACK => Terminal::$COLOR_BLACK,
				TextFormat::DARK_BLUE => Terminal::$COLOR_DARK_BLUE,
				TextFormat::DARK_GREEN => Terminal::$COLOR_DARK_GREEN,
				TextFormat::DARK_AQUA => Terminal::$COLOR_DARK_AQUA,
				TextFormat::DARK_RED => Terminal::$COLOR_DARK_RED,
				TextFormat::DARK_PURPLE => Terminal::$COLOR_PURPLE,
				TextFormat::GOLD => Terminal::$COLOR_GOLD,
				TextFormat::GRAY => Terminal::$COLOR_GRAY,
				TextFormat::DARK_GRAY => Terminal::$COLOR_DARK_GRAY,
				TextFormat::BLUE => Terminal::$COLOR_BLUE,
				TextFormat::GREEN => Terminal::$COLOR_GREEN,
				TextFormat::AQUA => Terminal::$COLOR_AQUA,
				TextFormat::RED => Terminal::$COLOR_RED,
				TextFormat::LIGHT_PURPLE => Terminal::$COLOR_LIGHT_PURPLE,
				TextFormat::YELLOW => Terminal::$COLOR_YELLOW,
				TextFormat::WHITE => Terminal::$COLOR_WHITE,
				default => $token,
			};
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
