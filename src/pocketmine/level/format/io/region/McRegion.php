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

namespace pocketmine\level\format\io\region;

use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\ThreadedChunkProvider;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\{
	ByteTag, CompoundTag, IntTag, LongTag, StringTag
};

class McRegion extends BaseLevelProvider{

	public const REGION_FILE_EXTENSION = "mcr";

	protected function createChunkProvider() : ThreadedChunkProvider{
		return new ThreadedChunkProvider(McRegionChunkProvider::class, $this->path);
	}

	public static function getProviderName() : string{
		return "mcregion";
	}

	/**
	 * Returns the storage version as per Minecraft PC world formats.
	 * @return int
	 */
	public static function getPcWorldFormatVersion() : int{
		return 19132; //mcregion
	}

	public function getWorldHeight() : int{
		//TODO: add world height options
		return 128;
	}

	public static function isValid(string $path) : bool{
		$isValid = (file_exists($path . "/level.dat") and is_dir($path . "/region/"));

		if($isValid){
			$files = array_filter(scandir($path . "/region/", SCANDIR_SORT_NONE), function($file){
				return substr($file, strrpos($file, ".") + 1, 2) === "mc"; //region file
			});

			foreach($files as $f){
				if(substr($f, strrpos($f, ".") + 1) !== static::REGION_FILE_EXTENSION){
					$isValid = false;
					break;
				}
			}
		}

		return $isValid;
	}

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []){
		if(!file_exists($path)){
			mkdir($path, 0777, true);
		}

		if(!file_exists($path . "/region")){
			mkdir($path . "/region", 0777);
		}
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
			new IntTag("version", static::getPcWorldFormatVersion()),
			new IntTag("DayTime", 0),
			new LongTag("LastPlayed", (int) (microtime(true) * 1000)),
			new LongTag("RandomSeed", $seed),
			new LongTag("SizeOnDisk", 0),
			new LongTag("Time", 0),
			new StringTag("generatorName", Generator::getGeneratorName($generator)),
			new StringTag("generatorOptions", $options["preset"] ?? ""),
			new StringTag("LevelName", $name),
			new CompoundTag("GameRules", [])
		]);
		$nbt = new BigEndianNBTStream();
		$nbt->setData(new CompoundTag("", [
			$levelData
		]));
		$buffer = $nbt->writeCompressed();
		file_put_contents($path . "level.dat", $buffer);
	}

	public function getGenerator() : string{
		return (string) $this->levelData["generatorName"];
	}

	public function getGeneratorOptions() : array{
		return ["preset" => $this->levelData["generatorOptions"]];
	}

	public function getDifficulty() : int{
		return $this->levelData->getByte("Difficulty", Level::DIFFICULTY_NORMAL);
	}

	public function setDifficulty(int $difficulty){
		$this->levelData->setByte("Difficulty", $difficulty);
	}
}
