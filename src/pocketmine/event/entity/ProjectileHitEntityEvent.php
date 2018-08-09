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

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\math\RayTraceResult;

class ProjectileHitEntityEvent extends ProjectileHitEvent{

	/** @var Entity */
	private $entityHit;

	public function __construct(Projectile $entity, RayTraceResult $rayTraceResult, Entity $entityHit){
		parent::__construct($entity, $rayTraceResult);
		$this->entityHit = $entityHit;
	}

	/**
	 * Returns the Entity struck by the projectile.
	 *
	 * @return Entity
	 */
	public function getEntityHit() : Entity{
		return $this->entityHit;
	}
}