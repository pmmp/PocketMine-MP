<?php

namespace pocketmine\block\utils;

use pocketmine\block\tile\Container;
use pocketmine\item\Item;

trait ContainerTrait{

	public function getPickedItem(bool $addUserData = false) : Item{
		$item = parent::getPickedItem($addUserData);
		if($addUserData){
			$tile = $this->position->getWorld()->getTile($this->position);
			if($tile instanceof Container){
				$item->setContainedItems($tile->getRealInventory()->getContents());
			}
		}
		return $item;
	}
}