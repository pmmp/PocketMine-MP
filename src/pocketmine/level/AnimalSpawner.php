<?php

namespace pocketmine\level;

use pocketmine\block\Block;
use pocketmine\entity\Animal;
use pocketmine\entity\Creature;
use pocketmine\entity\CreatureType;
use pocketmine\entity\Entity;
use pocketmine\entity\Monster;
use pocketmine\entity\WaterAnimal;
use pocketmine\math\Vector3;

class AnimalSpawner{

	public const MAX_MOBS = 289;

	/** @var int[][] */
	protected $eligibleChunkCoordinates = [];
	/** @var CreatureType[] */
	public static $creatureTypes = [];

	public function __construct(){
		self::$creatureTypes[] = new CreatureType(Monster::class, 70, Block::AIR, false);
		self::$creatureTypes[] = new CreatureType(Animal::class, 10, Block::AIR, true);
		self::$creatureTypes[] = new CreatureType(Creature::class, 15, Block::AIR, false);
		self::$creatureTypes[] = new CreatureType(WaterAnimal::class, 5, Block::STILL_WATER, false);
	}


	public function findChunksForSpawning(Level $level, bool $spawnHostileMobs, bool $spawnPeacefulMobs, bool $isDayTime){
		/*if(!$spawnHostileMobs and !$spawnPeacefulMobs){
			return 0;
		}else{
			$this->eligibleChunkCoordinates = [];
			$i = 0;

			foreach($level->getPlayers() as $player){
				if(!$player->isSpectator()){
					$j = (int) floor($player->x / 16.0);
					$k = (int) floor($player->z / 16.0);
					$l = 8;

					for($i1 = -$l; $i1 <= $l; ++$i1){
						for($j1 = -$l; $j1 <= $l; ++$j1){
							$flag = $i1 == -$l or $i1 == $l or $j1 == -$l or $j1 == $l;
							$hash = Level::blockHash($cX = $i1 + $j, 0, $cZ = $j1 + $k);

							if(!isset($this->eligibleChunkCoordinates[$hash])){
								++$i;

								if(!$flag and $level->isChunkLoaded($j, $k)){
									$this->eligibleChunkCoordinates[$hash] = [
										$cX,
										$cZ
									];
								}
							}
						}
					}
				}
			}

			$i4 = 0;
			$spawn = $level->getSpawnLocation();

			foreach(self::$creatureTypes as $creatureType)
            {
	            if((!$creatureType->isPeacefulCreature() or $spawnPeacefulMobs) and ($creatureType->isPeacefulCreature() or $spawnHostileMobs) and (!$creatureType->getCreatureClass() === Animal::class or $isDayTime)){
		            $a = $creatureType->getCreatureClass();
	            	$j4 = count(array_filter($level->getEntities(), function(Entity $entity) use ($a){
	            		return get_class($entity) == $a;
		            }));
		            $k4 = $creatureType->getMaxSpawn() * $i / self::MAX_MOBS;

		            if($j4 <= $k4){
		            	switch(1){
			            case 1:

			            foreach($this->eligibleChunkCoordinates as $coords)
                        {
	                        BlockPos blockpos = getRandomChunkPosition($level, chunkcoordintpair1 . chunkXPos, chunkcoordintpair1 . chunkZPos);
                            $k1 = blockpos . getX();
                            $l1 = blockpos . getY();
                            $i2 = blockpos . getZ();
                            Block block = $level . getBlockState(blockpos) . getBlock();

                            if(!block . isNormalCube()){
			            $j2 = 0;

			            for($k2 = 0;
			            k2 < 3;
			            ++k2){
			            $l2 = k1;
			            $i3 = l1;
			            $j3 = i2;
			            $k3 = 6;
			            BiomeGenBase . SpawnListEntry biomegenbase$spawnlistentry = null;
			            IEntityLivingData ientitylivingdata = null;

			            for($l3 = 0;
			            l3 < 4;
			            ++l3){
			            l2 += $level . rand . nextInt(k3) - $level . rand . nextInt(k3);
			            i3 += $level . rand . nextInt(1) - $level . rand . nextInt(1);
			            j3 += $level . rand . nextInt(k3) - $level . rand . nextInt(k3);
			            BlockPos blockpos1 = new BlockPos(l2, i3, j3);
			            float f = (float) l2 + 0.5F;
			            float f1 = (float) j3 + 0.5F;

			            if(!$level . isAnyPlayerWithinRangeAt((double) f, (double) i3, (double) f1, 24.0D) and blockpos2 . distanceSq((double) f, (double) i3, (double) f1) >= 576.0D){
			            if(biomegenbase$spawnlistentry == null){
			            biomegenbase$spawnlistentry = $level . getSpawnListEntryForTypeAt(enumcreaturetype, blockpos1);

			            if(biomegenbase$spawnlistentry == null){
			            break;
			            }
		            }

			            if($level . canCreatureTypeSpawnHere(enumcreaturetype, biomegenbase$spawnlistentry, blockpos1) and canCreatureTypeSpawnAtLocation(EntitySpawnPlacementRegistry . getPlacementForEntity(biomegenbase$spawnlistentry . entityClass), $level, blockpos1))
                                            {
	                                            EntityLiving entityliving;

                                                try{
	                                                entityliving = (EntityLiving)biomegenbase$spawnlistentry . entityClass . getConstructor(new Class[]{World .class}).newInstance(new Object[] {$level});
                                                }catch(Exception exception)
                                                {
	                                                exception . printStackTrace();
	                                                return i4;
                                                }

                                                entityliving . setLocationAndAngles((double) f, (double) i3, (double) f1, $level . rand . nextFloat() * 360.0F, 0.0F);

                                                if(entityliving . getCanSpawnHere() and entityliving . isNotColliding()){
	                                                ientitylivingdata = entityliving . onInitialSpawn($level . getDifficultyForLocation(new BlockPos(entityliving)), ientitylivingdata);

	                                                if(entityliving . isNotColliding()){
		                                                ++j2;
		                                                $level . spawnEntityInWorld(entityliving);
	                                                }

	                                                if(j2 >= entityliving . getMaxSpawnedInChunk()){
		                                                continue label374;
	                                                }
                                                }

                                                i4 += j2;
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

            return i4;
        }*/
	}

	public static function getRandomChunkPosition(Level $level, int $x, int $z){
		$i = $x * 16 + $level->random->nextBoundedInt(16);
		$j = $z * 16 + $level->random->nextBoundedInt(16);
		$k = $level->getHighestBlockAt($i, $j);
		$l = $level->random->nextBoundedInt($k > 0 ? $k : 256);
		return new Vector3($i, $l, $j);
	}

	/*public
	static canCreatureTypeSpawnAtLocation(EntityLiving . SpawnPlacementType p_180267_0_, World worldIn, BlockPos pos)
		{
			if(!worldIn . getWorldBorder() . contains(pos)){
				return false;
			}else{
				Block block = worldIn . getBlockState(pos) . getBlock();

				if(p_180267_0_ == EntityLiving . SpawnPlacementType . IN_WATER){
					return block . getMaterial() . isLiquid() and worldIn . getBlockState(pos . down()) . getBlock() . getMaterial() . isLiquid() and !worldIn . getBlockState(pos . up()) . getBlock() . isNormalCube();
				}else{
					BlockPos blockpos = pos . down();

					if(!World . doesBlockHaveSolidTopSurface(worldIn, blockpos)){
						return false;
					}else{
						Block block1 = worldIn . getBlockState(blockpos) . getBlock();
						bool flag = block1 != Blocks . bedrock and block1 != Blocks . barrier;
						return flag and !block . isNormalCube() and !block . getMaterial() . isLiquid() and !worldIn . getBlockState(pos . up()) . getBlock() . isNormalCube();
					}
				}
			}
		}*/

	/**
	 * Called during chunk generation to spawn initial creatures.
	 */
	/*public static void performWorldGenSpawning(World worldIn, BiomeGenBase p_77191_1_, $p_77191_2_, $p_77191_3_, $p_77191_4_, $p_77191_5_, Random p_77191_6_)
	{
		List<BiomeGenBase . SpawnListEntry > list = p_77191_1_ . getSpawnableList(EnumCreatureType . CREATURE);

		if(!list.isEmpty()){
			while(p_77191_6_ . nextFloat() < p_77191_1_ . getSpawningChance()){
				BiomeGenBase . SpawnListEntry biomegenbase$spawnlistentry = (BiomeGenBase . SpawnListEntry)WeightedRandom . getRandomItem(worldIn . rand, list);
				$i = biomegenbase$spawnlistentry . minGroupCount + p_77191_6_ . nextInt(1 + biomegenbase$spawnlistentry . maxGroupCount - biomegenbase$spawnlistentry . minGroupCount);
				IEntityLivingData ientitylivingdata = null;
				$j = p_77191_2_ + p_77191_6_ . nextInt(p_77191_4_);
				$k = p_77191_3_ + p_77191_6_ . nextInt(p_77191_5_);
				$l = j;
				$i1 = k;

				for($j1 = 0; j1 < i; ++j1)
				{
					bool flag = false;

					for($k1 = 0; !flag and k1 < 4; ++k1)
					{
						BlockPos blockpos = worldIn . getTopSolidOrLiquidBlock(new BlockPos(j, 0, k));

						if(canCreatureTypeSpawnAtLocation(EntityLiving . SpawnPlacementType . ON_GROUND, worldIn, blockpos)){
							EntityLiving entityliving;

							try{
								entityliving = (EntityLiving)biomegenbase$spawnlistentry . entityClass . getConstructor(new Class[]{World .class}).newInstance(new Object[] {worldIn});
							}catch(Exception exception)
							{
								exception . printStackTrace();
								continue;
							}

							entityliving . setLocationAndAngles((double) ((float) j + 0.5F), (double) blockpos . getY(), (double) ((float) k + 0.5F), p_77191_6_ . nextFloat() * 360.0F, 0.0F);
							worldIn . spawnEntityInWorld(entityliving);
							ientitylivingdata = entityliving . onInitialSpawn(worldIn . getDifficultyForLocation(new BlockPos(entityliving)), ientitylivingdata);
							flag = true;
						}

						j += p_77191_6_ . nextInt(5) - p_77191_6_ . nextInt(5);

						for(k += p_77191_6_ . nextInt(5) - p_77191_6_ . nextInt(5); j < p_77191_2_ or j >= p_77191_2_ + p_77191_4_ or k < p_77191_3_ or k >= p_77191_3_ + p_77191_4_; k = i1 + p_77191_6_ . nextInt(5) - p_77191_6_ . nextInt(5)){
							j = l + p_77191_6_ . nextInt(5) - p_77191_6_ . nextInt(5);
						}
					}
				}
			}
		}
	}*/
}