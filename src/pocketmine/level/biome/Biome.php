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

namespace pocketmine\level\biome;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\populator\Populator;
use pocketmine\utils\Random;

abstract class Biome{

	public const OCEAN = 0;
	public const PLAINS = 1;
	public const DESERT = 2;
	public const MOUNTAINS = 3;
	public const FOREST = 4;
	public const TAIGA = 5;
	public const SWAMP = 6;
	public const RIVER = 7;

	public const HELL = 8;

	public const ICE_PLAINS = 12;


	public const SMALL_MOUNTAINS = 20;


	public const BIRCH_FOREST = 27;


	public const MAX_BIOMES = 256;

	/** @var Biome[]|\SplFixedArray */
	private static $biomes;

	/** @var int */
	private $id;
	/** @var bool */
	private $registered = false;

	/** @var Populator[] */
	private $populators = [];

	/** @var int */
	private $minElevation;
	/** @var int */
	private $maxElevation;

	/** @var Block[] */
	private $groundCover = [];

	/** @var float */
	protected $rainfall = 0.5;
	/** @var float */
	protected $temperature = 0.5;

	protected static function register(int $id, Biome $biome){
		self::$biomes[$id] = $biome;
		$biome->setId($id);
	}

	public static function init(){
		self::$biomes = new \SplFixedArray(self::MAX_BIOMES);

		self::register(self::OCEAN, new OceanBiome());
		self::register(self::PLAINS, new PlainBiome());
		self::register(self::DESERT, new DesertBiome());
		self::register(self::MOUNTAINS, new MountainsBiome());
		self::register(self::FOREST, new ForestBiome());
		self::register(self::TAIGA, new TaigaBiome());
		self::register(self::SWAMP, new SwampBiome());
		self::register(self::RIVER, new RiverBiome());

		self::register(self::ICE_PLAINS, new IcePlainsBiome());


		self::register(self::SMALL_MOUNTAINS, new SmallMountainsBiome());

		self::register(self::BIRCH_FOREST, new ForestBiome(ForestBiome::TYPE_BIRCH));
	}

	/**
	 * @param int $id
	 *
	 * @return Biome
	 */
	public static function getBiome(int $id) : Biome{
		if(self::$biomes[$id] === null){
			self::register($id, new UnknownBiome());
		}
		return self::$biomes[$id];
	}

	public function clearPopulators(){
		$this->populators = [];
	}

	public function addPopulator(Populator $populator){
		$this->populators[] = $populator;
	}

	/**
	 * @param ChunkManager $level
	 * @param int          $chunkX
	 * @param int          $chunkZ
	 * @param Random       $random
	 */
	public function populateChunk(ChunkManager $level, int $chunkX, int $chunkZ, Random $random){
		foreach($this->populators as $populator){
			$populator->populate($level, $chunkX, $chunkZ, $random);
		}
	}

	/**
	 * @return Populator[]
	 */
	public function getPopulators() : array{
		return $this->populators;
	}

	public function setId(int $id){
		if(!$this->registered){
			$this->registered = true;
			$this->id = $id;
		}
	}

	public function getId() : int{
		return $this->id;
	}

	abstract public function getName() : string;

	public function getMinElevation() : int{
		return $this->minElevation;
	}

	public function getMaxElevation() : int{
		return $this->maxElevation;
	}

	public function setElevation(int $min, int $max){
		$this->minElevation = $min;
		$this->maxElevation = $max;
	}

	/**
	 * @return Block[]
	 */
	public function getGroundCover() : array{
		return $this->groundCover;
	}

	/**
	 * @param Block[] $covers
	 */
	public function setGroundCover(array $covers){
		$this->groundCover = $covers;
	}

	public function getTemperature() : float{
		return $this->temperature;
	}

	public function getRainfall() : float{
		return $this->rainfall;
	}
}
