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

namespace pocketmine\maps\renderer;

use pocketmine\maps\MapData;
use pocketmine\Player;

interface MapRenderer{

	public function initialize(MapData $mapData) : void;

	/**
	 * Renders a map
	 *
	 * @param MapData $mapData
	 * @param Player $player
	 */
	public function render(MapData $mapData, Player $player) : void;
}