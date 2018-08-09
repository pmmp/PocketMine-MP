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

use pocketmine\entity\projectile\Projectile;
use pocketmine\math\RayTraceResult;

/**
 * @allowHandle
 */
abstract class ProjectileHitEvent extends EntityEvent{
	/** @var RayTraceResult */
	private $rayTraceResult;

	/**
	 * @param Projectile     $entity
	 * @param RayTraceResult $rayTraceResult
	 */
	public function __construct(Projectile $entity, RayTraceResult $rayTraceResult){
		$this->entity = $entity;
		$this->rayTraceResult = $rayTraceResult;
	}

	/**
	 * @return Projectile
	 */
	public function getEntity(){
		return $this->entity;
	}

	/**
	 * Returns a RayTraceResult object containing information such as the exact position struck, the AABB it hit, and
	 * the face of the AABB that it hit.
	 *
	 * @return RayTraceResult
	 */
	public function getRayTraceResult() : RayTraceResult{
		return $this->rayTraceResult;
	}
}