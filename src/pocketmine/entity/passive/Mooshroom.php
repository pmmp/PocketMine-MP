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

namespace pocketmine\entity\passive;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Bowl;
use pocketmine\item\Shears;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Mooshroom extends Cow{

	public const NETWORK_ID = self::MOOSHROOM;

	protected $spawnableBlock = Block::MYCELIUM;

	public function getName() : string{
		return "Mooshroom";
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		if(!$this->isImmobile()){
			if($item instanceof Bowl and !$this->isBaby()){
				$new = ItemFactory::get(Item::MUSHROOM_STEW);
				if($player->isSurvival()){
					$item->pop();
				}

				if($player->getInventory()->canAddItem($new)){
					$player->getInventory()->addItem($new);
				}else{
					$player->dropItem($new);
				}

				return true;
			}elseif($item instanceof Shears and !$this->isBaby()){
				$cow = new Cow($this->level, Entity::createBaseNBT($this));
				$cow->setRotation($this->yaw, $this->pitch);
				$cow->setHealth($this->getHealth());
				$cow->setNameTag($this->getNameTag());
				$cow->setImmobile(!$this->server->mobAiEnabled);

				$item->applyDamage(1);

				for($i = 0; $i < 5; $i++){
					$player->dropItem(ItemFactory::get(Block::RED_MUSHROOM));
				}

				$this->flagForDespawn();
				$cow->spawnToAll();

				return true;
			}
		}
		return parent::onInteract($player, $item, $clickPos);
	}
}