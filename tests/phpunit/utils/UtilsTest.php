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
use pocketmine\utils\fixtures\TestAbstractClass;
use pocketmine\utils\fixtures\TestInstantiableClass;
use pocketmine\utils\fixtures\TestInterface;
use pocketmine\utils\fixtures\TestSubclassOfInstantiableClass;
use pocketmine\utils\fixtures\TestTrait;
use function define;
use function defined;

class UtilsTest extends TestCase{

	public function setUp() : void{
		if(!defined('pocketmine\PATH')){
			define('pocketmine\PATH', 'dummy');
		}
	}

	/**
	 * @return string[][]
	 * @phpstan-return list<array{string}>
	 */
	public static function parseDocCommentNewlineProvider() : array{
		return [
			["\t/**\r\n\t * @param PlayerJoinEvent \$event\r\n\t * @priority HIGHEST\r\n\t * @notHandler\r\n\t */"],
			["\t/**\n\t * @param PlayerJoinEvent \$event\n\t * @priority HIGHEST\n\t * @notHandler\n\t */"],
			["\t/**\r\t * @param PlayerJoinEvent \$event\r\t * @priority HIGHEST\r\t * @notHandler\r\t */"]
		];
	}

	/**
	 * @dataProvider parseDocCommentNewlineProvider
	 */
	public function testParseDocCommentNewlines(string $docComment) : void{
		$tags = Utils::parseDocComment($docComment);

		self::assertArrayHasKey("notHandler", $tags);
		self::assertEquals("", $tags["notHandler"]);
		self::assertArrayHasKey("priority", $tags);
		self::assertEquals("HIGHEST", $tags["priority"]);
	}

	/**
	 * @return string[][]
	 * @phpstan-return list<array{string}>
	 */
	public static function parseDocCommentOneLineProvider() : array{
		return [
			["/** @ignoreCancelled true dummy */"],
			["/**@ignoreCancelled true dummy*/"],
			["/** @ignoreCancelled    true dummy */"]
		];
	}

	/**
	 * @dataProvider parseDocCommentOneLineProvider
	 */
	public function testParseOneLineDocComment(string $comment) : void{
		$tags = Utils::parseDocComment($comment);
		self::assertArrayHasKey("ignoreCancelled", $tags);
		self::assertEquals("true dummy", $tags["ignoreCancelled"]);
	}

	public function testParseEmptyDocComment() : void{
		$tags = Utils::parseDocComment("");
		self::assertCount(0, $tags);
	}

	public function testParseDocCommentWithTagsContainingHyphens() : void{
		$tags = Utils::parseDocComment("/** @phpstan-return list<string> */");
		self::assertArrayHasKey("phpstan-return", $tags);
		self::assertEquals("list<string>", $tags["phpstan-return"]);
	}

	public function testNamespacedNiceClosureName() : void{
		//be careful with this test. The closure has to be declared on the same line as the assertion.
		self::assertSame('closure@' . Filesystem::cleanPath(__FILE__) . '#L' . __LINE__, Utils::getNiceClosureName(function() : void{}));
	}

	/**
	 * @return string[][]
	 * @return list<array{class-string, class-string}>
	 */
	public static function validInstanceProvider() : array{
		return [
			//direct instance / implement / extend
			[TestInstantiableClass::class, TestInstantiableClass::class],
			[TestInstantiableClass::class, TestAbstractClass::class],
			[TestInstantiableClass::class, TestInterface::class],

			//inherited
			[TestSubclassOfInstantiableClass::class, TestInstantiableClass::class],
			[TestSubclassOfInstantiableClass::class, TestAbstractClass::class],
			[TestSubclassOfInstantiableClass::class, TestInterface::class]
		];
	}

	/**
	 * @dataProvider validInstanceProvider
	 * @doesNotPerformAssertions
	 * @phpstan-param class-string $className
	 * @phpstan-param class-string $baseName
	 */
	public function testValidInstanceWithValidCombinations(string $className, string $baseName) : void{
		Utils::testValidInstance($className, $baseName);
	}

	/**
	 * @return string[][]
	 * @return list<array{string, string}>
	 */
	public static function validInstanceInvalidCombinationsProvider() : array{
		return [
			["iDontExist abc", TestInstantiableClass::class],
			[TestInstantiableClass::class, "iDon'tExist abc"],
			["iDontExist", "iAlsoDontExist"],
			[TestInstantiableClass::class, TestTrait::class],
			[TestTrait::class, TestTrait::class],
			[TestAbstractClass::class, TestAbstractClass::class],
			[TestInterface::class, TestInterface::class],
			[TestInstantiableClass::class, TestSubclassOfInstantiableClass::class]
		];
	}

	/**
	 * @dataProvider validInstanceInvalidCombinationsProvider
	 */
	public function testValidInstanceInvalidParameters(string $className, string $baseName) : void{
		$this->expectException(\InvalidArgumentException::class);
		Utils::testValidInstance($className, $baseName); //@phpstan-ignore-line
	}
}
