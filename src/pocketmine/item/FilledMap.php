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

namespace pocketmine\item;

use pocketmine\level\Level;
use pocketmine\maps\MapData;
use pocketmine\maps\MapManager;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\Color;

class FilledMap extends Item{

	public const TAG_MAP_UUID = "map_uuid";
	public const TAG_ZOOM = "zoom";

	public function __construct(int $meta = 0){
		parent::__construct(self::FILLED_MAP, $meta, "Filled Map");
	}

	public function getMapData() : ?MapData{
		return MapManager::getMapDataById($this->getMapId());
	}

	public function onUpdate(Player $player) : void{
		if($data = $this->getMapData()){
			if($data->isDirty()){
				$player->sendDataPacket($data->getDataPacket());
			}
		}
	}

	public function onCreateMap(Level $level, int $scale) : void{
		$this->setMapId($id = MapManager::getNextId());
		$this->setZoom($scale);

		$data = new MapData($id);
		$data->setScale($scale);
		$data->setDimension($level->getDimension());
		$data->calculateMapCenter(0, 0, $scale);

		// Set base colors for testing
		// All colors supported
		for($y = 0; $y < 128; $y++){
			for($x = 0; $x < 128; $x++){
				$data->setColorAt($x, $y, new Color(255, 0, 0));
			}
		}

		MapManager::registerMapData($data);
	}

	/**
	 * @return int
	 */
	public function getMaxStackSize() : int{
		return 1;
	}

	/**
	 * @param int $zoom
	 */
	public function setZoom(int $zoom) : void{
		if($zoom > 4){
			$zoom = 4;
		}
		$this->setNamedTagEntry(new ByteTag(self::TAG_ZOOM, $zoom));
	}

	/**
	 * @return int
	 */
	public function getZoom() : int{
		return $this->getNamedTag()->getByte(self::TAG_ZOOM, 0);
	}

	/**
	 * @param int $mapId
	 */
	public function setMapId(int $mapId) : void{
		$this->setNamedTagEntry(new StringTag(self::TAG_MAP_UUID, strval($mapId)));
	}

	/**
	 * @return int
	 */
	public function getMapId() : int{
		return intval($this->getNamedTag()->getString(self::TAG_MAP_UUID, "0"));
	}
}