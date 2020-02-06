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

namespace pocketmine\network\mcpe;

use PHPUnit\Framework\TestCase;

class StupidJsonDecodeTest extends TestCase{
	/** @var \Closure */
	private $stupidJsonDecodeFunc;

	public function setUp() : void{
		$this->stupidJsonDecodeFunc = (new \ReflectionMethod(PlayerNetworkSessionAdapter::class, 'stupid_json_decode'))->getClosure();
	}

	/**
	 * @return mixed[][]
	 * @phpstan-return list<array{string,mixed}>
	 */
	public function stupidJsonDecodeProvider() : array{
		return [
			["[\n   \"a\",\"b,c,d,e\\\"   \",,0,1,2, false, 0.001]", ['a', 'b,c,d,e"   ', '', 0, 1, 2, false, 0.001]],
			["0", 0],
			["false", false],
			["NULL", null],
			['["\",,\"word","a\",,\"word2",]', ['",,"word', 'a",,"word2', '']],
			['["\",,\"word","a\",,\"word2",""]', ['",,"word', 'a",,"word2', '']],
			['["Hello,, PocketMine"]', ['Hello,, PocketMine']],
			['[,]', ['', '']],
			['[]', []]
		];
	}

	/**
	 * @dataProvider stupidJsonDecodeProvider
	 *
	 * @param string $brokenJson
	 * @param mixed  $expect
	 *
	 * @throws \ReflectionException
	 */
	public function testStupidJsonDecode(string $brokenJson, $expect) : void{
		$decoded = ($this->stupidJsonDecodeFunc)($brokenJson, true);
		self::assertEquals($expect, $decoded);
	}
}
