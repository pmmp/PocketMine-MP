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
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class LeashKnot extends Entity{

	public const NETWORK_ID = self::LEASH_KNOT;

	/** @var float */
	protected $gravity = 0.0;
	/** @var float */
	protected $drag = 0.0;
	/** @var float */
	public $height = 0.33;
	/** @var float */
	public $width = 0.1875;
	/** @var int */
	public $dropCounter = 0;

	/**
	 * LeashKnot constructor.
	 *
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 */
	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);

		$this->setPosition($this->getHangingPosition()->add(0.5, 0.25, 0.5));
		$this->boundingBox = new AxisAlignedBB($this->x - 0.1875, $this->y - 0.25 + 0.125, $this->z - 0.1875, $this->x + 0.1875, $this->y + 0.25 + 0.125, $this->z + 0.1875);
	}

	public function initEntity() : void{
		$this->setMaxHealth(1);
		parent::initEntity();
	}

	/**
	 * @return bool
	 */
	public function isSurfaceValid() : bool{
		return $this->level->getBlock($this) instanceof Fence;
	}

	/**
	 * @return bool
	 */
	public function hasMovementUpdate() : bool{
		return false;
	}

	/**
	 * @param bool $teleport
	 */
	protected function updateMovement(bool $teleport = false) : void{

	}

	/**
	 * @return bool
	 */
	public function canBeCollidedWith() : bool{
		return false;
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
	 *
	 * @return bool
	 */
	public function onFirstInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		$flag = false;

		if($item->getId() === Item::LEAD){
			$f = 7.0;

			foreach($player->level->getCollidingEntities(new AxisAlignedBB($this->x - $f, $this->y - $f, $this->z - $f, $this->x + $f, $this->y + $f, $this->z + $f)) as $entity){
				if($entity instanceof Living){
					if($entity->isLeashed() and $entity->getLeashedToEntity() === $player){
						$entity->setLeashedToEntity($this, true);
						$flag = true;
					}
				}
			}
		}

		if(!$flag){
			$this->kill();

			if($player->isCreative()){
				$f = 7.0;

				foreach($player->level->getCollidingEntities(new AxisAlignedBB($this->x - $f, $this->y - $f, $this->z - $f, $this->x + $f, $this->y + $f, $this->z + $f)) as $entity){
					if($entity instanceof Living){
						if($entity->isLeashed() and $entity->getLeashedToEntity() === $this){
							$entity->clearLeashed(true, false);
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param EntityDamageEvent $source
	 */
	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);

		if(!$source->isCancelled()){
			$this->kill();
		}
	}

	public function kill() : void{
		parent::kill();

		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LEASHKNOT_BREAK);
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