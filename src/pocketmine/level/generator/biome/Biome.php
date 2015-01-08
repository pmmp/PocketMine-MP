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

use pocketmine\level\ChunkManager;
use pocketmine\level\generator\normal\biome\BeachBiome;
use pocketmine\level\generator\normal\biome\DesertBiome;
use pocketmine\level\generator\normal\biome\ForestBiome;
use pocketmine\level\generator\normal\biome\MountainsBiome;
use pocketmine\level\generator\normal\biome\OceanBiome;
use pocketmine\level\generator\normal\biome\PlainBiome;
use pocketmine\level\generator\normal\biome\RiverBiome;
use pocketmine\level\generator\normal\biome\SmallMountainsBiome;
use pocketmine\level\generator\populator\Populator;
use pocketmine\utils\Random;

abstract class Biome{

	const OCEAN = 0;
	const PLAINS = 1;
	const DESERT = 2;
	const MOUNTAINS = 3;
	const FOREST = 4;

	const RIVER = 7;

	const BEACH = 16;

	const SMALL_MOUNTAINS = 20;


	const MAX_BIOMES = 256;

	/** @var Biome[] */
	private static $biomes = [];
	private static $setup = false;

	private $id;
	private $registered = false;
	/** @var Populator[] */
	private $populators = [];

	protected static function register($id, Biome $biome){
		self::$biomes[(int) $id] = $biome;
		$biome->setId((int) $id);
	}

	public static function init(){
		if(self::$setup === false){
			self::$setup = true;
			self::register(self::OCEAN, new OceanBiome());
			self::register(self::PLAINS, new PlainBiome());
			self::register(self::DESERT, new DesertBiome());
			self::register(self::MOUNTAINS, new MountainsBiome());
			self::register(self::FOREST, new ForestBiome());

			self::register(self::RIVER, new RiverBiome());

			self::register(self::BEACH, new BeachBiome());

			self::register(self::SMALL_MOUNTAINS, new SmallMountainsBiome());
		}
	}

	/**
	 * @param $id
	 *
	 * @return Biome
	 */
	public static function getBiome($id){
		return isset(self::$biomes[$id]) ? self::$biomes[$id] : null;
	}

	public function clearPopulators(){
		$this->populators = [];
	}

	public function addPopulator(Populator $populator){
		$this->populators[] = $populator;
	}

	public function populateChunk(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		foreach($this->populators as $populator){
			$populator->populate($level, $chunkX, $chunkZ, $random);
		}
	}

	public function getPopulators(){
		return $this->populators;
	}

	public function setId($id){
		if(!$this->registered){
			$this->registered = true;
			$this->id = $id;
		}
	}

	public function getId(){
		return $this->id;
	}

	public abstract function getName();
}