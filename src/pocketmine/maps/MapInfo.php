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

use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\Player;

class MapInfo{

	/** @var Player */
	public $player;
	public $currentCheckX = 0;
	public $packetSendTimer = 0;
	public $dirty = true;

	public $minX = 0;
	public $minY = 0;
	public $maxX = 127;
	public $maxY = 127;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function getPacket(MapData $mapData) : ?ClientboundMapItemDataPacket{
		if($this->dirty){
			$this->dirty = false;

			$pk = new ClientboundMapItemDataPacket();
			$pk->height = $pk->width = 128;
			$pk->dimensionId = $mapData->getDimension();
			$pk->scale = $mapData->getScale();
			$pk->colors = $mapData->getColors();
			$pk->mapId = $mapData->getId();
			$pk->decorations = $mapData->getDecorations();
			$pk->trackedEntities = $mapData->getTrackedObjects();

			$pk->cropTexture($this->minX, $this->minY, $this->maxX + 1 - $this->minX, $this->maxY + 1 - $this->minY);

			return $pk;
		}elseif(($this->packetSendTimer++ % 5) === 0){ // update decorations
			$pk = new ClientboundMapItemDataPacket();
			$pk->height = $pk->width = 128;
			$pk->dimensionId = $mapData->getDimension();
			$pk->scale = $mapData->getScale();
			$pk->mapId = $mapData->getId();
			$pk->decorations = $mapData->getDecorations();
			$pk->trackedEntities = $mapData->getTrackedObjects();

			return $pk;
		}

		return null;
	}


	/**
	 * Calculates map canvas
	 *
	 * @param int $x
	 * @param int $y
	 */
	public function updateTextureAt(int $x, int $y) : void{
		if($this->dirty){
			$this->minX = min($this->minX, $x);
			$this->minY = min($this->minY, $y);
			$this->maxX = max($this->maxX, $x);
			$this->maxY = max($this->maxY, $y);
		}else{
			$this->dirty = true;
			$this->minX = $x;
			$this->minY = $y;
			$this->maxX = $x;
			$this->maxY = $y;
		}
	}
}