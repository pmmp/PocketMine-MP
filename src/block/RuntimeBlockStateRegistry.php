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
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\light\LightUpdate;
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

	public const COLLISION_CUSTOM = 0;
	public const COLLISION_CUBE = 1;
	public const COLLISION_NONE = 2;
	public const COLLISION_MAY_OVERFLOW = 3;

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

	/**
	 * Map of state ID -> useful AABB info to avoid unnecessary block allocations
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	public array $collisionInfo = [];

	public function __construct(){
		foreach(VanillaBlocks::getAll() as $block){
			$this->register($block);
		}
	}

	/**
	 * Maps a block type's state permutations to its corresponding state IDs. This is necessary for the block to be
	 * recognized when fetching it by its state ID from chunks at runtime.
	 *
	 * @throws \InvalidArgumentException if the desired block type ID is already registered
	 */
	public function register(Block $block) : void{
		$typeId = $block->getTypeId();

		if(isset($this->typeIndex[$typeId])){
			throw new \InvalidArgumentException("Block ID $typeId is already used by another block");
		}

		$this->typeIndex[$typeId] = clone $block;

		foreach($block->generateStatePermutations() as $v){
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

			$declarer = (new \ReflectionFunction($block->getModelPositionOffset(...)))->getClosureScopeClass();
			if($declarer === null){
				throw new AssumptionFailedError("We know this is a class method");
			}
			if($declarer->getName() !== Block::class){
				$this->collisionInfo[$index] = self::COLLISION_MAY_OVERFLOW;
			}else{
				$boxes = $block->getCollisionBoxes();
				if(count($boxes) === 0){
					$this->collisionInfo[$index] = self::COLLISION_NONE;
				}elseif(
					count($boxes) === 1 &&
					$boxes[0]->minX === 0.0 &&
					$boxes[0]->minY === 0.0 &&
					$boxes[0]->minZ === 0.0 &&
					$boxes[0]->maxX === 1.0 &&
					$boxes[0]->maxY === 1.0 &&
					$boxes[0]->maxZ === 1.0
				){
					$this->collisionInfo[$index] = self::COLLISION_CUBE;
				}else{
					$info = self::COLLISION_CUSTOM;

					//TODO: this could blow up if any recalculateCollisionBoxes() uses the world
					//it shouldn't, but that doesn't mean that custom blocks won't...
					foreach($block->getCollisionBoxes() as $box){
						if(
							$box->minX < 0 || $box->maxX > 1 ||
							$box->minY < 0 || $box->maxY > 1 ||
							$box->minZ < 0 || $box->maxZ > 1
						){
							$info = self::COLLISION_MAY_OVERFLOW;
							break;
						}
					}

					$this->collisionInfo[$index] = $info;
				}
			}
		}
	}

	public function fromStateId(int $stateId) : Block{
		if($stateId < 0){
			throw new \InvalidArgumentException("Block state ID cannot be negative");
		}
		if(isset($this->fullList[$stateId])) { //hot
			$block = clone $this->fullList[$stateId];
		}else{
			$typeId = $stateId >> Block::INTERNAL_STATE_DATA_BITS;
			$stateData = ($stateId ^ $typeId) & Block::INTERNAL_STATE_DATA_MASK;
			$block = new UnknownBlock(new BID($typeId), new BlockTypeInfo(BreakInfo::instant()), $stateData);
		}

		return $block;
	}

	/**
	 * @return Block[]
	 * @phpstan-return array<int, Block>
	 */
	public function getAllKnownStates() : array{
		return $this->fullList;
	}
}
