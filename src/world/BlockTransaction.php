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

namespace pocketmine\world;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\utils\Utils;

class BlockTransaction{
	/** @var Block[][][] */
	private array $blocks = [];

	/**
	 * @var \Closure[]
	 * @phpstan-var (\Closure(ChunkManager $world, int $x, int $y, int $z) : bool)[]
	 */
	private array $validators = [];

	public function __construct(private ChunkManager $world){
		$this->addValidator(static function(ChunkManager $world, int $x, int $y, int $z) : bool{
			return $world->isInWorld($x, $y, $z);
		});
	}

	/**
	 * Adds a block to the transaction at the given position.
	 *
	 * @return $this
	 */
	public function addBlock(Vector3 $pos, Block $state) : self{
		return $this->addBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $state);
	}

	/**
	 * Adds a block to the batch at the given coordinates.
	 *
	 * @return $this
	 */
	public function addBlockAt(int $x, int $y, int $z, Block $state) : self{
		$this->blocks[$x][$y][$z] = $state;
		return $this;
	}

	/**
	 * Reads a block from the given world, masked by the blocks in this transaction. This can be useful if you want to
	 * add blocks to the transaction that depend on previous blocks should they exist.
	 */
	public function fetchBlock(Vector3 $pos) : Block{
		return $this->fetchBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
	}

	/**
	 * @see BlockTransaction::fetchBlock()
	 */
	public function fetchBlockAt(int $x, int $y, int $z) : Block{
		return $this->blocks[$x][$y][$z] ?? $this->world->getBlockAt($x, $y, $z);
	}

	/**
	 * Validates and attempts to apply the transaction to the given world. If any part of the transaction fails to
	 * validate, no changes will be made to the world.
	 *
	 * @return bool if the application was successful
	 */
	public function apply() : bool{
		foreach($this->getBlocks() as [$x, $y, $z, $_]){
			foreach($this->validators as $validator){
				if(!$validator($this->world, $x, $y, $z)){
					return false;
				}
			}
		}
		$changedBlocks = 0;
		foreach($this->getBlocks() as [$x, $y, $z, $block]){
			$oldBlock = $this->world->getBlockAt($x, $y, $z);
			if(!$oldBlock->isSameState($block)){
				$this->world->setBlockAt($x, $y, $z, $block);
				$changedBlocks++;
			}
		}
		return $changedBlocks !== 0;
	}

	/**
	 * @return \Generator|mixed[] [int $x, int $y, int $z, Block $block]
	 * @phpstan-return \Generator<int, array{int, int, int, Block}, void, void>
	 */
	public function getBlocks() : \Generator{
		foreach($this->blocks as $x => $yLine){
			foreach($yLine as $y => $zLine){
				foreach($zLine as $z => $block){
					yield [$x, $y, $z, $block];
				}
			}
		}
	}

	/**
	 * Add a validation predicate which will be used to validate every block.
	 * The callable signature should be the same as the below dummy function.
	 * @see BlockTransaction::dummyValidator()
	 *
	 * @phpstan-param \Closure(ChunkManager $world, int $x, int $y, int $z) : bool $validator
	 */
	public function addValidator(\Closure $validator) : void{
		Utils::validateCallableSignature([$this, 'dummyValidator'], $validator);
		$this->validators[] = $validator;
	}

	/**
	 * Dummy function demonstrating the required closure signature for validators.
	 * @see BlockTransaction::addValidator()
	 *
	 * @dummy
	 */
	public function dummyValidator(ChunkManager $world, int $x, int $y, int $z) : bool{
		return true;
	}
}
