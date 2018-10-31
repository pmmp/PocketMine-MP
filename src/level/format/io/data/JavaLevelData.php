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

use pocketmine\level\generator\GeneratorManager;
use pocketmine\level\Level;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;

class JavaLevelData extends BaseNbtLevelData{

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = [], int $version = 19133) : void{
		//TODO, add extra details
		$levelData = new CompoundTag("Data", [
			new ByteTag("hardcore", ($options["hardcore"] ?? false) === true ? 1 : 0),
			new ByteTag("Difficulty", Level::getDifficultyFromString((string) ($options["difficulty"] ?? "normal"))),
			new ByteTag("initialized", 1),
			new IntTag("GameType", 0),
			new IntTag("generatorVersion", 1), //2 in MCPE
			new IntTag("SpawnX", 256),
			new IntTag("SpawnY", 70),
			new IntTag("SpawnZ", 256),
			new IntTag("version", $version),
			new IntTag("DayTime", 0),
			new LongTag("LastPlayed", (int) (microtime(true) * 1000)),
			new LongTag("RandomSeed", $seed),
			new LongTag("SizeOnDisk", 0),
			new LongTag("Time", 0),
			new StringTag("generatorName", GeneratorManager::getGeneratorName($generator)),
			new StringTag("generatorOptions", $options["preset"] ?? ""),
			new StringTag("LevelName", $name),
			new CompoundTag("GameRules", [])
		]);
		$nbt = new BigEndianNBTStream();
		$buffer = $nbt->writeCompressed(new CompoundTag("", [
			$levelData
		]));
		file_put_contents($path . "level.dat", $buffer);
	}

	protected function load() : ?CompoundTag{
		$nbt = new BigEndianNBTStream();
		$levelData = $nbt->readCompressed(file_get_contents($this->dataPath));
		if($levelData->hasTag("Data", CompoundTag::class)){
			return $levelData->getCompoundTag("Data");
		}
		return null;
	}

	protected function fix() : void{
		if(!$this->compoundTag->hasTag("generatorName", StringTag::class)){
			$this->compoundTag->setString("generatorName", "default", true);
		}elseif(($generatorName = self::hackyFixForGeneratorClasspathInLevelDat($this->compoundTag->getString("generatorName"))) !== null){
			$this->compoundTag->setString("generatorName", $generatorName);
		}

		if(!$this->compoundTag->hasTag("generatorOptions", StringTag::class)){
			$this->compoundTag->setString("generatorOptions", "");
		}
	}

	public function save() : void{
		$nbt = new BigEndianNBTStream();
		$this->compoundTag->setName("Data");
		$buffer = $nbt->writeCompressed(new CompoundTag("", [
			$this->compoundTag
		]));
		file_put_contents($this->dataPath, $buffer);
	}


	public function getDifficulty() : int{
		return $this->compoundTag->getByte("Difficulty", Level::DIFFICULTY_NORMAL);
	}

	public function setDifficulty(int $difficulty) : void{
		$this->compoundTag->setByte("Difficulty", $difficulty);
	}

	public function getRainTime() : int{
		return $this->compoundTag->getInt("rainTime", 0);
	}

	public function setRainTime(int $ticks) : void{
		$this->compoundTag->setInt("rainTime", $ticks);
	}

	public function getRainLevel() : float{
		if($this->compoundTag->hasTag("rainLevel", FloatTag::class)){ //PocketMine/MCPE
			return $this->compoundTag->getFloat("rainLevel");
		}

		return (float) $this->compoundTag->getByte("raining", 0); //PC vanilla
	}

	public function setRainLevel(float $level) : void{
		$this->compoundTag->setFloat("rainLevel", $level); //PocketMine/MCPE
		$this->compoundTag->setByte("raining", (int) ceil($level)); //PC vanilla
	}

	public function getLightningTime() : int{
		return $this->compoundTag->getInt("thunderTime", 0);
	}

	public function setLightningTime(int $ticks) : void{
		$this->compoundTag->setInt("thunderTime", $ticks);
	}

	public function getLightningLevel() : float{
		if($this->compoundTag->hasTag("lightningLevel", FloatTag::class)){ //PocketMine/MCPE
			return $this->compoundTag->getFloat("lightningLevel");
		}

		return (float) $this->compoundTag->getByte("thundering", 0); //PC vanilla
	}

	public function setLightningLevel(float $level) : void{
		$this->compoundTag->setFloat("lightningLevel", $level); //PocketMine/MCPE
		$this->compoundTag->setByte("thundering", (int) ceil($level)); //PC vanilla
	}
}
