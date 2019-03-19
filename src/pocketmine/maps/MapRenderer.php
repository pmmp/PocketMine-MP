<?php
/**
 * Created by PhpStorm.
 * User: aronf
 * Date: 20.03.2019
 * Time: 00:41
 */

namespace pocketmine\maps;

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