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

namespace pocketmine\level\generator\biome;

use pocketmine\level\generator\noise\Simplex;
use pocketmine\utils\Random;

class BiomeSelector{

	/** @var Biome */
	private $fallback;

	/** @var Simplex */
	private $temperature;
	/** @var Simplex */
	private $rainfall;

	/** @var Biome[] */
	private $biomes = [];

	private $select = [];

	public function __construct(Random $random, Biome $fallback){
		$this->fallback = $fallback;
		$this->temperature = new Simplex($random, 1, 0.004, 0.5, 2);
		$this->rainfall = new Simplex($random, 2, 0.004, 0.5, 2);
	}

	public function addBiome(Biome $biome, $start, $end){
		$this->biomes[$biome->getId()] = $biome;
		$this->select[$biome->getId()] = [$biome->getId(), $start, $end];
	}

	/**
	 * @param $x
	 * @param $z
	 *
	 * @return Biome
	 */
	public function pickBiome($x, $z){

		//$temperature = $this->temperature->noise2D($x, $z);
		$rainfall = $this->rainfall->noise2D($x, $z);

		if($rainfall > 0.9){
			return Biome::getBiome(Biome::OCEAN);
		}elseif($rainfall > 0.7){
			return Biome::getBiome(Biome::RIVER);
		}elseif($rainfall > 0.6){
			return Biome::getBiome(Biome::BEACH);
		}elseif($rainfall > 0.2){
			return Biome::getBiome(Biome::FOREST);
		}elseif($rainfall > -0.3){
			return Biome::getBiome(Biome::PLAINS);
		}elseif($rainfall > -0.6){
			return Biome::getBiome(Biome::DESERT);
		}elseif($rainfall > -0.7){
			return Biome::getBiome(Biome::BEACH);
		}else{
			return Biome::getBiome(Biome::OCEAN);
		}

	}
}