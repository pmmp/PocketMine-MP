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

namespace pocketmine\level;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;

class MovingObjectPosition{
	public const TYPE_BLOCK_COLLISION = 0;
	public const TYPE_ENTITY_COLLISION = 1;

	/** @var int */
	public $typeOfHit;

	/** @var int|null */
	public $blockX;
	/** @var int|null */
	public $blockY;
	/** @var int|null */
	public $blockZ;

	/**
	 * @var int|null
	 * Which side was hit. If its -1 then it went the full length of the ray trace.
	 * -1 or one of the Vector3::SIDE_* constants
	 */
	public $sideHit;

	/** @var Vector3 */
	public $hitVector;

	/** @var Entity|null */
	public $entityHit = null;

	protected function __construct(){

	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $side
	 * @param Vector3 $hitVector
	 *
	 * @return MovingObjectPosition
	 */
	public static function fromBlock(int $x, int $y, int $z, int $side, Vector3 $hitVector) : MovingObjectPosition{
		$ob = new MovingObjectPosition;
		$ob->typeOfHit = self::TYPE_BLOCK_COLLISION;
		$ob->blockX = $x;
		$ob->blockY = $y;
		$ob->blockZ = $z;
		$ob->sideHit = $side;
		$ob->hitVector = new Vector3($hitVector->x, $hitVector->y, $hitVector->z);
		return $ob;
	}

	/**
	 * @param Entity $entity
	 *
	 * @return MovingObjectPosition
	 */
	public static function fromEntity(Entity $entity) : MovingObjectPosition{
		$ob = new MovingObjectPosition;
		$ob->typeOfHit = self::TYPE_ENTITY_COLLISION;
		$ob->entityHit = $entity;
		$ob->hitVector = new Vector3($entity->x, $entity->y, $entity->z);
		return $ob;
	}
}
