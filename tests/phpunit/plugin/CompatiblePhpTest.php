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

namespace pocketmine\plugin;

use PHPUnit\Framework\TestCase;

class CompatiblePhpTest extends TestCase{

	/**
	 * @return string[][]
	 * @phpstan-return list<array{string, string}>
	 */
	public function incompatiblePhpVersions() : array{
		return [
			["5.0", "5.1.0"],
			["5.2", "5.1.0"],
			["5", "5.0.0"],
			["5.0.0", "7.0.0"],
			["7.0.0", "5.0.0"],
			["7.2.0", "7.1.0"],
			["7.2.0", "7.3.0"],
			["7.1.4", "7.1.3"]
		];
	}

	/**
	 * @return string[][]
	 * @phpstan-return list<array{string, string}>
	 */
	public function compatiblePhpVersions() : array{
		return [
			["5.0", "5.0.0"],
			["5.0", "5.0.2"],
			["5.2", "5.2.2"],
			["5.0.0", "5.0.0"],
			["7.3.0", "7.3.0"],
			["7.1.2", "7.1.5"],
			["8.0.0", "8.0.2"]
		];
	}

	/**
	 * @dataProvider incompatiblePhpVersions
	 */
	public function testIncompatibleVersions(string $pluginVersion, string $serverVersion) : void{
		self::assertFalse(PluginManager::isCompatiblePhp($pluginVersion, $serverVersion));
	}

	/**
	 * @dataProvider compatiblePhpVersions
	 */
	public function testCompatibleVersions(string $pluginVersion, string $serverVersion) : void{
		self::assertTrue(PluginManager::isCompatiblePhp($pluginVersion, $serverVersion));
	}
}
