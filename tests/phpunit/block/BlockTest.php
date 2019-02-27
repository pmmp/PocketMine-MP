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

namespace pocketmine\block;

use PHPUnit\Framework\TestCase;
use function file_get_contents;
use function json_decode;

class BlockTest extends TestCase{

	public function setUp() : void{
		BlockFactory::init();
	}

	/**
	 * Test registering a block which would overwrite another block, without forcing it
	 */
	public function testAccidentalOverrideBlock() : void{
		$block = new MyCustomBlock(new BlockIdentifier(Block::COBBLESTONE), "Cobblestone");
		$this->expectException(\InvalidArgumentException::class);
		BlockFactory::register($block);
	}

	/**
	 * Test registering a block deliberately overwriting another block works as expected
	 */
	public function testDeliberateOverrideBlock() : void{
		$block = new MyCustomBlock(new BlockIdentifier(Block::COBBLESTONE), "Cobblestone");
		BlockFactory::register($block, true);
		self::assertInstanceOf(MyCustomBlock::class, BlockFactory::get($block->getId()));
	}

	/**
	 * Test registering a new block which does not yet exist
	 */
	public function testRegisterNewBlock() : void{
		for($i = 0; $i < 256; ++$i){
			if(!BlockFactory::isRegistered($i)){
				$b = new StrangeNewBlock(new BlockIdentifier($i), "Strange New Block");
				BlockFactory::register($b);
				self::assertInstanceOf(StrangeNewBlock::class, BlockFactory::get($b->getId()));
				return;
			}
		}

		self::assertTrue(false, "Can't test registering new blocks because no unused spaces left");
	}

	/**
	 * Verifies that blocks with IDs larger than 255 can't be registered
	 */
	public function testRegisterIdTooLarge() : void{
		self::expectException(\RuntimeException::class);
		BlockFactory::register(new OutOfBoundsBlock(new BlockIdentifier(25555), "Out Of Bounds Block"));
	}

	/**
	 * Verifies that blocks with IDs smaller than 0 can't be registered
	 */
	public function testRegisterIdTooSmall() : void{
		self::expectException(\RuntimeException::class);
		BlockFactory::register(new OutOfBoundsBlock(new BlockIdentifier(-1), "Out Of Bounds Block"));
	}

	/**
	 * Test that the block factory doesn't return the same object twice - it has to clone it first
	 * This is necessary because the block factory currently holds lots of partially-initialized copies of block
	 * instances which would hold position data and other things, so it's necessary to clone them to avoid astonishing behaviour.
	 */
	public function testBlockFactoryClone() : void{
		for($i = 0; $i < 256; ++$i){
			$b1 = BlockFactory::get($i);
			$b2 = BlockFactory::get($i);
			self::assertNotSame($b1, $b2);
		}
	}

	/**
	 * @return array
	 */
	public function blockGetProvider() : array{
		return [
			[Block::STONE, 5],
			[Block::STONE, 15],
			[Block::GOLD_BLOCK, 0],
			[Block::WOODEN_PLANKS, 5],
			[Block::SAND, 0],
			[Block::GOLD_BLOCK, 0]
		];
	}

	/**
	 * @dataProvider blockGetProvider
	 * @param int $id
	 * @param int $meta
	 */
	public function testBlockGet(int $id, int $meta) : void{
		$block = BlockFactory::get($id, $meta);

		self::assertEquals($id, $block->getId());
		self::assertEquals($meta, $block->getMeta());
	}

	public function testBlockIds() : void{
		for($i = 0; $i < 256; ++$i){
			$b = BlockFactory::get($i);
			self::assertContains($i, $b->getIdInfo()->getAllBlockIds());
		}
	}

	/**
	 * Test that light filters in the static arrays have valid values. Wrong values can cause lots of unpleasant bugs
	 * (like freezes) when doing light population.
	 */
	public function testLightFiltersValid() : void{
		foreach(BlockFactory::$lightFilter as $id => $value){
			self::assertNotNull($value, "Light filter value missing for $id");
			self::assertLessThanOrEqual(15, $value, "Light filter value for $id is larger than the expected 15");
			self::assertGreaterThan(0, $value, "Light filter value for $id must be larger than 0");
		}
	}

	public function testConsistency() : void{
		$list = json_decode(file_get_contents(__DIR__ . '/block_factory_consistency_check.json'), true);
		$states = BlockFactory::getAllKnownStates();
		foreach($states as $k => $state){
			self::assertArrayHasKey($k, $list, "New block state $k (" . $state->getName() . ") - consistency check may need regenerating");
			self::assertSame($list[$k], $state->getName());
		}
		foreach($list as $k => $name){
			self::assertArrayHasKey($k, $states, "Missing previously-known block state $k ($name)");
			self::assertSame($name, $states[$k]->getName());
		}
	}
}
