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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\math\RayTraceResult;

class MovingObjectPosition{
	public const TYPE_BLOCK_COLLISION = 0;
	public const TYPE_ENTITY_COLLISION = 1;

	/** @var RayTraceResult */
	public $hitResult;

	/** @var int */
	public $typeOfHit;

	/** @var Entity|null */
	public $entityHit = null;
	/** @var Block|null */
	public $blockHit = null;

	protected function __construct(int $hitType, RayTraceResult $hitResult){
		$this->typeOfHit = $hitType;
		$this->hitResult = $hitResult;
	}

	/**
	 * @param Block          $block
	 * @param RayTraceResult $result
	 *
	 * @return MovingObjectPosition
	 */
	public static function fromBlock(Block $block, RayTraceResult $result) : MovingObjectPosition{
		$ob = new MovingObjectPosition(self::TYPE_BLOCK_COLLISION, $result);
		$ob->blockHit = $block;
		return $ob;
	}

	/**
	 * @param Entity         $entity
	 *
	 * @param RayTraceResult $result
	 *
	 * @return MovingObjectPosition
	 */
	public static function fromEntity(Entity $entity, RayTraceResult $result) : MovingObjectPosition{
		$ob = new MovingObjectPosition(self::TYPE_ENTITY_COLLISION, $result);
		$ob->entityHit = $entity;

		return $ob;
	}
}
