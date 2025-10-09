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

namespace pocketmine\item;

use PHPUnit\Framework\TestCase;

class StringToItemParserTest extends TestCase{

	public function testOverrideRemovesOldAlias() : void{
		$parser = new StringToItemParser();

		$item1 = VanillaItems::DIAMOND();
		$item2 = VanillaItems::EMERALD();

		$parser->register("test_alias", fn() => $item1);

		self::assertContains("test_alias", $parser->lookupAliases($item1));
		self::assertNotContains("test_alias", $parser->lookupAliases($item2));

		$parser->override("test_alias", fn() => $item2);

		self::assertNotContains("test_alias", $parser->lookupAliases($item1));
		self::assertContains("test_alias", $parser->lookupAliases($item2));
	}

	public function testOverrideWithNewAlias() : void{
		$parser = new StringToItemParser();

		$item = VanillaItems::DIAMOND();

		$parser->override("new_alias", fn() => $item);

		self::assertContains("new_alias", $parser->lookupAliases($item));
		self::assertSame($item, $parser->parse("new_alias"));
	}

	public function testOverrideMultipleAliases() : void{
		$parser = new StringToItemParser();

		$item1 = VanillaItems::DIAMOND();
		$item2 = VanillaItems::EMERALD();

		$parser->register("alias1", fn() => $item1);
		$parser->register("alias2", fn() => $item1);
		$parser->register("alias3", fn() => $item1);

		self::assertCount(3, $parser->lookupAliases($item1));

		$parser->override("alias2", fn() => $item2);

		self::assertCount(2, $parser->lookupAliases($item1));
		self::assertContains("alias1", $parser->lookupAliases($item1));
		self::assertContains("alias3", $parser->lookupAliases($item1));
		self::assertNotContains("alias2", $parser->lookupAliases($item1));

		self::assertCount(1, $parser->lookupAliases($item2));
		self::assertContains("alias2", $parser->lookupAliases($item2));
	}
}
