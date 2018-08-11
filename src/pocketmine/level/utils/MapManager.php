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

namespace pocketmine\level\utils;

use pocketmine\item\FilledMap;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\Server;

class MapManager{

	/** @var FilledMap[] */
	protected static $maps = [];
	/** @var int */
	protected static $mapIdCounter = 0;

	public static function registerMap(int $mapId, FilledMap $map) : void{
		self::$maps[$mapId] = $map;
	}

	public static function getMapById(int $id) : ?FilledMap{
		return self::$maps[$id] ?? null;
	}

	public static function getNextId() : int{
		return self::$mapIdCounter++;
	}

	public static function initMaps() : void{
		@mkdir($path = Server::getInstance()->getDataPath() . "maps/");

		if(is_file($path . "idcounts.dat")){
			$stream = new LittleEndianNBTStream();
			/** @var \pocketmine\nbt\tag\CompoundTag $data */
			$data = $stream->read(file_get_contents($path . "idcounts.dat"));

			self::$mapIdCounter = $data->getInt("map", 0);
		}
	}

}