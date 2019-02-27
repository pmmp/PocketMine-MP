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
use pocketmine\block\BlockFactory;

class ItemFactoryTest extends TestCase{

	public function setUp() : void{
		BlockFactory::init();
		ItemFactory::init();
	}

	/**
	 * Tests that blocks are considered to be valid registered items
	 */
	public function testItemBlockRegistered() : void{
		for($id = 0; $id < 256; ++$id){
			self::assertEquals(BlockFactory::isRegistered($id), ItemFactory::isRegistered($id));
		}
	}

	public function itemFromStringProvider() : array{
		return [
			["dye:4", ItemIds::DYE, 4],
			["351", ItemIds::DYE, 0],
			["351:4", ItemIds::DYE, 4],
			["stone:3", ItemIds::STONE, 3],
			["minecraft:string", ItemIds::STRING, 0],
			["diamond_pickaxe", ItemIds::DIAMOND_PICKAXE, 0],
			["diamond_pickaxe:5", ItemIds::DIAMOND_PICKAXE, 5]
		];
	}

	/**
	 * @dataProvider itemFromStringProvider
	 * @param string $string
	 * @param int    $id
	 * @param int    $meta
	 */
	public function testFromStringSingle(string $string, int $id, int $meta) : void{
		$item = ItemFactory::fromString($string);

		self::assertEquals($id, $item->getId());
		self::assertEquals($meta, $item->getMeta());
	}

	/**
	 * Test that durable items are correctly created by the item factory
	 */
	public function testGetDurableItem() : void{
		self::assertInstanceOf(Sword::class, $i1 = ItemFactory::get(Item::WOODEN_SWORD));
		/** @var Sword $i1 */
		self::assertSame(0, $i1->getDamage());
		self::assertInstanceOf(Sword::class, $i2 = ItemFactory::get(Item::WOODEN_SWORD, 1));
		/** @var Sword $i2 */
		self::assertSame(1, $i2->getDamage());
	}
}
