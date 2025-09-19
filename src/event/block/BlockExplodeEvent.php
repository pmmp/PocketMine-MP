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
use pocketmine\utils\Utils;
use pocketmine\world\Position;

/**
 * Called when a block explodes, after explosion impact has been calculated.
 *
 * @see BlockPreExplodeEvent
 */
class BlockExplodeEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	/**
	 * @param Block[] $blocks
	 * @param Block[] $ignitions
	 */
	public function __construct(
		Block $block,
		private Position $position,
		private array $blocks,
		private float $yield,
		private array $ignitions
	){
		parent::__construct($block);

		Utils::checkFloatNotInfOrNaN("yield", $yield);
		if($yield < 0.0 || $yield > 100.0){
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
	}

	public function getPosition() : Position{
		return $this->position;
	}

	/**
	 * Returns the percentage chance of drops from each block destroyed by the explosion.
	 *
	 * @return float 0-100
	 */
	public function getYield() : float{
		return $this->yield;
	}

	/**
	 * Sets the percentage chance of drops from each block destroyed by the explosion.
	 *
	 * @param float $yield 0-100
	 */
	public function setYield(float $yield) : void{
		Utils::checkFloatNotInfOrNaN("yield", $yield);
		if($yield < 0.0 || $yield > 100.0){
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
		$this->yield = $yield;
	}

	/**
	 * Returns a list of blocks destroyed by the explosion.
	 *
	 * @return Block[]
	 */
	public function getAffectedBlocks() : array{
		return $this->blocks;
	}

	/**
	 * Sets the blocks destroyed by the explosion.
	 *
	 * @param Block[] $blocks
	 */
	public function setAffectedBlocks(array $blocks) : void{
		Utils::validateArrayValueType($blocks, fn(Block $block) => null);
		$this->blocks = $blocks;
	}

	/**
	 * Returns a list of affected blocks that will be replaced by fire.
	 *
	 * @return Block[]
	 */
	public function getIgnitions() : array{
		return $this->ignitions;
	}

	/**
	 * Set the list of blocks that will be replaced by fire.
	 *
	 * @param Block[] $ignitions
	 */
	public function setIgnitions(array $ignitions) : void{
		Utils::validateArrayValueType($ignitions, fn(Block $block) => null);
		$this->ignitions = $ignitions;
	}
}
