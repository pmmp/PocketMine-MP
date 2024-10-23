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

namespace pocketmine\block;

use pocketmine\block\tile\MonsterSpawner as TileSpawner;
use pocketmine\block\tile\SpawnerSpawnRangeRegistry;
use pocketmine\block\utils\SupportType;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\event\block\SpawnerAttemptSpawnEvent;
use pocketmine\item\Item;
use pocketmine\item\SpawnEgg;
use pocketmine\item\SpawnEggEntityRegistry;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\world\particle\MobSpawnParticle;
use function assert;
use function mt_rand;

class MonsterSpawner extends Transparent{

	/** TODO: replace this with a cached entity or something of that nature */
	private string $entityTypeId = TileSpawner::DEFAULT_ENTITY_TYPE_ID;
	/** TODO: deserialize this properly and drop the NBT (PC and PE formats are different, just for fun) */
	private ?ListTag $spawnPotentials = null;
	/** TODO: deserialize this properly and drop the NBT (PC and PE formats are different, just for fun) */
	private ?CompoundTag $spawnData = null;

	private float $displayEntityWidth = 1.0;
	private float $displayEntityHeight = 1.0;
	private float $displayEntityScale = 1.0;

	private int $spawnDelay = TileSpawner::DEFAULT_MIN_SPAWN_DELAY;
	private int $minSpawnDelay = TileSpawner::DEFAULT_MIN_SPAWN_DELAY;
	private int $maxSpawnDelay = TileSpawner::DEFAULT_MAX_SPAWN_DELAY;
	private int $spawnPerAttempt = 1;
	private int $maxNearbyEntities = TileSpawner::DEFAULT_MAX_NEARBY_ENTITIES;
	private int $spawnRange = TileSpawner::DEFAULT_SPAWN_RANGE;
	private int $requiredPlayerRange = TileSpawner::DEFAULT_REQUIRED_PLAYER_RANGE;

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileSpawner){
			$this->entityTypeId = $tile->getEntityTypeId();
			$this->spawnPotentials = $tile->getSpawnPotentials();
			$this->spawnData = $tile->getSpawnData();
			$this->displayEntityWidth = $tile->getDisplayEntityWidth();
			$this->displayEntityHeight = $tile->getDisplayEntityHeight();
			$this->displayEntityScale = $tile->getDisplayEntityScale();
			$this->spawnDelay = $tile->getSpawnDelay();
			$this->minSpawnDelay = $tile->getMinSpawnDelay();
			$this->maxSpawnDelay = $tile->getMaxSpawnDelay();
			$this->spawnPerAttempt = $tile->getSpawnPerAttempt();
			$this->maxNearbyEntities = $tile->getMaxNearbyEntities();
			$this->spawnRange = $tile->getSpawnRange();
			$this->requiredPlayerRange = $tile->getRequiredPlayerRange();
		}else{
			$this->entityTypeId = TileSpawner::DEFAULT_ENTITY_TYPE_ID;
			$this->spawnPotentials = null;
			$this->spawnData = null;
			$this->displayEntityWidth = 1.0;
			$this->displayEntityHeight = 1.0;
			$this->displayEntityScale = 1.0;
			$this->spawnDelay = TileSpawner::DEFAULT_MIN_SPAWN_DELAY;
			$this->minSpawnDelay = TileSpawner::DEFAULT_MIN_SPAWN_DELAY;
			$this->maxSpawnDelay = TileSpawner::DEFAULT_MAX_SPAWN_DELAY;
			$this->spawnPerAttempt = 1;
			$this->maxNearbyEntities = TileSpawner::DEFAULT_MAX_NEARBY_ENTITIES;
			$this->spawnRange = TileSpawner::DEFAULT_SPAWN_RANGE;
			$this->requiredPlayerRange = TileSpawner::DEFAULT_REQUIRED_PLAYER_RANGE;
		}
		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileSpawner);
		$tile->setEntityTypeId($this->entityTypeId);
		$tile->setSpawnPotentials($this->spawnPotentials);
		$tile->setSpawnData($this->spawnData);
		$tile->setDisplayEntityWidth($this->displayEntityWidth);
		$tile->setDisplayEntityHeight($this->displayEntityHeight);
		$tile->setDisplayEntityScale($this->displayEntityScale);
		$tile->setSpawnDelay($this->spawnDelay);
		$tile->setMinSpawnDelay($this->minSpawnDelay);
		$tile->setMaxSpawnDelay($this->maxSpawnDelay);
		$tile->setSpawnPerAttempt($this->spawnPerAttempt);
		$tile->setMaxNearbyEntities($this->maxNearbyEntities);
		$tile->setSpawnRange($this->spawnRange);
		$tile->setRequiredPlayerRange($this->requiredPlayerRange);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	protected function getXpDropAmount() : int{
		return mt_rand(15, 43);
	}

	public function onScheduledUpdate() : void{
		if($this->onUpdate()){
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
		}
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof SpawnEgg){
			$entityId = SpawnEggEntityRegistry::getInstance()->getEntityId($item);
			if($entityId === null){
				return false;
			}
			$spawner = $this->position->getWorld()->getTile($this->position);
			if($spawner instanceof TileSpawner){
				$spawner->setEntityTypeId($entityId);
				$this->position->getWorld()->setBlock($this->position, $this);
				$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
				return true;
			}
		}
		return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
	}

	public function onUpdate() : bool{
		$world = $this->position->getWorld();
		$spawnerTile = $world->getTile($this->position);

		if(!$spawnerTile instanceof TileSpawner || $spawnerTile->closed || $this->entityTypeId === TileSpawner::DEFAULT_ENTITY_TYPE_ID){
			return false;
		}
		if($this->spawnDelay > 0){
			$this->spawnDelay--;
			return true;
		}
		$position = $this->getPosition();
		$world = $position->getWorld();
		if($world->getNearestEntity($position, $this->requiredPlayerRange, Player::class) === null){
			return true;
		}
		$count = 0;
		$spawnRangeBB = SpawnerSpawnRangeRegistry::getInstance()->getSpawnRange($this->entityTypeId) ?? AxisAlignedBB::one()->expand($this->spawnRange * 2 + 1, 8, $this->spawnRange * 2 + 1);
		$spawnRangeBB->offset($position->x, $position->y, $position->z);
		foreach($world->getNearbyEntities($spawnRangeBB) as $entity){
			if($entity::getNetworkTypeId() === $this->entityTypeId){
				$count++;
				if($count >= $this->maxNearbyEntities){
					return true;
				}
			}
		}
		$entityTypeId = $this->entityTypeId;
		if(SpawnerAttemptSpawnEvent::hasHandlers()){
			$ev = new SpawnerAttemptSpawnEvent($this, $entityTypeId);
			$ev->call();
			if($ev->isCancelled()){
				return true;
			}
			$entityTypeId = $ev->getEntityType();
		}
		// TODO: spawn condition check (light level etc.)
		for($i = 0; $i < $this->spawnPerAttempt; $i++){
			$spawnLocation = $position->add(mt_rand(-$this->spawnRange, $this->spawnRange), 0, mt_rand(-$this->spawnRange, $this->spawnRange));
			$spawnLocation = Location::fromObject($spawnLocation, $world);
			$nbt = CompoundTag::create()
				->setString(EntityFactory::TAG_IDENTIFIER, $entityTypeId)
				->setTag(Entity::TAG_POS, new ListTag([
					new DoubleTag($spawnLocation->x),
					new DoubleTag($spawnLocation->y),
					new DoubleTag($spawnLocation->z)
				]))
				->setTag(Entity::TAG_ROTATION, new ListTag([
					new FloatTag($spawnLocation->yaw),
					new FloatTag($spawnLocation->pitch)
				]));
			// TODO: spawnData, spawnPotentials
			$entity = EntityFactory::getInstance()->createFromData($world, $nbt);
			if($entity !== null){
				$entity->spawnToAll();
				$world->addParticle($spawnLocation, new MobSpawnParticle((int) $entity->getSize()->getWidth(), (int) $entity->getSize()->getHeight()));
				$count++;
				if($count >= $this->maxNearbyEntities){
					break;
				}
			}
		}
		$this->spawnDelay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);
		return true;
	}

	public function getEntityTypeId() : string{
		return $this->entityTypeId;
	}

	public function setEntityTypeId(string $entityTypeId) : void{
		$this->entityTypeId = $entityTypeId;
	}

	public function getSpawnPotentials() : ?ListTag{
		return $this->spawnPotentials;
	}

	public function setSpawnPotentials(?ListTag $spawnPotentials) : void{
		$this->spawnPotentials = $spawnPotentials;
	}

	public function getSpawnData() : ?CompoundTag{
		return $this->spawnData;
	}

	public function setSpawnData(?CompoundTag $spawnData) : void{
		$this->spawnData = $spawnData;
	}

	public function getDisplayEntityHeight() : float{
		return $this->displayEntityHeight;
	}

	public function setDisplayEntityHeight(float $displayEntityHeight) : void{
		$this->displayEntityHeight = $displayEntityHeight;
	}

	public function getDisplayEntityWidth() : float{
		return $this->displayEntityWidth;
	}

	public function setDisplayEntityWidth(float $displayEntityWidth) : void{
		$this->displayEntityWidth = $displayEntityWidth;
	}

	public function getDisplayEntityScale() : float{
		return $this->displayEntityScale;
	}

	public function setDisplayEntityScale(float $displayEntityScale) : void{
		$this->displayEntityScale = $displayEntityScale;
	}

	public function getSpawnDelay() : int{
		return $this->spawnDelay;
	}

	public function setSpawnDelay(int $spawnDelay) : void{
		$this->spawnDelay = $spawnDelay;
	}

	public function getMinSpawnDelay() : int{
		return $this->minSpawnDelay;
	}

	public function setMinSpawnDelay(int $minSpawnDelay) : void{
		$this->minSpawnDelay = $minSpawnDelay;
	}

	public function getMaxSpawnDelay() : int{
		return $this->maxSpawnDelay;
	}

	public function setMaxSpawnDelay(int $maxSpawnDelay) : void{
		$this->maxSpawnDelay = $maxSpawnDelay;
	}

	public function getRequiredPlayerRange() : int{
		return $this->requiredPlayerRange;
	}

	public function setRequiredPlayerRange(int $requiredPlayerRange) : void{
		$this->requiredPlayerRange = $requiredPlayerRange;
	}

	public function getSpawnRange() : int{
		return $this->spawnRange;
	}

	public function setSpawnRange(int $spawnRange) : void{
		$this->spawnRange = $spawnRange;
	}

	public function getSpawnPerAttempt() : int{
		return $this->spawnPerAttempt;
	}

	public function setSpawnPerAttempt(int $spawnPerAttempt) : void{
		$this->spawnPerAttempt = $spawnPerAttempt;
	}

	public function getMaxNearbyEntities() : int{
		return $this->maxNearbyEntities;
	}

	public function setMaxNearbyEntities(int $maxNearbyEntities) : void{
		$this->maxNearbyEntities = $maxNearbyEntities;
	}
}
