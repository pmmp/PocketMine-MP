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

	/**
	 * Tests that blocks are considered to be valid registered items
	 */
	public function testItemBlockRegistered() : void{
		for($id = 0; $id < 256; ++$id){
			self::assertEquals(BlockFactory::getInstance()->isRegistered($id), ItemFactory::getInstance()->isRegistered($id));
		}
	}

	/**
	 * Test that durable items are correctly created by the item factory
	 */
	public function testGetDurableItem() : void{
		self::assertInstanceOf(Sword::class, $i1 = ItemFactory::getInstance()->get(ItemIds::WOODEN_SWORD));
		/** @var Sword $i1 */
		self::assertSame(0, $i1->getDamage());
		self::assertInstanceOf(Sword::class, $i2 = ItemFactory::getInstance()->get(ItemIds::WOODEN_SWORD, 1));
		/** @var Sword $i2 */
		self::assertSame(1, $i2->getDamage());
	}

	public function testGetDurableItemWithTooLargeDurability() : void{
		self::assertInstanceOf(Sword::class, ItemFactory::getInstance()->get(ItemIds::WOODEN_SWORD, ToolTier::WOOD()->getMaxDurability()));
		ItemFactory::getInstance()->get(ItemIds::WOODEN_SWORD, ToolTier::WOOD()->getMaxDurability() + 1);
	}
}
