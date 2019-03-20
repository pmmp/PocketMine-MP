<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\maps;

use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;

class MapManager{
	/** @var MapData[] */
	protected static $maps = [];
	/** @var int */
	protected static $mapIdCounter = 0;

	public static function registerMapData(MapData $map) : void{
		self::$maps[$map->getId()] = $map;
	}

	public static function getMapDataById(int $id) : ?MapData{
		return self::$maps[$id] ?? null;
	}

	public static function getNextId() : int{
		return self::$mapIdCounter++;
	}

	public static function loadIdCounts() : void{
		@mkdir($path = Server::getInstance()->getDataPath() . "maps/", 0777);
		$stream = new LittleEndianNBTStream();

		if(is_file($path . "idcounts.dat")){
			/** @var \pocketmine\nbt\tag\CompoundTag $data */
			$data = $stream->read(file_get_contents($path . "idcounts.dat"));
			self::$mapIdCounter = $data->getInt("map", 0);
		}
	}

	public static function loadMaps() : void{
		@mkdir($path = Server::getInstance()->getDataPath() . "maps/", 0777);

		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file => $obj){
			$id = intval(str_replace("map_", "", basename($file)));
			self::loadMapData($id);
		}
	}

	public static function loadMapData(int $id) : void{
		@mkdir($path = Server::getInstance()->getDataPath() . "maps/");
		$stream = new BigEndianNBTStream();

		if(is_file($fp = $path . "maps/map_" . strval($id))){
			/** @var \pocketmine\nbt\tag\CompoundTag $data */
			$data = $stream->readCompressed(file_get_contents($fp));
			$mp = new MapData($id);
			$mp->readSaveData($data);

			self::registerMapData($mp);
		}
	}

	public static function saveMaps() : void{
		@mkdir($path = Server::getInstance()->getDataPath() . "maps/", 0777);
		$stream = new LittleEndianNBTStream();

		$idcounts = new CompoundTag();
		$idcounts->setInt("map", self::$mapIdCounter);

		file_put_contents($path . "idcounts.dat", $stream->write($idcounts));
		$stream = new BigEndianNBTStream();

		foreach(self::$maps as $data){
			if(!$data->isVirtual()){
				$tag = new CompoundTag("data");
				$data->writeSaveData($tag);

				file_put_contents($path . "map_" . strval($data->getId()) . ".dat", $stream->writeCompressed($tag));
			}
		}
	}

	public static function resetMaps() : void{
		self::$maps = [];
	}
}