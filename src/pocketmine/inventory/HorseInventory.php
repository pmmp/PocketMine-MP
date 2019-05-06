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

class HorseInventory extends ContainerInventory{

	public function getNetworkType() : int{
		return WindowTypes::HORSE;
	}

	public function getName() : string{
		return "Horse";
	}

	public function getDefaultSize() : int{
		return 1; //1 input, 1 output == 2
	}

	/**
	 * @return Item
	 */
	public function getArmor() : Item{
		return $this->getItem(1);
	}

	/**
	 * @return Item
	 */
	public function getSaddle() : Item{
		return $this->getItem(0);
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setArmor(Item $item) : bool{
		return $this->setItem(1, $item);
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setSaddle(Item $item) : bool{
		return $this->setItem(0, $item);
	}
}
