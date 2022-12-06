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

namespace pocketmine\event\entity;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\utils\Utils;
use pocketmine\world\Position;

/**
 * Called when an entity explodes, after the explosion's impact has been calculated.
 * No changes have been made to the world at this stage.
 *
 * @see ExplosionPrimeEvent
 *
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityExplodeEvent extends EntityEvent implements Cancellable{
	use CancellableTrait;

	/** @var Position */
	protected $position;

	/** @var Block[] */
	protected $blocks;

	/** @var float */
	protected $yield;

	/**
	 * @param Block[] $blocks
	 * @param float   $yield  0-100
	 */
	public function __construct(Entity $entity, Position $position, array $blocks, float $yield){
		$this->entity = $entity;
		$this->position = $position;
		$this->blocks = $blocks;
		if($yield < 0.0 || $yield > 100.0){
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
		$this->yield = $yield;
	}

	public function getPosition() : Position{
		return $this->position;
	}

	/**
	 * Returns a list of blocks destroyed by the explosion.
	 *
	 * @return Block[]
	 */
	public function getBlockList() : array{
		return $this->blocks;
	}

	/**
	 * Sets the blocks destroyed by the explosion.
	 *
	 * @param Block[] $blocks
	 */
	public function setBlockList(array $blocks) : void{
		Utils::validateArrayValueType($blocks, function(Block $_) : void{});
		$this->blocks = $blocks;
	}

	/**
	 * Returns the percentage chance of drops from each block destroyed by the explosion.
	 * @return float 0-100
	 */
	public function getYield() : float{
		return $this->yield;
	}

	/**
	 * Sets the percentage chance of drops from each block destroyed by the explosion.
	 * @param float $yield 0-100
	 */
	public function setYield(float $yield) : void{
		if($yield < 0.0 || $yield > 100.0){
			throw new \InvalidArgumentException("Yield must be in range 0.0 - 100.0");
		}
		$this->yield = $yield;
	}
}
