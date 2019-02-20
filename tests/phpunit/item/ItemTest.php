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
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

class ItemTest extends TestCase{

	public static function setUpBeforeClass() : void{
		BlockFactory::init();
		ItemFactory::init();
		Enchantment::init();
	}

	/** @var Item */
	private $item;

	public function setUp() : void{
		$this->item = ItemFactory::get(Item::DIAMOND_SWORD);
	}

	/**
	 * Test for issue #1145 (items aren't considered equal after NBT serializing and deserializing
	 */
	public function testItemEquals() : void{
		$item = ItemFactory::get(Item::STONE)->setCustomName("HI");
		$item2 = Item::nbtDeserialize($item->nbtSerialize());
		self::assertTrue($item2->equals($item));
		self::assertTrue($item->equals($item2));
	}

	/**
	 * Test that same items without NBT are considered equal
	 */
	public function testItemEqualsNoNbt() : void{
		$item1 = ItemFactory::get(Item::DIAMOND_SWORD);
		$item2 = clone $item1;
		self::assertTrue($item1->equals($item2));
	}

	public function testHasEnchantment() : void{
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::EFFICIENCY(), 5));
		self::assertTrue($this->item->hasEnchantment(Enchantment::EFFICIENCY()));
		self::assertTrue($this->item->hasEnchantment(Enchantment::EFFICIENCY(), 5));
	}

	public function testHasEnchantments() : void{
		self::assertFalse($this->item->hasEnchantments());
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::FIRE_ASPECT()));
		self::assertTrue($this->item->hasEnchantments());
	}

	public function testGetEnchantmentLevel() : void{
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::EFFICIENCY(), 5));
		self::assertSame(5, $this->item->getEnchantmentLevel(Enchantment::EFFICIENCY()));
	}

	public function testGetEnchantments() : void{
		/** @var EnchantmentInstance[] $enchantments */
		$enchantments = [
			new EnchantmentInstance(Enchantment::EFFICIENCY(), 5),
			new EnchantmentInstance(Enchantment::SHARPNESS(), 1)
		];
		foreach($enchantments as $enchantment){
			$this->item->addEnchantment($enchantment);
		}
		foreach($this->item->getEnchantments() as $enchantment){
			foreach($enchantments as $k => $applied){
				if($enchantment->getType() === $applied->getType() and $enchantment->getLevel() === $applied->getLevel()){
					unset($enchantments[$k]);
					continue 2;
				}
			}
			self::assertTrue(false, "Unknown extra enchantment found: " . $enchantment->getType()->getName() . " x" . $enchantment->getLevel());
		}
		self::assertEmpty($enchantments, "Expected all enchantments to be present");
	}

	public function testOverwriteEnchantment() : void{
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::SHARPNESS()));
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::SHARPNESS(), 5));
		self::assertSame(5, $this->item->getEnchantmentLevel(Enchantment::SHARPNESS()));
	}

	public function testRemoveAllEnchantments() : void{
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::FIRE_ASPECT()));
		self::assertCount(1, $this->item->getEnchantments());
		$this->item->removeEnchantments();
		self::assertEmpty($this->item->getEnchantments());
	}

	public function testRemoveEnchantment() : void{
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::KNOCKBACK()));
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::SHARPNESS()));
		self::assertCount(2, $this->item->getEnchantments());
		$this->item->removeEnchantment(Enchantment::SHARPNESS());
		self::assertFalse($this->item->hasEnchantment(Enchantment::SHARPNESS()));
	}

	public function testRemoveEnchantmentLevel() : void{
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::FIRE_ASPECT(), 2));
		$this->item->addEnchantment(new EnchantmentInstance(Enchantment::UNBREAKING()));
		self::assertCount(2, $this->item->getEnchantments());
		$this->item->removeEnchantment(Enchantment::FIRE_ASPECT(), 2);
		self::assertFalse($this->item->hasEnchantment(Enchantment::FIRE_ASPECT()));
	}
}
