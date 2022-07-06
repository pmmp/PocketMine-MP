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

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

/**
 * Called when an entity decides to explode, before the explosion's impact is calculated.
 * This allows changing the force of the explosion and whether it will destroy blocks.
 *
 * @see EntityExplodeEvent
 *
 * @phpstan-extends EntityEvent<Entity>
 */
class ExplosionPrimeEvent extends EntityEvent implements Cancellable{
	use CancellableTrait;

	protected float $force;
	private bool $blockBreaking = true;
	private bool $canBreakAll;

	public function __construct(Entity $entity, float $force, bool $canBreakAll){
		if($force <= 0){
			throw new \InvalidArgumentException("Explosion radius must be positive");
		}
		$this->entity = $entity;
		$this->force = $force;
		$this->canBreakAll = $canBreakAll;
	}

	public function getForce() : float{
		return $this->force;
	}

	public function setForce(float $force) : void{
		if($force <= 0){
			throw new \InvalidArgumentException("Explosion radius must be positive");
		}
		$this->force = $force;
	}
	public function isBlockBreaking() : bool{
		return $this->blockBreaking;
	}

	public function setBlockBreaking(bool $affectsBlocks) : void{
		$this->blockBreaking = $affectsBlocks;
	}

	/**
	 * Destroy the unbreakable blocks like bedrock
	 */
	public function canBreakAll() : bool{
		return $this->canBreakAll;
	}

	public function setCanBreakAll(bool $value = true) : void{
		$this->canBreakAll = $value;
	}

}
