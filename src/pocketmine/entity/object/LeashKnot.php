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

namespace pocketmine\entity\object;

use pocketmine\block\Fence;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddHangingEntityPacket;
use pocketmine\Player;

class LeashKnot extends Entity{

	public const NETWORK_ID = self::LEASH_KNOT;

	/** @var float */
	protected $gravity = 0.0;
	/** @var float */
	protected $drag = 0.0;
	/** @var float */
	public $height = 0.25;
	/** @var float */
	public $width = 0.25;
	/** @var int */
	public $dropCounter = 0;

	/**
	 * @return bool
	 */
	public function isSurfaceValid() : bool{
		return $this->level->getBlock($this) instanceof Fence;
	}

	/**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->dropCounter++ >= 100 and $this->isValid()){
			$this->dropCounter = 0;
			if(!$this->isSurfaceValid() and !$this->isFlaggedForDespawn()){
				$this->kill();
			}
		}
		return parent::entityBaseTick($tickDiff);
	}

	/**
	 * @param Player  $player
	 * @param Item    $item
	 * @param Vector3 $clickPos
	 * @param int     $slot
	 *
	 * @return bool
	 */
	public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : bool{
		$this->kill();
		// TODO
		return true;
	}

	/**
	 * @param Player $player
	 */
	public function sendSpawnPacket(Player $player) : void{
		$pk = new AddHangingEntityPacket();
		$pk->entityUniqueId = $pk->entityRuntimeId = $this->id;
		$pk->x = $this->getFloorX();
		$pk->y = $this->getFloorY();
		$pk->z = $this->getFloorZ();
		$pk->direction = 0;

		$player->sendDataPacket($pk);
	}

	/**
	 * @return Position
	 */
	public function getHangingPosition() : Position{
		return new Position($this->getFloorX(), $this->getFloorY(), $this->getFloorZ(), $this->level);
	}

	/**
	 * @param Level   $level
	 * @param Vector3 $pos
	 *
	 * @return null|LeashKnot
	 */
	public static function getKnotFromPosition(Level $level, Vector3 $pos) : ?LeashKnot{
		foreach($level->getCollidingEntities(new AxisAlignedBB($pos->x - 1, $pos->y - 1, $pos->z - 1, $pos->x + 1, $pos->y + 1, $pos->z + 1)) as $entity){
			if($entity instanceof LeashKnot){
				if($entity->getHangingPosition()->equals($pos)){
					return $entity;
				}
			}
		}

		return null;
	}
}