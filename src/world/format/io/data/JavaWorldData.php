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
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use pocketmine\VersionInfo;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use Symfony\Component\Filesystem\Path;
use function ceil;
use function file_put_contents;
use function microtime;
use function zlib_decode;
use function zlib_encode;
use const ZLIB_ENCODING_GZIP;

class JavaWorldData extends BaseNbtWorldData{

	private const TAG_DAY_TIME = "DayTime";
	private const TAG_DIFFICULTY = "Difficulty";
	private const TAG_FORMAT_VERSION = "version";
	private const TAG_GAME_RULES = "GameRules";
	private const TAG_GAME_TYPE = "GameType";
	private const TAG_GENERATOR_VERSION = "generatorVersion";
	private const TAG_HARDCORE = "hardcore";
	private const TAG_INITIALIZED = "initialized";
	private const TAG_LAST_PLAYED = "LastPlayed";
	private const TAG_RAINING = "raining";
	private const TAG_RAIN_TIME = "rainTime";
	private const TAG_ROOT_DATA = "Data";
	private const TAG_SIZE_ON_DISK = "SizeOnDisk";
	private const TAG_THUNDERING = "thundering";
	private const TAG_THUNDER_TIME = "thunderTime";

	public static function generate(string $path, string $name, WorldCreationOptions $options, int $version = 19133) : void{
		//TODO, add extra details

		$worldData = CompoundTag::create()
			->setByte(self::TAG_HARDCORE, 0)
			->setByte(self::TAG_DIFFICULTY, $options->getDifficulty())
			->setByte(self::TAG_INITIALIZED, 1)
			->setInt(self::TAG_GAME_TYPE, 0)
			->setInt(self::TAG_GENERATOR_VERSION, 1) //2 in MCPE
			->setInt(self::TAG_SPAWN_X, $options->getSpawnPosition()->getFloorX())
			->setInt(self::TAG_SPAWN_Y, $options->getSpawnPosition()->getFloorY())
			->setInt(self::TAG_SPAWN_Z, $options->getSpawnPosition()->getFloorZ())
			->setInt(self::TAG_FORMAT_VERSION, $version)
			->setInt(self::TAG_DAY_TIME, 0)
			->setLong(self::TAG_LAST_PLAYED, (int) (microtime(true) * 1000))
			->setLong(self::TAG_RANDOM_SEED, $options->getSeed())
			->setLong(self::TAG_SIZE_ON_DISK, 0)
			->setLong(self::TAG_TIME, 0)
			->setString(self::TAG_GENERATOR_NAME, GeneratorManager::getInstance()->getGeneratorName($options->getGeneratorClass()))
			->setString(self::TAG_GENERATOR_OPTIONS, $options->getGeneratorOptions())
			->setString(self::TAG_LEVEL_NAME, $name)
			->setTag(self::TAG_GAME_RULES, new CompoundTag());

		$nbt = new BigEndianNbtSerializer();
		$buffer = zlib_encode($nbt->write(new TreeRoot(CompoundTag::create()->setTag(self::TAG_ROOT_DATA, $worldData))), ZLIB_ENCODING_GZIP);
		file_put_contents(Path::join($path, "level.dat"), $buffer);
	}

	protected function load() : CompoundTag{
		try{
			$rawLevelData = Filesystem::fileGetContents($this->dataPath);
		}catch(\RuntimeException $e){
			throw new CorruptedWorldException($e->getMessage(), 0, $e);
		}
		$nbt = new BigEndianNbtSerializer();
		$decompressed = @zlib_decode($rawLevelData);
		if($decompressed === false){
			throw new CorruptedWorldException("Failed to decompress level.dat contents");
		}
		try{
			$worldData = $nbt->read($decompressed)->mustGetCompoundTag();
		}catch(NbtDataException $e){
			throw new CorruptedWorldException($e->getMessage(), 0, $e);
		}

		$dataTag = $worldData->getTag(self::TAG_ROOT_DATA);
		if(!($dataTag instanceof CompoundTag)){
			throw new CorruptedWorldException("Missing '" . self::TAG_ROOT_DATA . "' key or wrong type");
		}
		return $dataTag;
	}

	protected function fix() : void{
		$generatorNameTag = $this->compoundTag->getTag(self::TAG_GENERATOR_NAME);
		if(!($generatorNameTag instanceof StringTag)){
			$this->compoundTag->setString(self::TAG_GENERATOR_NAME, "default");
		}elseif(($generatorName = self::hackyFixForGeneratorClasspathInLevelDat($generatorNameTag->getValue())) !== null){
			$this->compoundTag->setString(self::TAG_GENERATOR_NAME, $generatorName);
		}

		if(!($this->compoundTag->getTag(self::TAG_GENERATOR_OPTIONS) instanceof StringTag)){
			$this->compoundTag->setString(self::TAG_GENERATOR_OPTIONS, "");
		}
	}

	public function save() : void{
		$this->compoundTag->setLong(VersionInfo::TAG_WORLD_DATA_VERSION, VersionInfo::WORLD_DATA_VERSION);

		$nbt = new BigEndianNbtSerializer();
		$buffer = Utils::assumeNotFalse(zlib_encode($nbt->write(new TreeRoot(CompoundTag::create()->setTag(self::TAG_ROOT_DATA, $this->compoundTag))), ZLIB_ENCODING_GZIP));
		Filesystem::safeFilePutContents($this->dataPath, $buffer);
	}

	public function getDifficulty() : int{
		return $this->compoundTag->getByte(self::TAG_DIFFICULTY, World::DIFFICULTY_NORMAL);
	}

	public function setDifficulty(int $difficulty) : void{
		$this->compoundTag->setByte(self::TAG_DIFFICULTY, $difficulty);
	}

	public function getRainTime() : int{
		return $this->compoundTag->getInt(self::TAG_RAIN_TIME, 0);
	}

	public function setRainTime(int $ticks) : void{
		$this->compoundTag->setInt(self::TAG_RAIN_TIME, $ticks);
	}

	public function getRainLevel() : float{
		return (float) $this->compoundTag->getByte(self::TAG_RAINING, 0);
	}

	public function setRainLevel(float $level) : void{
		$this->compoundTag->setByte(self::TAG_RAINING, (int) ceil($level));
	}

	public function getLightningTime() : int{
		return $this->compoundTag->getInt(self::TAG_THUNDER_TIME, 0);
	}

	public function setLightningTime(int $ticks) : void{
		$this->compoundTag->setInt(self::TAG_THUNDER_TIME, $ticks);
	}

	public function getLightningLevel() : float{
		return (float) $this->compoundTag->getByte(self::TAG_THUNDERING, 0);
	}

	public function setLightningLevel(float $level) : void{
		$this->compoundTag->setByte(self::TAG_THUNDERING, (int) ceil($level));
	}
}
