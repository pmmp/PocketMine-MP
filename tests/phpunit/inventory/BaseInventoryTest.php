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

namespace pocketmine\inventory;

use PHPUnit\Framework\TestCase;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;

class BaseInventoryTest extends TestCase{

	public function testAddItemDifferentUserData() : void{
		$inv = new SimpleInventory(1);
		$item1 = ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 1);
		$item2 = ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 1)->setCustomName("TEST");

		$inv->addItem(clone $item1);
		self::assertFalse($inv->canAddItem($item2), "Item WITHOUT userdata should not stack with item WITH userdata");
		self::assertNotEmpty($inv->addItem($item2));

		$inv->clearAll();
		self::assertEmpty($inv->getContents());

		$inv->addItem(clone $item2);
		self::assertFalse($inv->canAddItem($item1), "Item WITH userdata should not stack with item WITHOUT userdata");
		self::assertNotEmpty($inv->addItem($item1));
	}

	/**
	 * @return Item[]
	 */
	private function getTestItems() : array{
		return [
			VanillaItems::APPLE()->setCount(16),
			VanillaItems::APPLE()->setCount(16),
			VanillaItems::APPLE()->setCount(16),
			VanillaItems::APPLE()->setCount(16)
		];
	}

	public function testAddMultipleItemsInOneCall() : void{
		$inventory = new SimpleInventory(1);
		$leftover = $inventory->addItem(...$this->getTestItems());
		self::assertCount(0, $leftover);
		self::assertTrue($inventory->getItem(0)->equalsExact(VanillaItems::APPLE()->setCount(64)));
	}

	public function testAddMultipleItemsInOneCallWithLeftover() : void{
		$inventory = new SimpleInventory(1);
		$inventory->setItem(0, VanillaItems::APPLE()->setCount(20));
		$leftover = $inventory->addItem(...$this->getTestItems());
		self::assertCount(2, $leftover); //the leftovers are not currently stacked - if they were given separately, they'll be returned separately
		self::assertTrue($inventory->getItem(0)->equalsExact(VanillaItems::APPLE()->setCount(64)));

		$leftoverCount = 0;
		foreach($leftover as $item){
			self::assertTrue($item->equals(VanillaItems::APPLE()));
			$leftoverCount += $item->getCount();
		}
		self::assertSame(20, $leftoverCount);
	}

	public function testAddItemWithOversizedCount() : void{
		$inventory = new SimpleInventory(10);
		$leftover = $inventory->addItem(VanillaItems::APPLE()->setCount(100));
		self::assertCount(0, $leftover);

		$count = 0;
		foreach($inventory->getContents() as $item){
			self::assertTrue($item->equals(VanillaItems::APPLE()));
			$count += $item->getCount();
		}
		self::assertSame(100, $count);
	}

	public function testGetAddableItemQuantityStacking() : void{
		$inventory = new SimpleInventory(1);
		$inventory->addItem(VanillaItems::APPLE()->setCount(60));
		self::assertSame(2, $inventory->getAddableItemQuantity(VanillaItems::APPLE()->setCount(2)));
		self::assertSame(4, $inventory->getAddableItemQuantity(VanillaItems::APPLE()->setCount(6)));
	}

	public function testGetAddableItemQuantityEmptyStack() : void{
		$inventory = new SimpleInventory(1);
		$item = VanillaItems::APPLE();
		$item->setCount($item->getMaxStackSize());
		self::assertSame($item->getMaxStackSize(), $inventory->getAddableItemQuantity($item));
	}
}
