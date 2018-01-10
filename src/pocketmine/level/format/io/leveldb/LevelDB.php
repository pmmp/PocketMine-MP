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

namespace pocketmine\level\format\io\leveldb;

use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\ThreadedChunkProvider;
use pocketmine\level\generator\Flat;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\level\LevelException;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\tag\{
	ByteTag, CompoundTag, FloatTag, IntTag, LongTag, StringTag
};
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\Binary;

class LevelDB extends BaseLevelProvider{

	//According to Tomasso, these aren't supposed to be readable anymore. Thankfully he didn't change the readable ones...
	public const TAG_DATA_2D = "\x2d";
	public const TAG_DATA_2D_LEGACY = "\x2e";
	public const TAG_SUBCHUNK_PREFIX = "\x2f";
	public const TAG_LEGACY_TERRAIN = "0";
	public const TAG_BLOCK_ENTITY = "1";
	public const TAG_ENTITY = "2";
	public const TAG_PENDING_TICK = "3";
	public const TAG_BLOCK_EXTRA_DATA = "4";
	public const TAG_BIOME_STATE = "5";
	public const TAG_STATE_FINALISATION = "6";

	public const TAG_BORDER_BLOCKS = "8";
	public const TAG_HARDCODED_SPAWNERS = "9";

	public const FINALISATION_NEEDS_INSTATICKING = 0;
	public const FINALISATION_NEEDS_POPULATION = 1;
	public const FINALISATION_DONE = 2;

	public const TAG_VERSION = "v";

	public const ENTRY_FLAT_WORLD_LAYERS = "game_flatworldlayers";

	public const GENERATOR_LIMITED = 0;
	public const GENERATOR_INFINITE = 1;
	public const GENERATOR_FLAT = 2;

	public const CURRENT_STORAGE_VERSION = 6; //Current MCPE level format version
	public const CURRENT_LEVEL_CHUNK_VERSION = 7;
	public const CURRENT_LEVEL_SUBCHUNK_VERSION = 0;

	private static function checkForLevelDBExtension(){
		if(!extension_loaded('leveldb')){
			throw new LevelException("The leveldb PHP extension is required to use this world format");
		}

		if(!defined('LEVELDB_ZLIB_RAW_COMPRESSION')){
			throw new LevelException("Given version of php-leveldb doesn't support zlib raw compression");
		}
	}

	public function __construct(string $path){
		self::checkForLevelDBExtension();
		parent::__construct($path);
	}

	protected function loadLevelData() : void{
		$nbt = new LittleEndianNBTStream();
		$nbt->read(substr(file_get_contents($this->getPath() . "level.dat"), 8));
		$levelData = $nbt->getData();
		if($levelData instanceof CompoundTag){
			$this->levelData = $levelData;
		}else{
			throw new LevelException("Invalid level.dat");
		}

		$version = $this->levelData->getInt("StorageVersion", INT32_MAX, true);
		if($version > self::CURRENT_STORAGE_VERSION){
			throw new LevelException("Specified LevelDB world format version ($version) is not supported by " . \pocketmine\NAME);
		}
	}

	protected function fixLevelData() : void{
		$db = new \LevelDB($this->path . "/db", [
			"compression" => LEVELDB_ZLIB_RAW_COMPRESSION
		]);

		if(!$this->levelData->hasTag("generatorName", StringTag::class)){
			if($this->levelData->hasTag("Generator", IntTag::class)){
				switch($this->levelData->getInt("Generator")){ //Detect correct generator from MCPE data
					case self::GENERATOR_FLAT:
						$this->levelData->setString("generatorName", (string) Generator::getGenerator("FLAT"));
						if(($layers = $db->get(self::ENTRY_FLAT_WORLD_LAYERS)) !== false){ //Detect existing custom flat layers
							$layers = trim($layers, "[]");
						}else{
							$layers = "7,3,3,2";
						}
						$this->levelData->setString("generatorOptions", "2;" . $layers . ";1");
						break;
					case self::GENERATOR_INFINITE:
						//TODO: add a null generator which does not generate missing chunks (to allow importing back to MCPE and generating more normal terrain without PocketMine messing things up)
						$this->levelData->setString("generatorName", (string) Generator::getGenerator("DEFAULT"));
						$this->levelData->setString("generatorOptions", "");
						break;
					case self::GENERATOR_LIMITED:
						throw new LevelException("Limited worlds are not currently supported");
					default:
						throw new LevelException("Unknown LevelDB world format type, this level cannot be loaded");
				}
			}else{
				$this->levelData->setString("generatorName", (string) Generator::getGenerator("DEFAULT"));
			}
		}

		$db->close();

		if(!$this->levelData->hasTag("generatorOptions", StringTag::class)){
			$this->levelData->setString("generatorOptions", "");
		}
	}

	protected function createChunkProvider() : ThreadedChunkProvider{
		return new ThreadedChunkProvider(LevelDBChunkProvider::class, $this->path);
	}

	public static function getProviderName() : string{
		return "leveldb";
	}

	public function getWorldHeight() : int{
		return 256;
	}

	public static function isValid(string $path) : bool{
		return file_exists($path . "/level.dat") and is_dir($path . "/db/");
	}

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []){
		self::checkForLevelDBExtension();

		if(!file_exists($path . "/db")){
			mkdir($path . "/db", 0777, true);
		}

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
			new StringTag("generatorName", Generator::getGeneratorName($generator)),
			new StringTag("generatorOptions", $options["preset"] ?? "")
		]);

		$nbt = new LittleEndianNBTStream();
		$nbt->setData($levelData);
		$buffer = $nbt->write();
		file_put_contents($path . "level.dat", Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);


		$db = new \LevelDB($path . "/db", [
			"compression" => LEVELDB_ZLIB_RAW_COMPRESSION
		]);

		if($generatorType === self::GENERATOR_FLAT and isset($options["preset"])){
			$layers = explode(";", $options["preset"])[1] ?? "";
			if($layers !== ""){
				$out = "[";
				foreach(Flat::parseLayers($layers) as $result){
					$out .= $result[0] . ","; //only id, meta will unfortunately not survive :(
				}
				$out = rtrim($out, ",") . "]"; //remove trailing comma
				$db->put(self::ENTRY_FLAT_WORLD_LAYERS, $out); //Add vanilla flatworld layers to allow terrain generation by MCPE to continue seamlessly
			}
		}

		$db->close();

	}

	public function saveLevelData(){
		$this->levelData->setInt("NetworkVersion", ProtocolInfo::CURRENT_PROTOCOL);
		$this->levelData->setInt("StorageVersion", self::CURRENT_STORAGE_VERSION);

		$nbt = new LittleEndianNBTStream();
		$nbt->setData($this->levelData);
		$buffer = $nbt->write();
		file_put_contents($this->getPath() . "level.dat", Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	public function getGenerator() : string{
		return (string) $this->levelData["generatorName"];
	}

	public function getGeneratorOptions() : array{
		return ["preset" => $this->levelData["generatorOptions"]];
	}

	public function getDifficulty() : int{
		return $this->levelData->getInt("Difficulty", Level::DIFFICULTY_NORMAL);
	}

	public function setDifficulty(int $difficulty){
		$this->levelData->setInt("Difficulty", $difficulty); //yes, this is intended! (in PE: int, PC: byte)
	}
}
