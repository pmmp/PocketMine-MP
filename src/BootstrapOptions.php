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

/**
 * Constants for all the command-line options that PocketMine-MP supports.
 * Other options not listed here can be used to override server.properties and pocketmine.yml values temporarily.
 *
 * @internal
 */
final class BootstrapOptions{

	private function __construct(){
		//NOOP
	}

	/** Disables the setup wizard on first startup */
	public const NO_WIZARD = "no-wizard";
	/** Force-disables console text colour and formatting */
	public const DISABLE_ANSI = "disable-ansi";
	/** Force-enables console text colour and formatting */
	public const ENABLE_ANSI = "enable-ansi";
	/** Path to look in for plugins */
	public const PLUGINS = "plugins";
	/** Path to store and load server data */
	public const DATA = "data";
	/** Shows basic server version information and exits */
	public const VERSION = "version";
	/** Disables writing logs to server.log */
	public const NO_LOG_FILE = "no-log-file";
}
