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
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;

class EntityShootBowEvent extends EntityEvent implements Cancellable{
	/** @var Item */
	private $bow;
	/** @var Projectile */
	private $projectile;
	/** @var float */
	private $force;

	/**
	 * @param Living     $shooter
	 * @param Item       $bow
	 * @param Projectile $projectile
	 * @param float      $force
	 */
	public function __construct(Living $shooter, Item $bow, Projectile $projectile, float $force){
		$this->entity = $shooter;
		$this->bow = $bow;
		$this->projectile = $projectile;
		$this->force = $force;
	}

	/**
	 * @return Living
	 */
	public function getEntity(){
		return $this->entity;
	}

	/**
	 * @return Item
	 */
	public function getBow() : Item{
		return $this->bow;
	}

	/**
	 * Returns the entity considered as the projectile in this event.
	 *
	 * NOTE: This might not return a Projectile if a plugin modified the target entity.
	 *
	 * @return Entity
	 */
	public function getProjectile() : Entity{
		return $this->projectile;
	}

	/**
	 * @param Entity $projectile
	 */
	public function setProjectile(Entity $projectile){
		if($projectile !== $this->projectile){
			if(count($this->projectile->getViewers()) === 0){
				$this->projectile->close();
			}
			$this->projectile = $projectile;
		}
	}

	/**
	 * @return float
	 */
	public function getForce() : float{
		return $this->force;
	}

	/**
	 * @param float $force
	 */
	public function setForce(float $force){
		$this->force = $force;
	}


}
