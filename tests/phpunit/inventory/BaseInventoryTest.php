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
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class BaseInventoryTest extends TestCase{

	public function testAddItemDifferentUserData() : void{
		$inv = new class(1) extends SimpleInventory{

		};
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
}
