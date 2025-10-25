<?php

declare(strict_types=1);

namespace pocketmine\world\locate;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;

class StructureFinder{
	public function __construct(private World $world){
	}

	/**
	 * Basit yaklaşım: aranan yapı formlarını temsil eden blok dizilerini veya belirteçleri yüklenmiş chunklarda arar.
	 * Bu MVP, yalnızca bazı basit yapı anahtar kelimeleri için yakınlaşık arama yapar (village, temple vb).
	 */
	public function findStructureByName(string $name, Vector3 $start, int $radiusChunks = 16) : ?Vector3{
		$name = strtolower($name);

		$aliases = [
			'village' => ['village'],
			'desert_temple' => ['desert_pyramid', 'desert_temple'],
			'jungle_temple' => ['jungle_temple', 'jungle_pyramid'],
			'ocean_monument' => ['ocean_monument', 'monument'],
			'shipwreck' => ['shipwreck'],
			'mineshaft' => ['mineshaft', 'abandoned_mineshaft'],
		];

		$normalized = null;
		foreach($aliases as $key => $values){
			if(in_array($name, $values, true) || $name === $key){
				$normalized = $key;
				break;
			}
		}
		if($normalized === null){
			return null;
		}

		$startChunkX = (int) floor($start->x / 16);
		$startChunkZ = (int) floor($start->z / 16);

		for($r = 0; $r <= $radiusChunks; ++$r){
			for($dx = -$r; $dx <= $r; ++$dx){
				for($dz = -$r; $dz <= $r; ++$dz){
					$cx = $startChunkX + $dx;
					$cz = $startChunkZ + $dz;
					if(($pos = $this->scanChunkForStructure($cx, $cz, $normalized)) !== null){
						return $pos;
					}
				}
			}
		}

		return null;
	}

	private function scanChunkForStructure(int $chunkX, int $chunkZ, string $structure) : ?Vector3{
		$chunk = $this->world->getChunk($chunkX, $chunkZ);
		if($chunk === null || !$chunk->isPopulated()){
			return null;
		}

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$wx = $chunkX * 16 + $x;
				$wz = $chunkZ * 16 + $z;
				$y = $this->world->getHighestBlockAt($wx, $wz) ?? 64;

				switch($structure){
					case 'village':
						if($this->containsBlockNearby($wx, $y, $wz, [VanillaBlocks::OAK_DOOR()->getStateId(), VanillaBlocks::SPRUCE_DOOR()->getStateId(), VanillaBlocks::OAK_FENCE()->getStateId()])){
							return new Vector3($wx, $y, $wz);
						}
						break;
					case 'desert_temple':
						if($this->checkBlockPattern($wx, $y, $wz, ['sand', 'sandstone'])){
							return new Vector3($wx, $y, $wz);
						}
						break;
					case 'jungle_temple':
						if($this->containsBlockNearby($wx, $y, $wz, [VanillaBlocks::MOSSY_COBBLESTONE()->getStateId()])){
							return new Vector3($wx, $y, $wz);
						}
						break;
					case 'ocean_monument':
						$biome = $this->world->getBiome($wx, $y, $wz);
						if(stripos($biome->getName(), 'ocean') !== false && $this->containsBlockNearby($wx, $y, $wz, [VanillaBlocks::PRISMARINE()->getStateId(), VanillaBlocks::PRISMARINE_BRICKS()->getStateId()])){
							return new Vector3($wx, $y, $wz);
						}
						break;
					case 'shipwreck':
						$biome = $this->world->getBiome($wx, $y, $wz);
						if((stripos($biome->getName(), 'ocean') !== false || stripos($biome->getName(), 'beach') !== false) && $this->containsBlockNearby($wx, $y, $wz, [VanillaBlocks::OAK_PLANKS()->getStateId(), VanillaBlocks::CHEST()->getStateId()])){
							return new Vector3($wx, $y, $wz);
						}
						break;
					case 'mineshaft':
						if($this->containsBlockNearby($wx, $y, $wz, [VanillaBlocks::RAIL()->getStateId(), VanillaBlocks::COBWEB()->getStateId()])){
							return new Vector3($wx, $y, $wz);
						}
						break;
				}
			}
		}

		return null;
	}

	private function containsBlockNearby(int $x, int $y, int $z, array $stateIds, int $radius = 4) : bool{
		for($dx = -$radius; $dx <= $radius; ++$dx){
			for($dz = -$radius; $dz <= $radius; ++$dz){
				for($dy = -2; $dy <= 2; ++$dy){
					$bx = $x + $dx;
					$by = max($this->world->getMinY(), $y + $dy);
					$bz = $z + $dz;
					try{
						$b = $this->world->getBlockAt($bx, $by, $bz);
						if(in_array($b->getStateId(), $stateIds, true)){
							return true;
						}
					}catch(\Throwable $e){
						// dünyanın sınırları dışında olabilir
					}
				}
			}
		}
		return false;
	}

	private function checkBlockPattern(int $x, int $y, int $z, array $names, int $radius = 4) : bool{
		for($dx = -$radius; $dx <= $radius; ++$dx){
			for($dz = -$radius; $dz <= $radius; ++$dz){
				$bx = $x + $dx;
				$bz = $z + $dz;
				$by = $this->world->getHighestBlockAt($bx, $bz) ?? $y;
				$biome = $this->world->getBiome($bx, $by, $bz);
				foreach($names as $n){
					if(stripos($biome->getName(), $n) !== false){
						return true;
					}
				}
			}
		}
		return false;
	}
}
