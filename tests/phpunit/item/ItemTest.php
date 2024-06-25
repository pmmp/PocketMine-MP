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
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;

class ItemTest extends TestCase{

	/** @var Item */
	private $item;

	public function setUp() : void{
		$this->item = VanillaItems::DIAMOND_SWORD();
	}

	/**
	 * Test for issue #1145 (items aren't considered equal after NBT serializing and deserializing
	 */
	public function testItemEquals() : void{
		$item = VanillaBlocks::STONE()->asItem()->setCustomName("HI");
		$item2 = Item::nbtDeserialize($item->nbtSerialize());
		self::assertTrue($item2->equals($item));
		self::assertTrue($item->equals($item2));
	}

	/**
	 * Test that same items without NBT are considered equal
	 */
	public function testItemEqualsNoNbt() : void{
		$item1 = VanillaItems::DIAMOND_SWORD();
		$item2 = clone $item1;
		self::assertTrue($item1->equals($item2));
	}

	/**
	 * Tests whether items retain their display properties
	 * after being deserialized
	 */
	public function testItemPersistsDisplayProperties() : void{
		$lore = ["Line A", "Line B"];
		$name = "HI";
		$item = VanillaItems::DIAMOND_SWORD();
		$item->setCustomName($name);
		$item->setLore($lore);
		$item = Item::nbtDeserialize($item->nbtSerialize());
		self::assertTrue($item->getCustomName() === $name);
		self::assertTrue($item->getLore() === $lore);
	}

	public function testHasEnchantment() : void{
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 5));
		self::assertTrue($this->item->hasEnchantment(VanillaEnchantments::EFFICIENCY()));
		self::assertTrue($this->item->hasEnchantment(VanillaEnchantments::EFFICIENCY(), 5));
	}

	public function testHasEnchantments() : void{
		self::assertFalse($this->item->hasEnchantments());
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT()));
		self::assertTrue($this->item->hasEnchantments());
	}

	public function testGetEnchantmentLevel() : void{
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 5));
		self::assertSame(5, $this->item->getEnchantmentLevel(VanillaEnchantments::EFFICIENCY()));
	}

	public function testGetEnchantments() : void{
		/** @var EnchantmentInstance[] $enchantments */
		$enchantments = [
			new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 5),
			new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)
		];
		foreach($enchantments as $enchantment){
			$this->item->addEnchantment($enchantment);
		}
		foreach($this->item->getEnchantments() as $enchantment){
			foreach($enchantments as $k => $applied){
				if($enchantment->getType() === $applied->getType() && $enchantment->getLevel() === $applied->getLevel()){
					unset($enchantments[$k]);
					continue 2;
				}
			}
			self::fail("Unknown extra enchantment found");
		}
		self::assertEmpty($enchantments, "Expected all enchantments to be present");
	}

	public function testOverwriteEnchantment() : void{
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS()));
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5));
		self::assertSame(5, $this->item->getEnchantmentLevel(VanillaEnchantments::SHARPNESS()));
	}

	public function testRemoveAllEnchantments() : void{
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT()));
		self::assertCount(1, $this->item->getEnchantments());
		$this->item->removeEnchantments();
		self::assertEmpty($this->item->getEnchantments());
	}

	public function testRemoveEnchantment() : void{
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK()));
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS()));
		self::assertCount(2, $this->item->getEnchantments());
		$this->item->removeEnchantment(VanillaEnchantments::SHARPNESS());
		self::assertFalse($this->item->hasEnchantment(VanillaEnchantments::SHARPNESS()));
	}

	public function testRemoveEnchantmentLevel() : void{
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT(), 2));
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()));
		self::assertCount(2, $this->item->getEnchantments());
		$this->item->removeEnchantment(VanillaEnchantments::FIRE_ASPECT(), 2);
		self::assertFalse($this->item->hasEnchantment(VanillaEnchantments::FIRE_ASPECT()));
	}

	/**
	 * Tests that when all enchantments are removed from an item, the "ench" tag is removed as well
	 */
	public function testRemoveAllEnchantmentsNBT() : void{
		$this->item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1));
		$this->item->removeEnchantment(VanillaEnchantments::SHARPNESS());
		self::assertNull($this->item->getNamedTag()->getTag(Item::TAG_ENCH));
	}
}
