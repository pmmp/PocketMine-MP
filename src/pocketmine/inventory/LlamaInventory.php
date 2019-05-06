<?php

/*
    _____ _                 _        __  __ _____
  / ____| |               | |      |  \/  |  __ \
 | |    | | ___  _   _  __| |______| \  / | |__) |
 | |    | |/ _ \| | | |/ _` |______| |\/| |  ___/
 | |____| | (_) | |_| | (_| |      | |  | | |
  \_____|_|\___/ \__,_|\__,_|      |_|  |_|_|

     Make of Things.
 */

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\WindowTypes;

class LlamaInventory extends ContainerInventory{

	public function getNetworkType() : int{
		return WindowTypes::HORSE;
	}

	public function getName() : string{
		return "Llama";
	}

	public function getDefaultSize() : int{
		return 0; //1 input
	}
	
	/**
	 * @return Item
	 */
	public function getCarpet() : Item{
		return $this->getItem(0);
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setCarpet(Item $item) : bool{
		return $this->setItem(0, $item);
	}
}
