<?php

declare(strict_types=1);

namespace pocketmine\world\locate;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;

class BiomeFinder{
	public function __construct(private World $world){
	}

	/**
	 * Basit bir arama: oyuncunun bulunduğu chunk ve çevresindeki N chunk içinde biyom arar.
	 * Bu MVP, tüm dünya taraması yerine yüklü chunk'ları kontrol eder ve bulunmazsa null döner.
	 */
	public function findBiomeByName(string $name, Vector3 $start, int $radiusChunks = 16) : ?Vector3{
		$name = strtolower($name);
		$startChunkX = (int) floor($start->x / 16);
		$startChunkZ = (int) floor($start->z / 16);

		for($r = 0; $r <= $radiusChunks; ++$r){
			for($dx = -$r; $dx <= $r; ++$dx){
				$dz = $r;
				if(($pos = $this->scanChunk($startChunkX + $dx, $startChunkZ + $dz, $name, $start)) !== null){
					return $pos;
				}
				$dz = -$r;
				if(($pos = $this->scanChunk($startChunkX + $dx, $startChunkZ + $dz, $name, $start)) !== null){
					return $pos;
				}
			}
		}

		return null;
	}

	private function scanChunk(int $chunkX, int $chunkZ, string $name, Vector3 $origin) : ?Vector3{
		$chunk = $this->world->getChunk($chunkX, $chunkZ);
		if($chunk === null || !$chunk->isPopulated()){
			return null;
		}

		// hız için y ve x,z offsetlarını tararız
		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$wx = $chunkX * 16 + $x;
				$wz = $chunkZ * 16 + $z;
				$biome = $this->world->getBiome($wx, 64, $wz);
				if(strtolower($biome->getName()) === $name){
					return new Vector3($wx, 64, $wz);
				}
			}
		}

		return null;
	}
}
