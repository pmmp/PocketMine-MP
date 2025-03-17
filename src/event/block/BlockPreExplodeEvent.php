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
use pocketmine\player\Player;
use pocketmine\utils\Utils;

/**
 * Event triggered before a block explosion, allowing modifications to the explosion radius, block destruction, and fire chances.
 * This event is used to customize the behavior of explosions before they happen.
 *
 * @see BlockExplodeEvent
 */
class BlockPreExplodeEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	private const DEFAULT_FIRE_CHANCE = 1.0 / 3.0;

	private bool $blockBreaking = true;

	public function __construct(
		Block $block,
		private float $radius,
		private readonly ?Player $player = null,
		private float $fireChance = 0.0
	){
		Utils::checkFloatNotInfOrNaN("radius", $radius);
		if($radius <= 0){
			throw new \InvalidArgumentException("Explosion radius must be positive");
		}
		Utils::checkFloatNotInfOrNaN("fireChance", $fireChance);
		if($fireChance < 0.0 || $fireChance > 1.0){
			throw new \InvalidArgumentException("Fire chance must be a number between 0 and 1.");
		}
		parent::__construct($block);
	}

	public function getRadius() : float{
		return $this->radius;
	}

	public function setRadius(float $radius) : void{
		Utils::checkFloatNotInfOrNaN("radius", $radius);
		if($radius <= 0){
			throw new \InvalidArgumentException("Explosion radius must be positive");
		}
		$this->radius = $radius;
	}

	/**
	 * Checking whether the block will collapse
	 */
	public function isBlockBreaking() : bool{
		return $this->blockBreaking;
	}

	/**
	 * Set whether a block will be destroyed
	 */
	public function setBlockBreaking(bool $affectsBlocks) : void{
		$this->blockBreaking = $affectsBlocks;
	}

	/**
	 * Checking if there will be a fire
	 */
	public function isIncendiary() : bool{
		return $this->fireChance > 0;
	}

	/**
	 * Establish the probability of fire
	 */
	public function setIncendiary(bool $incendiary) : void{
		if(!$incendiary){
			$this->fireChance = 0;
		}elseif($this->fireChance <= 0){
			$this->fireChance = self::DEFAULT_FIRE_CHANCE;
		}
	}

	/**
	 * Get the probability of fire
	 */
	public function getFireChance() : float{
		return $this->fireChance;
	}

	/**
	 * Establish the probability of fire
	 */
	public function setFireChance(float $fireChance) : void{
		Utils::checkFloatNotInfOrNaN("fireChance", $fireChance);
		if($fireChance < 0.0 || $fireChance > 1.0){
			throw new \InvalidArgumentException("Fire chance must be a number between 0 and 1.");
		}
		$this->fireChance = $fireChance;
	}

	/**
	 * Get the player if the event was caused by him
	 */
	public function getPlayer() : ?Player{
		return $this->player;
	}
}
