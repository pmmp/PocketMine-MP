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

	/** @var BlockFactory */
	private $blockFactory;

	public function setUp() : void{
		$this->blockFactory = new BlockFactory();
	}

	/**
	 * Test registering a block which would overwrite another block, without forcing it
	 */
	public function testAccidentalOverrideBlock() : void{
		$block = new MyCustomBlock(new BlockIdentifier(BlockLegacyIds::COBBLESTONE, 0), "Cobblestone", BlockBreakInfo::instant());
		$this->expectException(\InvalidArgumentException::class);
		$this->blockFactory->register($block);
	}

	/**
	 * Test registering a block deliberately overwriting another block works as expected
	 */
	public function testDeliberateOverrideBlock() : void{
		$block = new MyCustomBlock(new BlockIdentifier(BlockLegacyIds::COBBLESTONE, 0), "Cobblestone", BlockBreakInfo::instant());
		$this->blockFactory->register($block, true);
		self::assertInstanceOf(MyCustomBlock::class, $this->blockFactory->get($block->getId(), 0));
	}

	/**
	 * Test registering a new block which does not yet exist
	 */
	public function testRegisterNewBlock() : void{
		for($i = 0; $i < 256; ++$i){
			if(!$this->blockFactory->isRegistered($i)){
				$b = new StrangeNewBlock(new BlockIdentifier($i, 0), "Strange New Block", BlockBreakInfo::instant());
				$this->blockFactory->register($b);
				self::assertInstanceOf(StrangeNewBlock::class, $this->blockFactory->get($b->getId(), 0));
				return;
			}
		}

		throw new \RuntimeException("Can't test registering new blocks because no unused spaces left");
	}

	/**
	 * Verifies that blocks with IDs larger than 255 can't be registered
	 */
	public function testRegisterIdTooLarge() : void{
		self::expectException(\RuntimeException::class);
		$this->blockFactory->register(new OutOfBoundsBlock(new BlockIdentifier(25555, 0), "Out Of Bounds Block", BlockBreakInfo::instant()));
	}

	/**
	 * Verifies that blocks with IDs smaller than 0 can't be registered
	 */
	public function testRegisterIdTooSmall() : void{
		self::expectException(\RuntimeException::class);
		$this->blockFactory->register(new OutOfBoundsBlock(new BlockIdentifier(-1, 0), "Out Of Bounds Block", BlockBreakInfo::instant()));
	}

	/**
	 * Test that the block factory doesn't return the same object twice - it has to clone it first
	 * This is necessary because the block factory currently holds lots of partially-initialized copies of block
	 * instances which would hold position data and other things, so it's necessary to clone them to avoid astonishing behaviour.
	 */
	public function testBlockFactoryClone() : void{
		for($i = 0; $i < 256; ++$i){
			$b1 = $this->blockFactory->get($i, 0);
			$b2 = $this->blockFactory->get($i, 0);
			self::assertNotSame($b1, $b2);
		}
	}

	/**
	 * @return int[][]
	 * @phpstan-return list<array{int,int}>
	 */
	public function blockGetProvider() : array{
		return [
			[BlockLegacyIds::STONE, 5],
			[BlockLegacyIds::STONE, 15],
			[BlockLegacyIds::GOLD_BLOCK, 0],
			[BlockLegacyIds::WOODEN_PLANKS, 5],
			[BlockLegacyIds::SAND, 0],
			[BlockLegacyIds::GOLD_BLOCK, 0]
		];
	}

	/**
	 * @dataProvider blockGetProvider
	 */
	public function testBlockGet(int $id, int $meta) : void{
		$block = $this->blockFactory->get($id, $meta);

		self::assertEquals($id, $block->getId());
		self::assertEquals($meta, $block->getMeta());
	}

	public function testBlockIds() : void{
		for($i = 0; $i < 256; ++$i){
			$b = $this->blockFactory->get($i, 0);
			self::assertContains($i, $b->getIdInfo()->getAllBlockIds());
		}
	}

	/**
	 * Test that light filters in the static arrays have valid values. Wrong values can cause lots of unpleasant bugs
	 * (like freezes) when doing light population.
	 */
	public function testLightFiltersValid() : void{
		foreach($this->blockFactory->lightFilter as $id => $value){
			self::assertNotNull($value, "Light filter value missing for $id");
			self::assertLessThanOrEqual(15, $value, "Light filter value for $id is larger than the expected 15");
			self::assertGreaterThan(0, $value, "Light filter value for $id must be larger than 0");
		}
	}

	public function testConsistency() : void{
		$list = json_decode(file_get_contents(__DIR__ . '/block_factory_consistency_check.json'), true);
		$states = $this->blockFactory->getAllKnownStates();
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
