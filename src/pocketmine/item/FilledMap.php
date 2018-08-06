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
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\Player;

class FilledMap extends Item{

	public const TAG_COLORS = "colors";
	public const TAG_SCALE = "scale";
	public const TAG_DIMENSION = "dimension";

	public function __construct(int $meta = 0){
		parent::__construct(self::FILLED_MAP, $meta, "Filled Map");
	}

	public function createMapDataPacket() : ?ClientboundMapItemDataPacket{
		$pk = new ClientboundMapItemDataPacket();
		$pk->mapId = $this->getNamedTag()->getInt("mapId", 0);
		$pk->height = $pk->width = 128;
		$pk->scale = 0;

		return $pk;
	}

	public function onUpdate(Player $player) : void{
		//TODO
	}

	public function onCreateMap() : void{
		MapManager::registerMap($id = MapManager::getNextId(), $this);
		$nbt = $this->getNamedTag();
		$nbt->setInt("mapId", $id);
	}
}