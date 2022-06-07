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

	/** @var float */
	protected $force;
	private bool $blockBreaking = true;

	public function __construct(Entity $entity, float $force){
		if($force <= 0){
			throw new \InvalidArgumentException("Explosion radius must be positive");
		}
		$this->entity = $entity;
		$this->force = $force;
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
}
