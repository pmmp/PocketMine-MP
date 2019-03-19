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
 * This program is free software): you can redistribute it and/or modify
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

use pocketmine\maps\MapData;
use pocketmine\maps\MapManager;
use pocketmine\Player;

class Map extends Item{

	public const TAG_MAP_UUID = "map_uuid"; // TAG_Long
	public const TAG_MAP_DISPLAY_PLAYERS = "map_display_players"; // TAG_Byte
	public const TAG_MAP_NAME_INDEX = "map_name_index"; // TAG_Int
	public const TAG_MAP_IS_INIT = "map_is_init"; // TAG_Byte

	public function __construct(int $meta = 0){
		parent::__construct(self::FILLED_MAP, $meta, "Map");

		if($this->isMapInit()){
			MapManager::loadMapData($this->getMapId());
		}
	}

	public function getMapData() : ?MapData{
		return MapManager::getMapDataById($this->getMapId());
	}

	public function onUpdate(Player $player) : void{
		if($data = $this->getMapData()){
			$data->renderMap($player);
			$player->sendTip("messaaage");

			$data->updateVisiblePlayers($player, $this);
		}
	}

	public function initMap(Player $player, int $scale) : void{
		$this->setMapId($id = MapManager::getNextId());
		$this->setMapInit(true);
		$this->setMapNameIndex($id + 1);

		$data = new MapData($id);
		$data->setScale($scale);
		$data->setDimension($player->level->getDimension());
		$data->calculateMapCenter($player->getFloorX(), $player->getFloorZ(), $scale);

		MapManager::registerMapData($data);
	}

	/**
	 * @return int
	 */
	public function getMaxStackSize() : int{
		return 1;
	}

	public function setMapId(int $mapId) : void{
		$this->getNamedTag()->setLong(self::TAG_MAP_UUID, $mapId);
	}

	public function getMapId() : int{
		return $this->getNamedTag()->getLong(self::TAG_MAP_UUID, 0, true);
	}

	public function setMapNameIndex(int $nameIndex) : void{
		$this->getNamedTag()->setInt(self::TAG_MAP_NAME_INDEX, $nameIndex);
	}

	public function getMapNameIndex() : int{
		return $this->getNamedTag()->getInt(self::TAG_MAP_NAME_INDEX, 0, true);
	}

	public function setMapDisplayPlayers(bool $value) : void{
		$this->getNamedTag()->setByte(self::TAG_MAP_DISPLAY_PLAYERS, intval($value));
	}

	public function isMapDisplayPlayers() : bool{
		return boolval($this->getNamedTag()->getByte(self::TAG_MAP_DISPLAY_PLAYERS, 0, true));
	}

	public function setMapInit(bool $value) : void{
		$this->getNamedTag()->setByte(self::TAG_MAP_IS_INIT, intval($value));
	}

	public function isMapInit() : bool{
		return boolval($this->getNamedTag()->getByte(self::TAG_MAP_IS_INIT, 0, true));
	}
}