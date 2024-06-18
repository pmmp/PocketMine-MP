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

namespace pocketmine\world\format\io\data;

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\io\WorldData;
use function file_exists;

abstract class BaseNbtWorldData implements WorldData{
	protected const TAG_LEVEL_NAME = "LevelName";
	protected const TAG_GENERATOR_NAME = "generatorName";
	protected const TAG_GENERATOR_OPTIONS = "generatorOptions";
	protected const TAG_RANDOM_SEED = "RandomSeed";
	protected const TAG_TIME = "Time";
	protected const TAG_SPAWN_X = "SpawnX";
	protected const TAG_SPAWN_Y = "SpawnY";
	protected const TAG_SPAWN_Z = "SpawnZ";

	protected CompoundTag $compoundTag;

	/**
	 * @throws CorruptedWorldException
	 * @throws UnsupportedWorldFormatException
	 */
	public function __construct(
		protected string $dataPath
	){
		if(!file_exists($this->dataPath)){
			throw new CorruptedWorldException("World data not found at $dataPath");
		}

		try{
			$this->compoundTag = $this->load();
		}catch(CorruptedWorldException $e){
			throw new CorruptedWorldException("Corrupted world data: " . $e->getMessage(), 0, $e);
		}
		$this->fix();
	}

	/**
	 * @throws CorruptedWorldException
	 * @throws UnsupportedWorldFormatException
	 */
	abstract protected function load() : CompoundTag;

	/**
	 * @throws CorruptedWorldException
	 * @throws UnsupportedWorldFormatException
	 */
	abstract protected function fix() : void;

	/**
	 * Hack to fix worlds broken previously by older versions of PocketMine-MP which incorrectly saved classpaths of
	 * generators into level.dat on imported (not generated) worlds.
	 *
	 * This should only have affected leveldb worlds as far as I know, because PC format worlds include the
	 * generatorName tag by default. However, MCPE leveldb ones didn't, and so they would get filled in with something
	 * broken.
	 *
	 * This bug took a long time to get found because previously the generator manager would just return the default
	 * generator silently on failure to identify the correct generator, which caused lots of unexpected bugs.
	 *
	 * Only classnames which were written into the level.dat from "fixing" the level data are included here. These are
	 * hardcoded to avoid problems fixing broken worlds in the future if these classes get moved, renamed or removed.
	 *
	 * @param string $className Classname saved in level.dat
	 *
	 * @return null|string Name of the correct generator to replace the broken value
	 */
	protected static function hackyFixForGeneratorClasspathInLevelDat(string $className) : ?string{
		//THESE ARE DELIBERATELY HARDCODED, DO NOT CHANGE!
		switch($className){
			/** @noinspection ClassConstantCanBeUsedInspection */
			case 'pocketmine\level\generator\normal\Normal':
				return "normal";
			/** @noinspection ClassConstantCanBeUsedInspection */
			case 'pocketmine\level\generator\Flat':
				return "flat";
		}

		return null;
	}

	public function getCompoundTag() : CompoundTag{
		return $this->compoundTag;
	}

	/* The below are common between PC and PE */

	public function getName() : string{
		return $this->compoundTag->getString(self::TAG_LEVEL_NAME);
	}

	public function setName(string $value) : void{
		$this->compoundTag->setString(self::TAG_LEVEL_NAME, $value);
	}

	public function getGenerator() : string{
		return $this->compoundTag->getString(self::TAG_GENERATOR_NAME, "DEFAULT");
	}

	public function getGeneratorOptions() : string{
		return $this->compoundTag->getString(self::TAG_GENERATOR_OPTIONS, "");
	}

	public function getSeed() : int{
		return $this->compoundTag->getLong(self::TAG_RANDOM_SEED);
	}

	public function getTime() : int{
		if(($timeTag = $this->compoundTag->getTag(self::TAG_TIME)) instanceof IntTag){ //some older PM worlds had this in the wrong format
			return $timeTag->getValue();
		}
		return $this->compoundTag->getLong(self::TAG_TIME, 0);
	}

	public function setTime(int $value) : void{
		$this->compoundTag->setLong(self::TAG_TIME, $value);
	}

	public function getSpawn() : Vector3{
		return new Vector3($this->compoundTag->getInt(self::TAG_SPAWN_X), $this->compoundTag->getInt(self::TAG_SPAWN_Y), $this->compoundTag->getInt(self::TAG_SPAWN_Z));
	}

	public function setSpawn(Vector3 $pos) : void{
		$this->compoundTag->setInt(self::TAG_SPAWN_X, $pos->getFloorX());
		$this->compoundTag->setInt(self::TAG_SPAWN_Y, $pos->getFloorY());
		$this->compoundTag->setInt(self::TAG_SPAWN_Z, $pos->getFloorZ());
	}

}
