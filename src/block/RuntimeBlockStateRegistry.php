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

use pocketmine\block\BlockBreakInfo as BreakInfo;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\data\runtime\InvalidSerializedRuntimeDataException;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\light\LightUpdate;
use function get_class;
use function min;

/**
 * Blocks are stored as state IDs in chunks at runtime (it would waste far too much memory to represent every block as
 * an object). This class maps block state IDs to their corresponding block objects when reading blocks from chunks at
 * runtime.
 *
 * @internal Plugin devs shouldn't need to interact with this class at all, unless registering a new block type.
 */
class RuntimeBlockStateRegistry{
	use SingletonTrait;

	/**
	 * @var Block[]
	 * @phpstan-var array<int, Block>
	 */
	private array $fullList = [];

	/**
	 * Index of default states for every block type
	 * @var Block[]
	 * @phpstan-var array<int, Block>
	 */
	private array $typeIndex = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	public array $light = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	public array $lightFilter = [];
	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	public array $blocksDirectSkyLight = [];
	/**
	 * @var float[]
	 * @phpstan-var array<int, float>
	 */
	public array $blastResistance = [];

	public function __construct(){
		foreach(VanillaBlocks::getAll() as $block){
			$this->register($block);
		}
	}

	/**
	 * Generates all the possible valid blockstates for a given block type.
	 *
	 * @phpstan-return \Generator<int, Block, void, void>
	 */
	private static function generateAllStatesForType(Block $block) : \Generator{
		//TODO: this bruteforce approach to discovering all valid states is very inefficient for larger state data sizes
		//at some point we'll need to find a better way to do this
		$bits = $block->getRequiredTypeDataBits() + $block->getRequiredStateDataBits();
		if($bits > Block::INTERNAL_STATE_DATA_BITS){
			throw new \InvalidArgumentException("Block state data cannot use more than " . Block::INTERNAL_STATE_DATA_BITS . " bits");
		}
		for($stateData = 0; $stateData < (1 << $bits); ++$stateData){
			$v = clone $block;
			try{
				$v->decodeStateData($stateData);
				if($v->computeStateData() !== $stateData){
					//TODO: this should probably be a hard error
					throw new \LogicException(get_class($block) . "::decodeStateData() accepts invalid state data (returned " . $v->computeStateData() . " for input $stateData)");
				}
			}catch(InvalidSerializedRuntimeDataException){ //invalid property combination, leave it
				continue;
			}

			yield $v;
		}
	}

	/**
	 * Maps a block type to its corresponding type ID. This is necessary for the block to be recognized when loading
	 * from disk, and also when being read at runtime.
	 *
	 * NOTE: If you are registering a new block type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param bool $override Whether to override existing registrations
	 *
	 * @throws \InvalidArgumentException if something attempted to override an already-registered block without specifying the
	 * $override parameter.
	 */
	public function register(Block $block, bool $override = false) : void{
		$typeId = $block->getTypeId();

		if(!$override && isset($this->typeIndex[$typeId])){
			throw new \InvalidArgumentException("Block ID $typeId is already used by another block, and override was not requested");
		}

		$this->typeIndex[$typeId] = clone $block;

		foreach(self::generateAllStatesForType($block) as $v){
			$this->fillStaticArrays($v->getStateId(), $v);
		}
	}

	private function fillStaticArrays(int $index, Block $block) : void{
		$fullId = $block->getStateId();
		if($index !== $fullId){
			throw new AssumptionFailedError("Cannot fill static arrays for an invalid blockstate");
		}else{
			$this->fullList[$index] = $block;
			$this->blastResistance[$index] = $block->getBreakInfo()->getBlastResistance();
			$this->light[$index] = $block->getLightLevel();
			$this->lightFilter[$index] = min(15, $block->getLightFilter() + LightUpdate::BASE_LIGHT_FILTER);
			if($block->blocksDirectSkyLight()){
				$this->blocksDirectSkyLight[$index] = true;
			}
		}
	}

	/**
	 * @internal
	 * Returns the default state of the block type associated with the given type ID.
	 */
	public function fromTypeId(int $typeId) : Block{
		if(isset($this->typeIndex[$typeId])){
			return clone $this->typeIndex[$typeId];
		}

		throw new \InvalidArgumentException("Block ID $typeId is not registered");
	}

	public function fromStateId(int $stateId) : Block{
		if($stateId < 0){
			throw new \InvalidArgumentException("Block state ID cannot be negative");
		}
		if(isset($this->fullList[$stateId])) { //hot
			$block = clone $this->fullList[$stateId];
		}else{
			$typeId = $stateId >> Block::INTERNAL_STATE_DATA_BITS;
			$stateData = $stateId & Block::INTERNAL_STATE_DATA_MASK;
			$block = new UnknownBlock(new BID($typeId), new BlockTypeInfo(BreakInfo::instant()), $stateData);
		}

		return $block;
	}

	/**
	 * Returns whether a specified block state is already registered in the block factory.
	 */
	public function isRegistered(int $typeId) : bool{
		$b = $this->typeIndex[$typeId] ?? null;
		return $b !== null && !($b instanceof UnknownBlock);
	}

	/**
	 * @return Block[]
	 * @phpstan-return array<int, Block>
	 */
	public function getAllKnownTypes() : array{
		return $this->typeIndex;
	}

	/**
	 * @return Block[]
	 */
	public function getAllKnownStates() : array{
		return $this->fullList;
	}
}
