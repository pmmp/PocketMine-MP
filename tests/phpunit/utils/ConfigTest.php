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

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase{

	/**
	 * @return \Generator
	 */
	public function fixYamlIndexesProvider() : \Generator{
		yield ["x: 1\ny: 2\nz: 3\n", ["y"]];
		yield [" x : 1\n y : 2\n z : 3\n", ["y"]];
	}

	/**
	 * @dataProvider fixYamlIndexesProvider
	 *
	 * @param string $test
	 * @param array  $expectedKeys
	 */
	public function testFixYamlIndexes(string $test, array $expectedKeys) : void{
		$fixed = Config::fixYAMLIndexes($test);
		$decoded = yaml_parse($fixed);
		foreach($expectedKeys as $k){
			self::assertArrayHasKey($k, $decoded);
		}
	}
}
