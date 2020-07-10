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

use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\Binary;
use pocketmine\utils\Limits;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\generator\Flat;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use function file_get_contents;
use function file_put_contents;
use function strlen;
use function substr;
use function time;

class BedrockWorldData extends BaseNbtWorldData{

	public const CURRENT_STORAGE_VERSION = 8;

	public const GENERATOR_LIMITED = 0;
	public const GENERATOR_INFINITE = 1;
	public const GENERATOR_FLAT = 2;

	/**
	 * @param mixed[] $options
	 * @phpstan-param class-string<Generator> $generator
	 * @phpstan-param array<string, mixed>    $options
	 */
	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []) : void{
		Utils::testValidInstance($generator, Generator::class);
		switch($generator){
			case Flat::class:
				$generatorType = self::GENERATOR_FLAT;
				break;
			default:
				$generatorType = self::GENERATOR_INFINITE;
			//TODO: add support for limited worlds
		}

		$worldData = CompoundTag::create()
			//Vanilla fields
			->setInt("DayCycleStopTime", -1)
			->setInt("Difficulty", World::getDifficultyFromString((string) ($options["difficulty"] ?? "normal")))
			->setByte("ForceGameType", 0)
			->setInt("GameType", 0)
			->setInt("Generator", $generatorType)
			->setLong("LastPlayed", time())
			->setString("LevelName", $name)
			->setInt("NetworkVersion", ProtocolInfo::CURRENT_PROTOCOL)
			//->setInt("Platform", 2) //TODO: find out what the possible values are for
			->setLong("RandomSeed", $seed)
			->setInt("SpawnX", 0)
			->setInt("SpawnY", 32767)
			->setInt("SpawnZ", 0)
			->setInt("StorageVersion", self::CURRENT_STORAGE_VERSION)
			->setLong("Time", 0)
			->setByte("eduLevel", 0)
			->setByte("falldamage", 1)
			->setByte("firedamage", 1)
			->setByte("hasBeenLoadedInCreative", 1) //badly named, this actually determines whether achievements can be earned in this world...
			->setByte("immutableWorld", 0)
			->setFloat("lightningLevel", 0.0)
			->setInt("lightningTime", 0)
			->setByte("pvp", 1)
			->setFloat("rainLevel", 0.0)
			->setInt("rainTime", 0)
			->setByte("spawnMobs", 1)
			->setByte("texturePacksRequired", 0) //TODO

			//Additional PocketMine-MP fields
			->setTag("GameRules", new CompoundTag())
			->setByte("hardcore", ($options["hardcore"] ?? false) === true ? 1 : 0)
			->setString("generatorName", GeneratorManager::getInstance()->getGeneratorName($generator))
			->setString("generatorOptions", $options["preset"] ?? "");

		$nbt = new LittleEndianNbtSerializer();
		$buffer = $nbt->write(new TreeRoot($worldData));
		file_put_contents($path . "level.dat", Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	protected function load() : CompoundTag{
		$rawLevelData = @file_get_contents($this->dataPath);
		if($rawLevelData === false){
			throw new CorruptedWorldException("Failed to read level.dat (permission denied or doesn't exist)");
		}
		if(strlen($rawLevelData) <= 8){
			throw new CorruptedWorldException("Truncated level.dat");
		}
		$nbt = new LittleEndianNbtSerializer();
		try{
			$worldData = $nbt->read(substr($rawLevelData, 8))->mustGetCompoundTag();
		}catch(NbtDataException $e){
			throw new CorruptedWorldException($e->getMessage(), 0, $e);
		}

		$version = $worldData->getInt("StorageVersion", Limits::INT32_MAX);
		if($version > self::CURRENT_STORAGE_VERSION){
			throw new UnsupportedWorldFormatException("LevelDB world format version $version is currently unsupported");
		}

		return $worldData;
	}

	protected function fix() : void{
		$generatorNameTag = $this->compoundTag->getTag("generatorName");
		if(!($generatorNameTag instanceof StringTag)){
			if(($mcpeGeneratorTypeTag = $this->compoundTag->getTag("Generator")) instanceof IntTag){
				switch($mcpeGeneratorTypeTag->getValue()){ //Detect correct generator from MCPE data
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
						throw new UnsupportedWorldFormatException("Limited worlds are not currently supported");
					default:
						throw new UnsupportedWorldFormatException("Unknown LevelDB generator type");
				}
			}else{
				$this->compoundTag->setString("generatorName", "default");
			}
		}elseif(($generatorName = self::hackyFixForGeneratorClasspathInLevelDat($generatorNameTag->getValue())) !== null){
			$this->compoundTag->setString("generatorName", $generatorName);
		}

		if(!($this->compoundTag->getTag("generatorOptions")) instanceof StringTag){
			$this->compoundTag->setString("generatorOptions", "");
		}
	}

	public function save() : void{
		$this->compoundTag->setInt("NetworkVersion", ProtocolInfo::CURRENT_PROTOCOL);
		$this->compoundTag->setInt("StorageVersion", self::CURRENT_STORAGE_VERSION);

		$nbt = new LittleEndianNbtSerializer();
		$buffer = $nbt->write(new TreeRoot($this->compoundTag));
		file_put_contents($this->dataPath, Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	public function getDifficulty() : int{
		return $this->compoundTag->getInt("Difficulty", World::DIFFICULTY_NORMAL);
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
