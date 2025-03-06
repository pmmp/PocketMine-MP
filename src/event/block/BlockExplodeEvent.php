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

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\world\Position;

/**
 * Event triggered when a block explodes (e.g., a bed in the Nether).
 * This event is used to handle the explosion of blocks and customize their behavior.
 *
 * @see BlockPreExplodeEvent
 *
 * @phpstan-extends BlockEvent<Block>
 */
class BlockExplodeEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	/**
	 * @param Block $block The block that exploded
	 * @param Position $position The position of the explosion
	 * @param Block[] $blocks The list of blocks affected by the explosion
	 * @param float $yield The explosion yield (intensity 0-100)
	 */
	public function __construct(
		Block $block,
		protected Position $position,
		protected array $blocks,
		protected float $yield,
		private array $ignitions,
		protected float $fireChance
	){
		parent::__construct($block);

		if($yield < 0.0 || $yield > 100.0){
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
	}

	public function getPosition() : Position{
		return $this->position;
	}

	/**
	 * Get the explosion yield (intensity).
	 *
	 * @return float The intensity of the explosion
	 */
	public function getYield() : float{
		return $this->yield;
	}

	/**
	 * Set the explosion yield (intensity).
	 *
	 * @param float $yield The intensity of the explosion (0-100)
	 */
	public function setYield(float $yield) : void{
		if($yield < 0.0 || $yield > 100.0){
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
		$this->yield = $yield;
	}

	/**
	 * Get the set of affected blocks for fire ignitions
	 *
	 * @return array A set of blocks that are affected by fire ignitions
	 */
	public function getAffectedBlocks() : array{
		return $this->blocks;
	}

	/**
	 * Set the set of blocks affected by fire ignitions
	 *
	 * @param array $blocks The set of blocks to be affected by fire ignitions
	 */
	public function setAffectedBlocks(array $blocks) : void{
		$this->blocks = $blocks;
	}

	/**
	 * Get the set of blocks that can be ignited by the explosion
	 *
	 * @return array A set of blocks that are ignited by the explosion
	 */
	public function getIgnitions() : array{
		return $this->ignitions;
	}

	/**
	 * Set the set of blocks that can be ignited by the explosion
	 *
	 * @param array $ignitions The set of blocks to set as ignited
	 */
	public function setIgnitions(array $ignitions) : void{
		$this->ignitions = $ignitions;
	}

	/**
	 * Get the fire chance of the explosion
	 *
	 * @return float The fire chance (probability) of the explosion
	 */
	public function getFireChance() : float{
		return $this->fireChance;
	}

	/**
	 * Set the fire chance of the explosion
	 *
	 * @param float $fireChance The fire chance to set
	 */
	public function setFireChance(float $fireChance) : void{
		$this->fireChance = $fireChance;
	}
}
