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

namespace pocketmine\tile;

use pocketmine\block\Air;
use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\Server;

class MobSpawner extends Spawnable{

	// TODO: Use more nbt tags for more customization
	public const TAG_IS_MOVABLE = "isMovable";
	public const TAG_DELAY = "Delay";
	public const TAG_MAX_NEARBY_ENTITIES = "MaxNearbyEntities";
	public const TAG_MAX_SPAWN_DELAY = "MaxSpawnDelay";
	public const TAG_MIN_SPAWN_DELAY = "MinSawnDelay";
	public const TAG_REQUIRED_PLAYER_RANGE = "RequiredPlayerRange";
	public const TAG_SPAWN_COUNT = "SpawnCount";
	public const TAG_SPAWN_RANGE = "SpawnRange";
	public const TAG_ENTITY_ID = "EntityId";
	public const TAG_DISPLAY_ENTITY_HEIGHT = "DisplayEntityHeight";
	public const TAG_DISPLAY_ENTITY_SCALE = "DisplayEntityScale";
	public const TAG_DISPLAY_ENTITY_WIDTH = "DisplayEntityWidth";
	public const TAG_SPAWN_DATA = "SpawnData"; // TODO

	/** @var int */
	protected $entityId = -1;
	/** @var int */
	protected $spawnRange = 4;
	/** @var int */
	protected $maxNearbyEntities = 6;
	/** @var int */
	protected $requiredPlayerRange = 16;
	/** @var int */
	protected $delay = 0;
	/** @var int */
	protected $minSpawnDelay = 200;
	/** @var int */
	protected $maxSpawnDelay = 800;
	/** @var int */
	protected $spawnCount = 1;
	/** @var bool */
	protected $isMovable = true;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);

		$this->scheduleUpdate();
	}

	/**
	 * @return int
	 */
	public function getEntityId() : int{
		return $this->entityId;
	}

	/**
	 * @param int $entityId
	 */
	public function setEntityId(int $entityId) : void{
		$this->entityId = $entityId;
		$this->onChanged();
	}

	/**
	 * @return int
	 */
	public function getSpawnRange() : int{
		return $this->spawnRange;
	}

	/**
	 * @param int $spawnRange
	 */
	public function setSpawnRange(int $spawnRange) : void{
		$this->spawnRange = $spawnRange;
	}

	/**
	 * @return int
	 */
	public function getMaxNearbyEntities() : int{
		return $this->maxNearbyEntities;
	}

	/**
	 * @param int $maxNearbyEntities
	 */
	public function setMaxNearbyEntities(int $maxNearbyEntities) : void{
		$this->maxNearbyEntities = $maxNearbyEntities;
	}

	/**
	 * @return int
	 */
	public function getRequiredPlayerRange() : int{
		return $this->requiredPlayerRange;
	}

	/**
	 * @param int $requiredPlayerRange
	 */
	public function setRequiredPlayerRange(int $requiredPlayerRange) : void{
		$this->requiredPlayerRange = $requiredPlayerRange;
	}

	/**
	 * @return int
	 */
	public function getDelay() : int{
		return $this->delay;
	}

	/**
	 * @param int $delay
	 */
	public function setDelay(int $delay) : void{
		$this->delay = $delay;
	}

	/**
	 * @return int
	 */
	public function getMinSpawnDelay() : int{
		return $this->minSpawnDelay;
	}

	/**
	 * @param int $minSpawnDelay
	 */
	public function setMinSpawnDelay(int $minSpawnDelay) : void{
		$this->minSpawnDelay = $minSpawnDelay;
	}

	/**
	 * @return int
	 */
	public function getMaxSpawnDelay() : int{
		return $this->maxSpawnDelay;
	}

	/**
	 * @param int $maxSpawnDelay
	 */
	public function setMaxSpawnDelay(int $maxSpawnDelay) : void{
		$this->maxSpawnDelay = $maxSpawnDelay;
	}

	/**
	 * @return int
	 */
	public function getSpawnCount() : int{
		return $this->spawnCount;
	}

	/**
	 * @param int $spawnCount
	 */
	public function setSpawnCount(int $spawnCount) : void{
		$this->spawnCount = $spawnCount;
	}

	/**
	 * @return bool
	 */
	public function isMovable() : bool{
		return $this->isMovable;
	}

	/**
	 * @param bool $isMovable
	 */
	public function setMovable(bool $isMovable) : void{
		$this->isMovable = $isMovable;
		$this->onChanged();
	}

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->delay = $nbt->getShort(self::TAG_DELAY, 0, true);
		$this->maxNearbyEntities = $nbt->getShort(self::TAG_MAX_NEARBY_ENTITIES, 6, true);
		$this->maxSpawnDelay = $nbt->getShort(self::TAG_MAX_SPAWN_DELAY, 800, true);
		$this->minSpawnDelay = $nbt->getShort(self::TAG_MIN_SPAWN_DELAY, 200, true);
		$this->requiredPlayerRange = $nbt->getShort(self::TAG_REQUIRED_PLAYER_RANGE, 16, true);
		$this->spawnCount = $nbt->getShort(self::TAG_SPAWN_COUNT, 1, true);
		$this->spawnRange = $nbt->getShort(self::TAG_SPAWN_RANGE, 4, true);
		$this->entityId = $nbt->getInt(self::TAG_ENTITY_ID, -1, true);
	}

	public function getDefaultName() : string{
		return "MobSpawner";
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_IS_MOVABLE, intval($this->isMovable), true);
		$nbt->setShort(self::TAG_DELAY, $this->delay, true);
		$nbt->setShort(self::TAG_MAX_NEARBY_ENTITIES, $this->maxNearbyEntities, true);
		$nbt->setShort(self::TAG_MAX_SPAWN_DELAY, $this->maxSpawnDelay, true);
		$nbt->setShort(self::TAG_MIN_SPAWN_DELAY, $this->minSpawnDelay, true);
		$nbt->setShort(self::TAG_REQUIRED_PLAYER_RANGE, $this->requiredPlayerRange, true);
		$nbt->setShort(self::TAG_SPAWN_COUNT, $this->spawnCount, true);
		$nbt->setShort(self::TAG_SPAWN_RANGE, $this->spawnRange, true);
		$nbt->setInt(self::TAG_ENTITY_ID, $this->entityId, true);
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_IS_MOVABLE, intval($this->isMovable));
		$nbt->setInt(self::TAG_ENTITY_ID, $this->entityId);
	}

	public function onUpdate() : bool{
		if($this->entityId !== -1){
			if($this->delay++ >= rand($this->minSpawnDelay, $this->maxSpawnDelay)){
				$nearEntityCount = 0;
				$canSpawnMob = false;

				foreach($this->level->getEntities() as $entity){
					if($entity->distance($this) <= $this->requiredPlayerRange){
						if($entity instanceof Player){
							$canSpawnMob = true;
						}else{
							$nearEntityCount++;
						}
					}
				}

				if($canSpawnMob and $nearEntityCount <= $this->maxNearbyEntities){
					for($i = 0; $i < $this->spawnCount; $i++){
						$spawnPos = $this->add(rand(-$this->spawnRange, $this->spawnRange), rand(0, 1), rand(-$this->spawnRange, $this->spawnRange));
						if($this->isValidSpawnPosition($spawnPos)){
							$mob = Entity::createEntity($this->entityId, $this->level, Entity::createBaseNBT($spawnPos->add(0.5, 0, 0.5)));
							if($mob instanceof Entity){
								if($mob instanceof Mob){
									if(Server::getInstance()->mobAiEnabled){
										$mob->setImmobile(false);
									}
								}
								$mob->spawnToAll();
							}
						}
					}
				}

				$this->delay = 0;
			}
		}
		return true;
	}

	public function isValidSpawnPosition(Vector3 $pos) : bool{
		return $this->level->getBlock($pos) instanceof Air and $this->level->getBlock($pos->up()) instanceof Air and $this->level->getBlock($pos->down())->isSolid();
	}
}