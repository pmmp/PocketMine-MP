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

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

/**
 * This trait implements most methods in the {@link Nameable} interface. It should only be used by Entities.
 */
trait NameableTrait{

	abstract public function setNameTag(string $name) : void;

	/**
	 * @see Entity::onInteract()
	 */
	public function onInteract(Player $player, Vector3 $clickPos) : bool{
		$item = $player->getInventory()->getItemInHand();
		if($item->getTypeId() === ItemTypeIds::NAME_TAG && $item->hasCustomName()){
			$this->setNameTag($item->getCustomName());
			if($player->hasFiniteResources()){
				$item->pop();
				$player->getInventory()->setItemInHand($item);
			}
			return true;
		}
		return parent::onInteract($player, $clickPos);
	}

	/**
	 * @see Entity::getInteractiveTag()
	 */
	public function getInteractiveTag(Player $player, Item $item) : ?string{
		if($item->getTypeId() === ItemTypeIds::NAME_TAG && $item->hasCustomName()){
			return KnownTranslationKeys::ACTION_INTERACT_NAME;
		}
		return parent::getInteractiveTag($player, $item);
	}
}
