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

namespace pocketmine\level;

use pocketmine\block\Block;
use pocketmine\block\Leaves;
use pocketmine\block\Liquid;
use pocketmine\block\Water;
use pocketmine\entity\Animal;
use pocketmine\entity\Creature;
use pocketmine\entity\CreatureType;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Mob;
use pocketmine\entity\Monster;
use pocketmine\entity\SpawnPlacementTypes;
use pocketmine\entity\WaterAnimal;
use pocketmine\level\biome\Biome;
use pocketmine\level\biome\SpawnListEntry;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;
use pocketmine\utils\WeightedRandomItem;

use function array_filter;
use function count;
use function is_a;

class AnimalSpawner{
	public const MAX_MOBS = 289;

	/** @var CreatureType[] */
	public static $creatureTypes = [];

	public function __construct(){
		self::$creatureTypes[Monster::class] = new CreatureType(Monster::class, 70, Block::AIR, false);
		self::$creatureTypes[Animal::class] = new CreatureType(Animal::class, 10, Block::AIR, true);
		self::$creatureTypes[Creature::class] = new CreatureType(Creature::class, 15, Block::AIR, false);
		self::$creatureTypes[WaterAnimal::class] = new CreatureType(WaterAnimal::class, 5, Block::STILL_WATER, false);
	}

	/**
	 * @param Level $level
	 * @param bool  $spawnHostileMobs
	 * @param bool  $spawnPeacefulMobs
	 * @param array $eligibleChunks
	 */
	public function findChunksForSpawning(Level $level, bool $spawnHostileMobs, bool $spawnPeacefulMobs, array $eligibleChunks) : void{
		if($spawnHostileMobs or $spawnPeacefulMobs){
			$spawn = $level->getSpawnLocation();

			foreach(self::$creatureTypes as $creatureType){
				if((!$creatureType->isPeacefulCreature() or $spawnPeacefulMobs) and ($creatureType->isPeacefulCreature() or $spawnHostileMobs) and ($creatureType->getCreatureClass() !== Animal::class or $level->isDayTime())) {
					$a = $creatureType->getCreatureClass();
					$j4 = count(array_filter($level->getEntities(), function(Entity $entity) use ($a){
						return is_a($entity, $a);
					}));
					$k4 = $creatureType->getMaxSpawn() * count($eligibleChunks) / self::MAX_MOBS;

					if($j4 <= $k4){
						foreach($eligibleChunks as $chunkHash){
							Level::getXZ($chunkHash, $cx, $cz);
							if(count($level->getChunkEntities($cx, $cz)) === 0){

								$pos = self::getRandomChunkPosition($level, $cx, $cz);
								$block = $level->getBlock($pos);

								if(!$block->isSolid() and $level->getNearestEntity($pos, 24, Player::class) === null){
									$j2 = 0;

									for($k2 = 0; $k2 < 3; ++$k2){
										$l2 = $pos->x;
										$i3 = $pos->y;
										$j3 = $pos->z;
										$entry = null;
										$s1 = $level->random->nextBoundedInt(4);

										for($l3 = 0; $l3 < $s1; ++$l3){
											$l2 += $level->random->nextBoundedInt(6) - $level->random->nextBoundedInt(6);
											$i3 += $level->random->nextBoundedInt(1) - $level->random->nextBoundedInt(1);
											$j3 += $level->random->nextBoundedInt(6) - $level->random->nextBoundedInt(6);
											$pos1 = new Vector3($l2, $i3, $j3);

											if($pos1->distanceSquared($spawn) >= 576){
												if($entry === null){
													$entry = $level->getSpawnListEntryForTypeAt($creatureType, $pos1);

													if($entry === null){
														break;
													}
												}

												if($level->canCreatureTypeSpawnHere($creatureType, $entry, $pos1) and self::canCreatureTypeSpawnAtLocation($entry->entityClass::SPAWN_PLACEMENT_TYPE, $level, $pos1)){
													$entity = null;
													try{
														$class = $entry->entityClass;
														/** @var Living $entity */
														$entity = new $class($level, Entity::createBaseNBT($pos1->add(0.5, 0, 0.5)));
													}catch(\Exception $e){
														return;
													}

													if($entity instanceof Mob){
														$entity->setImmobile(false);
													}

													$entity->setRotation($level->random->nextFloat() * 360, 0);

													if($entity->canSpawnHere() and count($level->getCollidingEntities($entity->getBoundingBox(), $entity)) === 0){
														// TODO: implement mob initial spawn
														++$j2;
														$entity->spawnToAll();

														if($j2 >= $entity->getMaxSpawnedInChunk()){
															continue 4;
														}
													}else{
														$entity->flagForDespawn();
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param Level $level
	 * @param int   $x
	 * @param int   $z
	 *
	 * @return Vector3
	 */
	public static function getRandomChunkPosition(Level $level, int $x, int $z){
		$i = $x * 16 + $level->random->nextBoundedInt(16);
		$j = $z * 16 + $level->random->nextBoundedInt(16);
		$k = $level->getHeightMap($i, $j) + 1;
		if($k % 16 !== 0){
			$k = $k + 16 - ($k % 16);
		}
		$l = $level->random->nextBoundedInt($k > 0 ? $k : 256);
		return new Vector3($i, $l, $j);
	}

	/**
	 * @param int     $spawnPlacementType
	 * @param Level   $level
	 * @param Vector3 $pos
	 *
	 * @return bool
	 */
	public static function canCreatureTypeSpawnAtLocation(int $spawnPlacementType, Level $level, Vector3 $pos){
		$block = $level->getBlock($pos);

		if($spawnPlacementType === SpawnPlacementTypes::PLACEMENT_TYPE_IN_WATER){
			return $block instanceof Water and $level->getBlock($pos->down()) instanceof Water and !$level->getBlock($pos->up())->isSolid();
		}else{
			$block1 = $level->getBlock($pos->down());

			if(!$block1->isSolid()){
				return false;
			}else{
				$flag = $block1->getId() !== Block::BEDROCK;
				return $flag and !$block->isSolid() and !($block instanceof Liquid) and !$level->getBlock($pos->up())->isSolid();
			}
		}
	}

	/**
	 * Called during chunk generation to spawn initial creatures.
	 *
	 * @param Level  $level
	 * @param Biome  $biome
	 * @param int    $sourceX
	 * @param int    $sourceZ
	 * @param int    $xRange
	 * @param int    $zRange
	 * @param Random $random
	 */
	public static function performChunkGeneratorSpawning(Level $level, Biome $biome, int $sourceX, int $sourceZ, int $xRange, int $zRange, Random $random){
		$list = $biome->getSpawnableList(self::$creatureTypes[Animal::class]);

		if(!empty($list)){
			while($random->nextFloat() < $biome->getSpawningChance()){
				/** @var SpawnListEntry $entry */
				$entry = WeightedRandomItem::getRandomItem($random, $list, WeightedRandomItem::getTotalWeight($list) + 2);
				if($entry === null) continue;

				$i = $random->nextBoundedInt($entry->minGroupCount + $random->nextBoundedInt(1 + $entry->maxGroupCount - $entry->minGroupCount));
				$j = $sourceX + $random->nextBoundedInt($xRange);
				$k = $sourceZ + $random->nextBoundedInt($zRange);
				$l = $j;
				$i1 = $k;

				for($j1 = 0; $j1 < $i; ++$j1){
					$flag = false;

					for($k1 = 0; !$flag and $k1 < 4; ++$k1){
						$pos = new Vector3($j, $level->getHeightMap($j, $k) + 1, $k);

						for(; $pos->y > 0; $pos = $pos->down()){
							$down = $level->getBlock($pos->down());

							if(!($down instanceof Leaves) and $down->isSolid()){
								break;
							}
						}

						if(self::canCreatureTypeSpawnAtLocation(SpawnPlacementTypes::PLACEMENT_TYPE_ON_GROUND, $level, $pos)){
							$entity = null;

							try{
								$class = $entry->entityClass;
								/** @var Entity $entity */
								$entity = new $class($level, Entity::createBaseNBT($pos->add(0.5, 0, 0.5)));
							}catch(\Exception $e){
								continue;
							}

							if($entity instanceof Mob){
								$entity->setImmobile(false);
							}

							$entity->setRotation($random->nextFloat() * 360, 0);
							$entity->spawnToAll();
							// TODO: entity initial spawn
							$flag = true;
						}

						$j += $random->nextBoundedInt(5) - $random->nextBoundedInt(5);

						for($k += $random->nextBoundedInt(5) - $random->nextBoundedInt(5); $j < $sourceX or $j >= $sourceX + $xRange or $k < $sourceZ or $k >= $sourceZ + $xRange; $k = $i1 + $random->nextBoundedInt(5) - $random->nextBoundedInt(5)){
							$j = $l + $random->nextBoundedInt(5) - $random->nextBoundedInt(5);
						}
					}
				}
			}
		}
	}
}
