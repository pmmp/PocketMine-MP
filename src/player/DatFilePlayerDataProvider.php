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

namespace pocketmine\player;

use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function file_exists;
use function rename;
use function strtolower;
use function zlib_decode;
use function zlib_encode;
use const ZLIB_ENCODING_GZIP;

/**
 * Stores player data in a single .dat file per player. Each file is gzipped big-endian NBT.
 */
final class DatFilePlayerDataProvider implements PlayerDataProvider{

	public function __construct(
		private string $path
	){}

	private function getPlayerDataPath(string $username) : string{
		return Path::join($this->path, strtolower($username) . '.dat');
	}

	private function handleCorruptedPlayerData(string $name) : void{
		$path = $this->getPlayerDataPath($name);
		rename($path, $path . '.bak');
	}

	public function hasData(string $name) : bool{
		return file_exists($this->getPlayerDataPath($name));
	}

	public function loadData(string $name) : ?CompoundTag{
		$name = strtolower($name);
		$path = $this->getPlayerDataPath($name);

		if(!file_exists($path)){
			return null;
		}

		try{
			$contents = Filesystem::fileGetContents($path);
		}catch(\RuntimeException $e){
			throw new PlayerDataLoadException("Failed to read player data file \"$path\": " . $e->getMessage(), 0, $e);
		}
		try{
			$decompressed = ErrorToExceptionHandler::trapAndRemoveFalse(fn() => zlib_decode($contents));
		}catch(\ErrorException $e){
			$this->handleCorruptedPlayerData($name);
			throw new PlayerDataLoadException("Failed to decompress raw player data for \"$name\": " . $e->getMessage(), 0, $e);
		}

		try{
			return (new BigEndianNbtSerializer())->read($decompressed)->mustGetCompoundTag();
		}catch(NbtDataException $e){ //corrupt data
			$this->handleCorruptedPlayerData($name);
			throw new PlayerDataLoadException("Failed to decode NBT data for \"$name\": " . $e->getMessage(), 0, $e);
		}
	}

	public function saveData(string $name, CompoundTag $data) : void{
		$nbt = new BigEndianNbtSerializer();
		$contents = Utils::assumeNotFalse(zlib_encode($nbt->write(new TreeRoot($data)), ZLIB_ENCODING_GZIP), "zlib_encode() failed unexpectedly");
		try{
			Filesystem::safeFilePutContents($this->getPlayerDataPath($name), $contents);
		}catch(\RuntimeException $e){
			throw new PlayerDataSaveException("Failed to write player data file: " . $e->getMessage(), 0, $e);
		}
	}
}
