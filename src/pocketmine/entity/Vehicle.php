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

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Vehicle extends Entity implements Rideable{

	public function onLeave(Entity $rider) : void{

	}

	public function onMount(Entity $rider) : void{

	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : void{
		$player->mountEntity($this);
	}

	public function setRiddenByEntity(?Entity $riddenByEntity = null) : void{
		if($riddenByEntity !== null){
			$this->onMount($riddenByEntity);
		}else{
			$this->onLeave($this->riddenByEntity);
		}

		parent::setRiddenByEntity($riddenByEntity);
	}

}