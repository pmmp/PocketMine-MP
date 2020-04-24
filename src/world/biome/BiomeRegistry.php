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

namespace pocketmine\world\biome;

use pocketmine\block\utils\TreeType;
use pocketmine\utils\SingletonTrait;

final class BiomeRegistry{
	use SingletonTrait;

	/**
	 * @var Biome[]|\SplFixedArray
	 * @phpstan-var \SplFixedArray<Biome>
	 */
	private $biomes;

	public function __construct(){
		$this->biomes = new \SplFixedArray(Biome::MAX_BIOMES);

		$this->register(Biome::OCEAN, new OceanBiome());
		$this->register(Biome::PLAINS, new PlainBiome());
		$this->register(Biome::DESERT, new DesertBiome());
		$this->register(Biome::MOUNTAINS, new MountainsBiome());
		$this->register(Biome::FOREST, new ForestBiome());
		$this->register(Biome::TAIGA, new TaigaBiome());
		$this->register(Biome::SWAMP, new SwampBiome());
		$this->register(Biome::RIVER, new RiverBiome());

		$this->register(Biome::ICE_PLAINS, new IcePlainsBiome());

		$this->register(Biome::SMALL_MOUNTAINS, new SmallMountainsBiome());

		$this->register(Biome::BIRCH_FOREST, new ForestBiome(TreeType::BIRCH()));
	}

	public function register(int $id, Biome $biome) : void{
		$this->biomes[$id] = $biome;
		$biome->setId($id);
	}

	public function getBiome(int $id) : Biome{
		if($this->biomes[$id] === null){
			$this->register($id, new UnknownBiome());
		}

		return $this->biomes[$id];
	}
}