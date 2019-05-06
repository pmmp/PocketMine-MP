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

namespace pocketmine\nbt;

use PHPUnit\Framework\TestCase;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;

class JsonNbtParserTest extends TestCase{

	public function testIncompleteCompound() : void{
		$this->expectExceptionMessage("unexpected end of stream");
		JsonNbtParser::parseJson("{SomeTag:[]");
	}

	public function testIncompleteList() : void{
		$this->expectExceptionMessage("unexpected end of stream");
		JsonNbtParser::parseJson("{SomeTag:[");
	}

	public function testTrailingBraces() : void{
		$this->expectExceptionMessage("unexpected trailing characters");
		JsonNbtParser::parseJson("{SomeTag:[]}}");
	}

	public function testWrongOuterTagType() : void{
		$this->expectExceptionMessage("expected compound start");
		JsonNbtParser::parseJson("[1,2,3]");
	}

	public function testParseGarbage() : void{
		$this->expectExceptionMessage("expected compound start");
		JsonNbtParser::parseJson("dsfhjfughfuy{string:string} ");
	}

	public function testEmptyCompound() : void{
		$tag = JsonNbtParser::parseJson("{}");
		self::assertNotNull($tag);
		self::assertCount(0, $tag);
	}

	public function testEmptyList() : void{
		$tag = JsonNbtParser::parseJson("{TestList:[]}");
		self::assertNotNull($tag);
		self::assertTrue($tag->hasTag("TestList", ListTag::class));
		self::assertCount(0, $tag->getListTag("TestList"));
	}

	public function testMixedList() : void{
		$this->expectExceptionMessageRegExp("/Invalid tag of type .* assigned to ListTag, expected .*/");
		JsonNbtParser::parseJson("{TestList:[1f, string2, 3b]}");
	}

	public function testQuotedKeys() : void{
		$tag = JsonNbtParser::parseJson("{\"String With Spaces\": 1}");
		self::assertTrue($tag->hasTag("String With Spaces", IntTag::class));
	}

	public function testQuotedValues() : void{
		$tag = JsonNbtParser::parseJson("{TestString:\"  TEST  minecraft:stone  \"}");
		self::assertSame("  TEST  minecraft:stone  ", $tag->getString("TestString"));
	}
}
