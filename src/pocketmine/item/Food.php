<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Living;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CompletedUsingItemPacket;
use pocketmine\Player;

abstract class Food extends Item implements FoodSource{
	public function requiresHunger() : bool{
		return true;
	}

	/**
	 * @return Item
	 */
	public function getResidue(){
		return ItemFactory::get(Item::AIR, 0, 0);
	}

	public function getAdditionalEffects() : array{
		return [];
	}

	public function getCompletedAction(){
		return CompletedUsingItemPacket::ACTION_EAT;
	}

	public function onUse(Player $player) : bool{
		$slot = $player->getInventory()->getItemInHand();

		$ev = new PlayerItemConsumeEvent($player, $slot);
		$ev->call();

		/** @var $slot Consumable */
		if($ev->isCancelled() or !$player->consumeObject($slot)){
			$player->getInventory()->sendContents($player);
			return true;
		}

		if($player->isSurvival()){
			$slot->pop();
			$player->getInventory()->setItemInHand($slot);
			$player->getInventory()->addItem($slot->getResidue());
		}

		return true;
	}

	public function onConsume(Living $consumer){

	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		return true;
	}
}
