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
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use function get_debug_type;
use function implode;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;
use function log;
use const JSON_THROW_ON_ERROR;

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

	/**
	 * @return int[][]|string[][]
	 * @phpstan-return array{array<string, int>, array<string, string>}
	 */
	public static function computeConsistencyCheckTable(RuntimeBlockStateRegistry $blockStateRegistry) : array{
		$newTable = [];
		$newTileMap = [];

		$idNameLookup = [];
		//if we ever split up block registration into multiple registries (e.g. separating chemistry blocks),
		//we'll need to ensure those additional registries are also included here
		foreach(Utils::stringifyKeys(VanillaBlocks::getAll()) as $name => $blockType){
			$id = $blockType->getTypeId();
			if(isset($idNameLookup[$id])){
				throw new AssumptionFailedError("TypeID $name collides with " . $idNameLookup[$id]);
			}
			$idNameLookup[$id] = $name;
		}

		foreach($blockStateRegistry->getAllKnownStates() as $index => $block){
			if($index !== $block->getStateId()){
				throw new AssumptionFailedError("State index should always match state ID");
			}
			$idName = $idNameLookup[$block->getTypeId()];
			$newTable[$idName] = ($newTable[$idName] ?? 0) + 1;

			$tileClass = $block->getIdInfo()->getTileClass();
			if($tileClass !== null){
				if(isset($newTileMap[$idName]) && $newTileMap[$idName] !== $tileClass){
					throw new AssumptionFailedError("Tile entity $tileClass for $idName is inconsistent");
				}
				$newTileMap[$idName] = $tileClass;
			}
		}
		return [$newTable, $newTileMap];
	}

	/**
	 * @phpstan-param array<string, int>    $actualStateCounts
	 * @phpstan-param array<string, string> $actualTiles
	 *
	 * @return string[]
	 */
	public static function computeConsistencyCheckDiff(string $expectedFile, array $actualStateCounts, array $actualTiles) : array{
		$expected = json_decode(Filesystem::fileGetContents($expectedFile), true, 3, JSON_THROW_ON_ERROR);
		if(!is_array($expected)){
			throw new AssumptionFailedError("Old table should be array{stateCounts: array<string, int>, tiles: array<string, string>}");
		}
		$expectedStates = $expected["stateCounts"] ?? [];
		$expectedTiles = $expected["tiles"] ?? [];
		if(!is_array($expectedStates)){
			throw new AssumptionFailedError("stateCounts should be an array, but have " . get_debug_type($expectedStates));
		}
		if(!is_array($expectedTiles)){
			throw new AssumptionFailedError("tiles should be an array, but have " . get_debug_type($expectedTiles));
		}

		$errors = [];
		foreach(Utils::promoteKeys($expectedStates) as $typeName => $numStates){
			if(!is_string($typeName) || !is_int($numStates)){
				throw new AssumptionFailedError("Old table should be array<string, int>");
			}
			if(!isset($actualStateCounts[$typeName])){
				$errors[] = "Removed block type $typeName ($numStates permutations)";
			}elseif($actualStateCounts[$typeName] !== $numStates){
				$errors[] = "Block type $typeName permutation count changed: $numStates -> " . $actualStateCounts[$typeName];
			}
		}
		foreach(Utils::stringifyKeys($actualStateCounts) as $typeName => $numStates){
			if(!isset($expectedStates[$typeName])){
				$errors[] = "Added block type $typeName (" . $actualStateCounts[$typeName] . " permutations)";
			}
		}

		foreach(Utils::promoteKeys($expectedTiles) as $typeName => $tile){
			if(!is_string($typeName) || !is_string($tile)){
				throw new AssumptionFailedError("Tile table should be array<string, string>");
			}
			if(isset($actualStateCounts[$typeName])){
				if(!isset($actualTiles[$typeName])){
					$errors[] = "$typeName no longer has a tile";
				}elseif($actualTiles[$typeName] !== $tile){
					$errors[] = "$typeName has changed tile ($tile -> " . $actualTiles[$typeName] . ")";
				}
			}
		}
		foreach(Utils::promoteKeys($actualTiles) as $typeName => $tile){
			if(isset($expectedStates[$typeName]) && !isset($expectedTiles[$typeName])){
				$errors[] = "$typeName has a tile when it previously didn't ($tile)";
			}
		}

		return $errors;
	}

	public function testConsistency() : void{
		[$newTable, $newTileMap] = self::computeConsistencyCheckTable($this->blockFactory);
		$errors = self::computeConsistencyCheckDiff(__DIR__ . '/block_factory_consistency_check.json', $newTable, $newTileMap);

		self::assertEmpty($errors, "Block factory consistency check failed:\n" . implode("\n", $errors));
	}

	public function testEmptyStateId() : void{
		$block = $this->blockFactory->fromStateId(Block::EMPTY_STATE_ID);
		self::assertInstanceOf(Air::class, $block);
	}

	public function testStateDataSizeNotTooLarge() : void{
		$typeIdBitsMin = ((int) log(BlockTypeIds::FIRST_UNUSED_BLOCK_ID, 2)) + 1;

		$typeIdBitsMin++; //for custom blocks

		self::assertLessThanOrEqual(32, Block::INTERNAL_STATE_DATA_BITS + $typeIdBitsMin, "State data size cannot be larger than " . (32 - $typeIdBitsMin) . " bits (need at least $typeIdBitsMin bits for block type ID)");
	}

	public function testAsItemFromItem() : void{
		$block = VanillaBlocks::FLOWER_POT();
		$item = $block->asItem();
		$defaultBlock = $item->getBlock();
		$item2 = $defaultBlock->asItem();
		self::assertTrue($item2->equalsExact($item));
	}
}
