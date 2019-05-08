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

use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use function ceil;
use function file_get_contents;
use function file_put_contents;
use function microtime;

class JavaWorldData extends BaseNbtWorldData{

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = [], int $version = 19133) : void{
		Utils::testValidInstance($generator, Generator::class);
		//TODO, add extra details
		$worldData = CompoundTag::create()
			->setByte("hardcore", ($options["hardcore"] ?? false) === true ? 1 : 0)
			->setByte("Difficulty", World::getDifficultyFromString((string) ($options["difficulty"] ?? "normal")))
			->setByte("initialized", 1)
			->setInt("GameType", 0)
			->setInt("generatorVersion", 1) //2 in MCPE
			->setInt("SpawnX", 256)
			->setInt("SpawnY", 70)
			->setInt("SpawnZ", 256)
			->setInt("version", $version)
			->setInt("DayTime", 0)
			->setLong("LastPlayed", (int) (microtime(true) * 1000))
			->setLong("RandomSeed", $seed)
			->setLong("SizeOnDisk", 0)
			->setLong("Time", 0)
			->setString("generatorName", GeneratorManager::getGeneratorName($generator))
			->setString("generatorOptions", $options["preset"] ?? "")
			->setString("LevelName", $name)
			->setTag("GameRules", new CompoundTag());

		$nbt = new BigEndianNbtSerializer();
		$buffer = $nbt->writeCompressed(new TreeRoot(CompoundTag::create()->setTag("Data", $worldData)));
		file_put_contents($path . "level.dat", $buffer);
	}

	protected function load() : CompoundTag{
		$nbt = new BigEndianNbtSerializer();
		try{
			$worldData = $nbt->readCompressed(file_get_contents($this->dataPath))->getTag();
		}catch(NbtDataException $e){
			throw new CorruptedWorldException($e->getMessage(), 0, $e);
		}

		if(!$worldData->hasTag("Data", CompoundTag::class)){
			throw new CorruptedWorldException("Missing 'Data' key or wrong type");
		}
		return $worldData->getCompoundTag("Data");
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
		$nbt = new BigEndianNbtSerializer();
		$buffer = $nbt->writeCompressed(new TreeRoot(CompoundTag::create()->setTag("Data", $this->compoundTag)));
		file_put_contents($this->dataPath, $buffer);
	}


	public function getDifficulty() : int{
		return $this->compoundTag->getByte("Difficulty", World::DIFFICULTY_NORMAL);
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
