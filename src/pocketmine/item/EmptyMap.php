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

use pocketmine\math\Vector3;
use pocketmine\Player;

class EmptyMap extends Item{

	public const TYPE_EXPLORER_MAP = 2;

	public function __construct(int $meta = 0){
		parent::__construct(self::EMPTY_MAP, $meta, "Empty Map");
	}

	/**
	 * @param Player  $player
	 * @param Vector3 $directionVector
	 *
	 * @return bool
	 */
	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		$map = new Map();
		$map->initMap($player, 0);

		if($this->getDamage() === self::TYPE_EXPLORER_MAP){
			$map->setMapDisplayPlayers(true);
		}

		if($player->getInventory()->canAddItem($map)){
			$player->getInventory()->addItem($map);
		}else{
			$player->dropItem($map);
		}

		$this->pop();

		return true;
	}

	/**
	 * @return int
	 */
	public function getMaxStackSize() : int{
		return 1;
	}
}