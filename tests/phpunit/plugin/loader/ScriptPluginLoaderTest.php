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

namespace pocketmine\plugin\loader;

use PHPUnit\Framework\TestCase;
use function implode;
use function shuffle;

class ScriptPluginLoaderTest extends TestCase{

	public function goodExtensionProvider(){
		$paths = [];
		$parts = ["php", "path", "code", "random", "yml", "phar"];
		for($i = 0; $i <= 10; ++$i){
			shuffle($parts);
			$paths[] = [implode("/", $parts) . ".php"];
		}

		return $paths;
	}

	public function badExtensionProvider(){
		$paths = [];
		$parts = ["php", "path", "code", "random", "yml", "phar"];
		$letters = ["h", "p", "r"];
		for($i = 0; $i <= 10; ++$i){
			shuffle($parts);
			shuffle($letters);
			$paths[] = [implode("/", $parts) . "." . implode("", $letters)];
		}

		return $paths;
	}

	/**
	 * @dataProvider goodExtensionProvider
	 *
	 * @param $path
	 */
	public function testDetectsPharPlugin($path){
		$loader = new DummyScriptPluginLoader();

		$this->assertTrue($loader->checkExtension($path), $path);
	}

	/**
	 * @dataProvider badExtensionProvider
	 *
	 * @param $path
	 */
	public function testNotDetectWrongExtension($path){
		$loader = new DummyScriptPluginLoader();

		$this->assertFalse($loader->checkExtension($path), $path);
	}

}