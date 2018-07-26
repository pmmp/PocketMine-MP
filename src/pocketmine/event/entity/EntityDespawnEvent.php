<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\event\entity;

use pocketmine\entity\Creature;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\Vehicle;

/**
 * Called when a entity is despawned
 */
class EntityDespawnEvent extends EntityEvent{
	/** @var int */
	private $entityType;

	/**
	 * @param Entity $entity
	 */
	public function __construct(Entity $entity){
		$this->entity = $entity;
		$this->entityType = $entity::NETWORK_ID;
	}

	/**
	 * @return int
	 */
	public function getType() : int{
		return $this->entityType;
	}

	/**
	 * @return bool
	 */
	public function isCreature() : bool{
		return $this->entity instanceof Creature;
	}

	/**
	 * @return bool
	 */
	public function isHuman() : bool{
		return $this->entity instanceof Human;
	}

	/**
	 * @return bool
	 */
	public function isProjectile() : bool{
		return $this->entity instanceof Projectile;
	}

	/**
	 * @return bool
	 */
	public function isVehicle() : bool{
		return $this->entity instanceof Vehicle;
	}

	/**
	 * @return bool
	 */
	public function isItem() : bool{
		return $this->entity instanceof ItemEntity;
	}

}