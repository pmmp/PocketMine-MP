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

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Player;
use pocketmine\Server;

class EndPortal extends Transparent{

	protected $id = self::END_PORTAL;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getLightLevel() : int{
		return 1;
	}

	public function getName() : string{
		return "End Portal";
	}

	public function getHardness() : float{
		return -1;
	}

	public function getBlastResistance() : float{
		return 18000000;
	}

	public function isBreakable(Item $item) : bool{
		return false;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void{
		if(Server::getInstance()->isAllowTheEnd()){
			if($entity->getLevel()->getDimension() === DimensionIds::THE_END){
				$entity->travelToDimension(DimensionIds::OVERWORLD);
			}else{
				$entity->travelToDimension(DimensionIds::THE_END);
			}
		}
	}

	public function onBreak(Item $item, Player $player = null) : bool{
		$result = parent::onBreak($item, $player);

		foreach($this->getHorizontalSides() as $side){
			if($side instanceof EndPortal){
				$side->onBreak($item, $player);
			}
		}

		return $result;
	}
}