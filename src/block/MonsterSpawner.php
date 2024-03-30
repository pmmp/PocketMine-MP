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
use function mt_rand;

class MonsterSpawner extends Transparent{

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

		if(!$spawnerTile instanceof TileSpawner || $spawnerTile->closed || $spawnerTile->getEntityTypeId() === TileSpawner::DEFAULT_ENTITY_TYPE_ID){
			return false;
		}
		$spawnDelay = $spawnerTile->getSpawnDelay();
		if($spawnDelay > 0){
			$spawnerTile->setSpawnDelay($spawnDelay - 1);
			return true;
		}
		$position = $spawnerTile->getPosition();
		$world = $position->getWorld();
		if($world->getNearestEntity($position, $spawnerTile->getRequiredPlayerRange(), Player::class) === null){
			return true;
		}
		$entityTypeId = $spawnerTile->getEntityTypeId();
		$spawnRange = $spawnerTile->getSpawnRange();
		$count = 0;
		$spawnRangeBB = SpawnerSpawnRangeRegistry::getInstance()->getSpawnRange($entityTypeId) ?? AxisAlignedBB::one()->expand($spawnRange * 2 + 1, 8, $spawnRange * 2 + 1);
		$spawnRangeBB->offset($position->x, $position->y, $position->z);
		foreach($world->getNearbyEntities($spawnRangeBB) as $entity){
			if($entity::getNetworkTypeId() === $entityTypeId){
				$count++;
				if($count >= $spawnerTile->getMaxNearbyEntities()){
					return true;
				}
			}
		}
		if(SpawnerAttemptSpawnEvent::hasHandlers()){
			$ev = new SpawnerAttemptSpawnEvent($spawnerTile->getBlock(), $entityTypeId);
			$ev->call();
			if($ev->isCancelled()){
				return true;
			}
			$entityTypeId = $ev->getEntityType();
		}
		// TODO: spawn condition check (light level etc.)
		for($i = 0; $i < $spawnerTile->getSpawnPerAttempt(); $i++){
			$spawnLocation = $position->add(mt_rand(-$spawnRange, $spawnRange), 0, mt_rand(-$spawnRange, $spawnRange));
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
				if($count >= $spawnerTile->getMaxNearbyEntities()){
					break;
				}
			}
		}
		$spawnerTile->setSpawnDelay(mt_rand($spawnerTile->getMinSpawnDelay(), $spawnerTile->getMaxSpawnDelay()));
		return true;
	}
}
