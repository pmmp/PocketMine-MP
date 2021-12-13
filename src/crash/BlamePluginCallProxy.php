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

namespace pocketmine\crash;

/**
 * @internal
 */
final class BlamePluginCallProxy{
	public const FILE = __FILE__;

	private function __construct(){
		//NOOP
	}

	public static ?string $blamedPlugin = null;

	/**
	 * Wrapper to explicitly blame a plugin for any fatal error or exception that gets thrown by the given Closure.
	 * This is needed because sometimes crash frames will appear in PocketMine-MP code files even if they were caused by
	 * plugins.
	 *
	 * For example, when constructing a plugin's main class, if the class declares a nonexisting constant as a default
	 * value of a property or constant, the error will appear in PluginManager.php, even though the error was actually
	 * caused by the borked main class.
	 *
	 * WARNING: If you catch any exception thrown by this function, be sure to clear the blamedPlugin, otherwise further
	 * errors could be incorrectly blamed on the plugin.
	 *
	 * @phpstan-template TReturn
	 * @phpstan-param \Closure() : TReturn $closure
	 * @phpstan-return TReturn
	 */
	public static function call(string $pluginName, \Closure $closure) : mixed{
		self::$blamedPlugin = $pluginName;
		$result = $closure();
		self::$blamedPlugin = null;
		return $result;
	}
}