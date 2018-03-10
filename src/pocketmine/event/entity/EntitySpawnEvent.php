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

use pocketmine\entity\Creature;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\Vehicle;
use pocketmine\level\Position;

/**
 * Called when a entity is spawned
 */
class EntitySpawnEvent extends EntityEvent{
	public static $handlerList = null;

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
	 * @return Position
	 */
	public function getPosition() : Position{
		return $this->entity->getPosition();
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
