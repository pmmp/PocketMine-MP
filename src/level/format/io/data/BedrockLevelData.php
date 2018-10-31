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

use pocketmine\level\format\io\exception\UnsupportedLevelFormatException;
use pocketmine\level\generator\Flat;
use pocketmine\level\generator\GeneratorManager;
use pocketmine\level\Level;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\Binary;

class BedrockLevelData extends BaseNbtLevelData{

	public const CURRENT_STORAGE_VERSION = 6;

	public const GENERATOR_LIMITED = 0;
	public const GENERATOR_INFINITE = 1;
	public const GENERATOR_FLAT = 2;

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []) : void{
		switch($generator){
			case Flat::class:
				$generatorType = self::GENERATOR_FLAT;
				break;
			default:
				$generatorType = self::GENERATOR_INFINITE;
			//TODO: add support for limited worlds
		}

		$levelData = new CompoundTag("", [
			//Vanilla fields
			new IntTag("DayCycleStopTime", -1),
			new IntTag("Difficulty", Level::getDifficultyFromString((string) ($options["difficulty"] ?? "normal"))),
			new ByteTag("ForceGameType", 0),
			new IntTag("GameType", 0),
			new IntTag("Generator", $generatorType),
			new LongTag("LastPlayed", time()),
			new StringTag("LevelName", $name),
			new IntTag("NetworkVersion", ProtocolInfo::CURRENT_PROTOCOL),
			//new IntTag("Platform", 2), //TODO: find out what the possible values are for
			new LongTag("RandomSeed", $seed),
			new IntTag("SpawnX", 0),
			new IntTag("SpawnY", 32767),
			new IntTag("SpawnZ", 0),
			new IntTag("StorageVersion", self::CURRENT_STORAGE_VERSION),
			new LongTag("Time", 0),
			new ByteTag("eduLevel", 0),
			new ByteTag("falldamage", 1),
			new ByteTag("firedamage", 1),
			new ByteTag("hasBeenLoadedInCreative", 1), //badly named, this actually determines whether achievements can be earned in this world...
			new ByteTag("immutableWorld", 0),
			new FloatTag("lightningLevel", 0.0),
			new IntTag("lightningTime", 0),
			new ByteTag("pvp", 1),
			new FloatTag("rainLevel", 0.0),
			new IntTag("rainTime", 0),
			new ByteTag("spawnMobs", 1),
			new ByteTag("texturePacksRequired", 0), //TODO

			//Additional PocketMine-MP fields
			new CompoundTag("GameRules", []),
			new ByteTag("hardcore", ($options["hardcore"] ?? false) === true ? 1 : 0),
			new StringTag("generatorName", GeneratorManager::getGeneratorName($generator)),
			new StringTag("generatorOptions", $options["preset"] ?? "")
		]);

		$nbt = new LittleEndianNBTStream();
		$buffer = $nbt->write($levelData);
		file_put_contents($path . "level.dat", Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	protected function load() : ?CompoundTag{
		$nbt = new LittleEndianNBTStream();
		$levelData = $nbt->read(substr(file_get_contents($this->dataPath), 8));

		$version = $levelData->getInt("StorageVersion", INT32_MAX, true);
		if($version > self::CURRENT_STORAGE_VERSION){
			throw new UnsupportedLevelFormatException("Specified LevelDB world format version ($version) is not supported by " . \pocketmine\NAME);
		}

		return $levelData;
	}

	protected function fix() : void{
		if(!$this->compoundTag->hasTag("generatorName", StringTag::class)){
			if($this->compoundTag->hasTag("Generator", IntTag::class)){
				switch($this->compoundTag->getInt("Generator")){ //Detect correct generator from MCPE data
					case self::GENERATOR_FLAT:
						$this->compoundTag->setString("generatorName", "flat");
						$this->compoundTag->setString("generatorOptions", "2;7,3,3,2;1");
						break;
					case self::GENERATOR_INFINITE:
						//TODO: add a null generator which does not generate missing chunks (to allow importing back to MCPE and generating more normal terrain without PocketMine messing things up)
						$this->compoundTag->setString("generatorName", "default");
						$this->compoundTag->setString("generatorOptions", "");
						break;
					case self::GENERATOR_LIMITED:
						throw new UnsupportedLevelFormatException("Limited worlds are not currently supported");
					default:
						throw new UnsupportedLevelFormatException("Unknown LevelDB world format type, this level cannot be loaded");
				}
			}else{
				$this->compoundTag->setString("generatorName", "default");
			}
		}elseif(($generatorName = self::hackyFixForGeneratorClasspathInLevelDat($this->compoundTag->getString("generatorName"))) !== null){
			$this->compoundTag->setString("generatorName", $generatorName);
		}

		if(!$this->compoundTag->hasTag("generatorOptions", StringTag::class)){
			$this->compoundTag->setString("generatorOptions", "");
		}
	}

	public function save() : void{
		$this->compoundTag->setInt("NetworkVersion", ProtocolInfo::CURRENT_PROTOCOL);
		$this->compoundTag->setInt("StorageVersion", self::CURRENT_STORAGE_VERSION);

		$nbt = new LittleEndianNBTStream();
		$buffer = $nbt->write($this->compoundTag);
		file_put_contents($this->dataPath, Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	public function getDifficulty() : int{
		return $this->compoundTag->getInt("Difficulty", Level::DIFFICULTY_NORMAL);
	}

	public function setDifficulty(int $difficulty) : void{
		$this->compoundTag->setInt("Difficulty", $difficulty); //yes, this is intended! (in PE: int, PC: byte)
	}

	public function getRainTime() : int{
		return $this->compoundTag->getInt("rainTime", 0);
	}

	public function setRainTime(int $ticks) : void{
		$this->compoundTag->setInt("rainTime", $ticks);
	}

	public function getRainLevel() : float{
		return $this->compoundTag->getFloat("rainLevel", 0.0);
	}

	public function setRainLevel(float $level) : void{
		$this->compoundTag->setFloat("rainLevel", $level);
	}

	public function getLightningTime() : int{
		return $this->compoundTag->getInt("lightningTime", 0);
	}

	public function setLightningTime(int $ticks) : void{
		$this->compoundTag->setInt("lightningTime", $ticks);
	}

	public function getLightningLevel() : float{
		return $this->compoundTag->getFloat("lightningLevel", 0.0);
	}

	public function setLightningLevel(float $level) : void{
		$this->compoundTag->setFloat("lightningLevel", $level);
	}
}
