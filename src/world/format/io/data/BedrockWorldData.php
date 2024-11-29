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
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Binary;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Limits;
use pocketmine\VersionInfo;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\generator\Flat;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use Symfony\Component\Filesystem\Path;
use function array_map;
use function file_put_contents;
use function sprintf;
use function strlen;
use function substr;
use function time;

class BedrockWorldData extends BaseNbtWorldData{

	public const CURRENT_STORAGE_VERSION = 10;
	public const CURRENT_STORAGE_NETWORK_VERSION = 748;
	public const CURRENT_CLIENT_VERSION_TARGET = [
		1, //major
		21, //minor
		40, //patch
		1, //revision
		0 //is beta
	];

	public const GENERATOR_LIMITED = 0;
	public const GENERATOR_INFINITE = 1;
	public const GENERATOR_FLAT = 2;

	private const TAG_DAY_CYCLE_STOP_TIME = "DayCycleStopTime";
	private const TAG_DIFFICULTY = "Difficulty";
	private const TAG_FORCE_GAME_TYPE = "ForceGameType";
	private const TAG_GAME_TYPE = "GameType";
	private const TAG_GENERATOR = "Generator";
	private const TAG_LAST_PLAYED = "LastPlayed";
	private const TAG_NETWORK_VERSION = "NetworkVersion";
	private const TAG_STORAGE_VERSION = "StorageVersion";
	private const TAG_IS_EDU = "eduLevel";
	private const TAG_FALL_DAMAGE_ENABLED = "falldamage";
	private const TAG_FIRE_DAMAGE_ENABLED = "firedamage";
	private const TAG_ACHIEVEMENTS_DISABLED = "hasBeenLoadedInCreative";
	private const TAG_IMMUTABLE_WORLD = "immutableWorld";
	private const TAG_LIGHTNING_LEVEL = "lightningLevel";
	private const TAG_LIGHTNING_TIME = "lightningTime";
	private const TAG_PVP_ENABLED = "pvp";
	private const TAG_RAIN_LEVEL = "rainLevel";
	private const TAG_RAIN_TIME = "rainTime";
	private const TAG_SPAWN_MOBS = "spawnMobs";
	private const TAG_TEXTURE_PACKS_REQUIRED = "texturePacksRequired";
	private const TAG_LAST_OPENED_WITH_VERSION = "lastOpenedWithVersion";
	private const TAG_COMMANDS_ENABLED = "commandsEnabled";

	public static function generate(string $path, string $name, WorldCreationOptions $options) : void{
		switch($options->getGeneratorClass()){
			case Flat::class:
				$generatorType = self::GENERATOR_FLAT;
				break;
			default:
				$generatorType = self::GENERATOR_INFINITE;
			//TODO: add support for limited worlds
		}

		$worldData = CompoundTag::create()
			//Vanilla fields
			->setInt(self::TAG_DAY_CYCLE_STOP_TIME, -1)
			->setInt(self::TAG_DIFFICULTY, $options->getDifficulty())
			->setByte(self::TAG_FORCE_GAME_TYPE, 0)
			->setInt(self::TAG_GAME_TYPE, 0)
			->setInt(self::TAG_GENERATOR, $generatorType)
			->setLong(self::TAG_LAST_PLAYED, time())
			->setString(self::TAG_LEVEL_NAME, $name)
			->setInt(self::TAG_NETWORK_VERSION, self::CURRENT_STORAGE_NETWORK_VERSION)
			//->setInt("Platform", 2) //TODO: find out what the possible values are for
			->setLong(self::TAG_RANDOM_SEED, $options->getSeed())
			->setInt(self::TAG_SPAWN_X, $options->getSpawnPosition()->getFloorX())
			->setInt(self::TAG_SPAWN_Y, $options->getSpawnPosition()->getFloorY())
			->setInt(self::TAG_SPAWN_Z, $options->getSpawnPosition()->getFloorZ())
			->setInt(self::TAG_STORAGE_VERSION, self::CURRENT_STORAGE_VERSION)
			->setLong(self::TAG_TIME, 0)
			->setByte(self::TAG_IS_EDU, 0)
			->setByte(self::TAG_FALL_DAMAGE_ENABLED, 1)
			->setByte(self::TAG_FIRE_DAMAGE_ENABLED, 1)
			->setByte(self::TAG_ACHIEVEMENTS_DISABLED, 1) //badly named, this actually determines whether achievements can be earned in this world...
			->setByte(self::TAG_IMMUTABLE_WORLD, 0)
			->setFloat(self::TAG_LIGHTNING_LEVEL, 0.0)
			->setInt(self::TAG_LIGHTNING_TIME, 0)
			->setByte(self::TAG_PVP_ENABLED, 1)
			->setFloat(self::TAG_RAIN_LEVEL, 0.0)
			->setInt(self::TAG_RAIN_TIME, 0)
			->setByte(self::TAG_SPAWN_MOBS, 1)
			->setByte(self::TAG_TEXTURE_PACKS_REQUIRED, 0) //TODO
			->setByte(self::TAG_COMMANDS_ENABLED, 1)
			->setTag(self::TAG_LAST_OPENED_WITH_VERSION, new ListTag(array_map(fn(int $v) => new IntTag($v), self::CURRENT_CLIENT_VERSION_TARGET)))

			//Additional PocketMine-MP fields
			->setString(self::TAG_GENERATOR_NAME, GeneratorManager::getInstance()->getGeneratorName($options->getGeneratorClass()))
			->setString(self::TAG_GENERATOR_OPTIONS, $options->getGeneratorOptions());

		$nbt = new LittleEndianNbtSerializer();
		$buffer = $nbt->write(new TreeRoot($worldData));
		file_put_contents(Path::join($path, "level.dat"), Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	protected function load() : CompoundTag{
		try{
			$rawLevelData = Filesystem::fileGetContents($this->dataPath);
		}catch(\RuntimeException $e){
			throw new CorruptedWorldException($e->getMessage(), 0, $e);
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

		$version = $worldData->getInt(self::TAG_STORAGE_VERSION, Limits::INT32_MAX);
		if($version === Limits::INT32_MAX){
			throw new CorruptedWorldException(sprintf("Missing '%s' tag in level.dat", self::TAG_STORAGE_VERSION));
		}
		if($version > self::CURRENT_STORAGE_VERSION){
			throw new UnsupportedWorldFormatException("LevelDB world format version $version is currently unsupported");
		}
		//StorageVersion is rarely updated - instead, the game relies on the NetworkVersion tag, which is synced with
		//the network protocol version for that version.
		$protocolVersion = $worldData->getInt(self::TAG_NETWORK_VERSION, Limits::INT32_MAX);
		if($protocolVersion === Limits::INT32_MAX){
			throw new CorruptedWorldException(sprintf("Missing '%s' tag in level.dat", self::TAG_NETWORK_VERSION));
		}
		if($protocolVersion > self::CURRENT_STORAGE_NETWORK_VERSION){
			throw new UnsupportedWorldFormatException("LevelDB world protocol version $protocolVersion is currently unsupported");
		}

		return $worldData;
	}

	protected function fix() : void{
		$generatorNameTag = $this->compoundTag->getTag(self::TAG_GENERATOR_NAME);
		if(!($generatorNameTag instanceof StringTag)){
			if(($mcpeGeneratorTypeTag = $this->compoundTag->getTag(self::TAG_GENERATOR)) instanceof IntTag){
				switch($mcpeGeneratorTypeTag->getValue()){ //Detect correct generator from MCPE data
					case self::GENERATOR_FLAT:
						$this->compoundTag->setString(self::TAG_GENERATOR_NAME, "flat");
						$this->compoundTag->setString(self::TAG_GENERATOR_OPTIONS, "2;7,3,3,2;1");
						break;
					case self::GENERATOR_INFINITE:
						//TODO: add a null generator which does not generate missing chunks (to allow importing back to MCPE and generating more normal terrain without PocketMine messing things up)
						$this->compoundTag->setString(self::TAG_GENERATOR_NAME, "default");
						$this->compoundTag->setString(self::TAG_GENERATOR_OPTIONS, "");
						break;
					case self::GENERATOR_LIMITED:
						throw new UnsupportedWorldFormatException("Limited worlds are not currently supported");
					default:
						throw new UnsupportedWorldFormatException("Unknown LevelDB generator type");
				}
			}else{
				$this->compoundTag->setString(self::TAG_GENERATOR_NAME, "default");
			}
		}elseif(($generatorName = self::hackyFixForGeneratorClasspathInLevelDat($generatorNameTag->getValue())) !== null){
			$this->compoundTag->setString(self::TAG_GENERATOR_NAME, $generatorName);
		}

		if(!($this->compoundTag->getTag(self::TAG_GENERATOR_OPTIONS)) instanceof StringTag){
			$this->compoundTag->setString(self::TAG_GENERATOR_OPTIONS, "");
		}
	}

	public function save() : void{
		$this->compoundTag->setInt(self::TAG_NETWORK_VERSION, self::CURRENT_STORAGE_NETWORK_VERSION);
		$this->compoundTag->setInt(self::TAG_STORAGE_VERSION, self::CURRENT_STORAGE_VERSION);
		$this->compoundTag->setTag(self::TAG_LAST_OPENED_WITH_VERSION, new ListTag(array_map(fn(int $v) => new IntTag($v), self::CURRENT_CLIENT_VERSION_TARGET)));
		$this->compoundTag->setLong(VersionInfo::TAG_WORLD_DATA_VERSION, VersionInfo::WORLD_DATA_VERSION);

		$nbt = new LittleEndianNbtSerializer();
		$buffer = $nbt->write(new TreeRoot($this->compoundTag));
		Filesystem::safeFilePutContents($this->dataPath, Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	public function getDifficulty() : int{
		return $this->compoundTag->getInt(self::TAG_DIFFICULTY, World::DIFFICULTY_NORMAL);
	}

	public function setDifficulty(int $difficulty) : void{
		$this->compoundTag->setInt(self::TAG_DIFFICULTY, $difficulty); //yes, this is intended! (in PE: int, PC: byte)
	}

	public function getRainTime() : int{
		return $this->compoundTag->getInt(self::TAG_RAIN_TIME, 0);
	}

	public function setRainTime(int $ticks) : void{
		$this->compoundTag->setInt(self::TAG_RAIN_TIME, $ticks);
	}

	public function getRainLevel() : float{
		return $this->compoundTag->getFloat(self::TAG_RAIN_LEVEL, 0.0);
	}

	public function setRainLevel(float $level) : void{
		$this->compoundTag->setFloat(self::TAG_RAIN_LEVEL, $level);
	}

	public function getLightningTime() : int{
		return $this->compoundTag->getInt(self::TAG_LIGHTNING_TIME, 0);
	}

	public function setLightningTime(int $ticks) : void{
		$this->compoundTag->setInt(self::TAG_LIGHTNING_TIME, $ticks);
	}

	public function getLightningLevel() : float{
		return $this->compoundTag->getFloat(self::TAG_LIGHTNING_LEVEL, 0.0);
	}

	public function setLightningLevel(float $level) : void{
		$this->compoundTag->setFloat(self::TAG_LIGHTNING_LEVEL, $level);
	}
}
