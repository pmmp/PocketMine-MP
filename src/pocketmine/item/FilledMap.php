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

use pocketmine\level\utils\MapManager;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\Player;
use pocketmine\utils\Color;

class FilledMap extends Item{

	public const TAG_MAP_UUID = "map_uuid";
	public const TAG_ZOOM = "zoom";

	public function __construct(int $meta = 0){
		parent::__construct(self::FILLED_MAP, $meta, "Filled Map");
	}

	public function createMapDataPacket(int $mapId) : ?ClientboundMapItemDataPacket{
		var_dump($mapId);
		$pk = new ClientboundMapItemDataPacket();
		$pk->mapId = $mapId;
		$pk->height = $pk->width = 128;
		$pk->scale = 0;

		for($y = 0; $y < 128; $y++){
			for($x = 0; $x < 128; $x++){
				if(!isset($pk->colors[$y])){
					$pk->colors[$y] = [];
				}
				$pk->colors[$y][$x] = new Color(127, 178, 56);
			}
		}

		return $pk;
	}

	public function onUpdate(Player $player) : void{
		//TODO
	}

	public function onCreateMap() : void{
		MapManager::registerMap($id = MapManager::getNextId(), $this);
		$nbt = $this->getNamedTag();
		$nbt->setString("map_uuid", strval($id));
	}
}