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
use function asort;
use function file_get_contents;
use function is_array;
use function json_decode;
use function print_r;
use const SORT_STRING;

class BlockTest extends TestCase{

	/** @var RuntimeBlockStateRegistry */
	private $blockFactory;

	public function setUp() : void{
		$this->blockFactory = new RuntimeBlockStateRegistry();
	}

	/**
	 * Test registering a block which would overwrite another block, without forcing it
	 */
	public function testAccidentalOverrideBlock() : void{
		$block = new MyCustomBlock(new BlockIdentifier(BlockTypeIds::COBBLESTONE), "Cobblestone", new BlockTypeInfo(BlockBreakInfo::instant()));
		$this->expectException(\InvalidArgumentException::class);
		$this->blockFactory->register($block);
	}

	/**
	 * Test registering a new block which does not yet exist
	 */
	public function testRegisterNewBlock() : void{
		$b = new StrangeNewBlock(new BlockIdentifier(BlockTypeIds::newId()), "Strange New Block", new BlockTypeInfo(BlockBreakInfo::instant()));
		$this->blockFactory->register($b);
		self::assertInstanceOf(StrangeNewBlock::class, $this->blockFactory->fromStateId($b->getStateId()));
	}

	/**
	 * Verifies that blocks with IDs smaller than 0 can't be registered
	 */
	public function testRegisterIdTooSmall() : void{
		self::expectException(\InvalidArgumentException::class);
		$this->blockFactory->register(new OutOfBoundsBlock(new BlockIdentifier(-1), "Out Of Bounds Block", new BlockTypeInfo(BlockBreakInfo::instant())));
	}

	/**
	 * Test that the block factory doesn't return the same object twice - it has to clone it first
	 * This is necessary because the block factory currently holds lots of partially-initialized copies of block
	 * instances which would hold position data and other things, so it's necessary to clone them to avoid astonishing behaviour.
	 */
	public function testBlockFactoryClone() : void{
		foreach($this->blockFactory->getAllKnownStates() as $k => $state){
			$b1 = $this->blockFactory->fromStateId($k);
			$b2 = $this->blockFactory->fromStateId($k);
			self::assertNotSame($b1, $b2);
		}
	}

	/**
	 * Test that light filters in the static arrays have valid values. Wrong values can cause lots of unpleasant bugs
	 * (like freezes) when doing light population.
	 */
	public function testLightFiltersValid() : void{
		foreach($this->blockFactory->lightFilter as $id => $value){
			self::assertLessThanOrEqual(15, $value, "Light filter value for $id is larger than the expected 15");
			self::assertGreaterThan(0, $value, "Light filter value for $id must be larger than 0");
		}
	}

	public function testConsistency() : void{
		$list = json_decode(file_get_contents(__DIR__ . '/block_factory_consistency_check.json'), true);
		if(!is_array($list)){
			throw new \pocketmine\utils\AssumptionFailedError("Old table should be array{knownStates: array<string, string>, stateDataBits: int}");
		}
		$knownStates = [];
		/**
		 * @var string $name
		 * @var int[]  $stateIds
		 */
		foreach($list["knownStates"] as $name => $stateIds){
			foreach($stateIds as $stateId){
				$knownStates[$stateId] = $name;
			}
		}
		$oldStateDataSize = $list["stateDataBits"];
		self::assertSame($oldStateDataSize, Block::INTERNAL_STATE_DATA_BITS, "Changed number of state data bits - consistency check probably need regenerating");

		$states = $this->blockFactory->getAllKnownStates();
		foreach($states as $stateId => $state){
			self::assertArrayHasKey($stateId, $knownStates, "New block state $stateId (" . print_r($state, true) . ") - consistency check may need regenerating");
			self::assertSame($knownStates[$stateId], $state->getName());
		}
		asort($knownStates, SORT_STRING);
		foreach($knownStates as $k => $name){
			self::assertArrayHasKey($k, $states, "Missing previously-known block state $k " . ($k >> Block::INTERNAL_STATE_DATA_BITS) . ":" . ($k & Block::INTERNAL_STATE_DATA_MASK) . " ($name)");
			self::assertSame($name, $states[$k]->getName());
		}
	}

	public function testEmptyStateId() : void{
		$block = $this->blockFactory->fromStateId(Block::EMPTY_STATE_ID);
		self::assertInstanceOf(Air::class, $block);
	}

	public function testAsItemFromItem() : void{
		$block = VanillaBlocks::FLOWER_POT();
		$item = $block->asItem();
		$defaultBlock = $item->getBlock();
		$item2 = $defaultBlock->asItem();
		self::assertTrue($item2->equalsExact($item));
	}
}
