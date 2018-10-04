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

namespace pocketmine\level\format\io\data;

use pocketmine\level\format\io\LevelData;
use pocketmine\level\LevelException;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;

abstract class BaseNbtLevelData implements LevelData{

	/** @var string */
	protected $dataPath;

	/** @var CompoundTag */
	protected $compoundTag;

	public function __construct(string $dataPath){
		$this->dataPath = $dataPath;

		if(!file_exists($this->dataPath)){
			throw new LevelException("Level data not found at $dataPath");
		}

		$this->compoundTag = $this->load();
		if($this->compoundTag === null){
			throw new LevelException("Invalid level data");
		}
	}

	/**
	 * @return CompoundTag
	 */
	abstract protected function load() : ?CompoundTag;

	abstract protected function fix() : void;

	public function getCompoundTag() : CompoundTag{
		return $this->compoundTag;
	}


	/* The below are common between PC and PE */

	public function getName() : string{
		return $this->compoundTag->getString("LevelName");
	}

	public function getGenerator() : string{
		return $this->compoundTag->getString("generatorName", "DEFAULT");
	}

	public function getGeneratorOptions() : array{
		return ["preset" => $this->compoundTag->getString("generatorOptions", "")];
	}

	public function getSeed() : int{
		return $this->compoundTag->getLong("RandomSeed");
	}

	public function getTime() : int{
		return $this->compoundTag->getLong("Time", 0, true);
	}

	public function setTime(int $value) : void{
		$this->compoundTag->setLong("Time", $value, true); //some older PM worlds had this in the wrong format
	}

	public function getSpawn() : Vector3{
		return new Vector3($this->compoundTag->getInt("SpawnX"), $this->compoundTag->getInt("SpawnY"), $this->compoundTag->getInt("SpawnZ"));
	}

	public function setSpawn(Vector3 $pos) : void{
		$this->compoundTag->setInt("SpawnX", $pos->getFloorX());
		$this->compoundTag->setInt("SpawnY", $pos->getFloorY());
		$this->compoundTag->setInt("SpawnZ", $pos->getFloorZ());
	}

}
