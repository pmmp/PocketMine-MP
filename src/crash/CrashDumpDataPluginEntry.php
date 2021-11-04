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

final class CrashDumpDataPluginEntry{
	/**
	 * @param string[] $authors
	 * @param string[] $api
	 * @param string[] $depends
	 * @param string[] $softDepends
	 */
	public function __construct(
		public string $name,
		public string $version,
		public array $authors,
		public array $api,
		public bool $enabled,
		public array $depends,
		public array $softDepends,
		public string $main,
		public string $load,
		public string $website,
	){}
}