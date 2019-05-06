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
use pocketmine\tile\StoneCutter;

class StoneCutterInventory extends ContainerInventory{
	/** @var StoneCutter */
	protected $holder;

	public function __construct(StoneCutter $tile){
		parent::__construct($tile);
	}

	public function getNetworkType() : int{
		return WindowTypes::STONECUTTER;
	}

	public function getName() : string{
		return "Stone Cutter";
	}

	public function getDefaultSize() : int{
		return 2; //1 input, 1 output == 2
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return StoneCutter
	 */
	public function getHolder(){
		return $this->holder;
	}

	/**
	 * @return Item
	 */
	public function getResult() : Item{
		return $this->getItem(1);
	}

	/**
	 * @return Item
	 */
	public function getMaterial() : Item{
		return $this->getItem(0);
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setResult(Item $item) : bool{
		return $this->setItem(1, $item);
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setMaterial(Item $item) : bool{
		return $this->setItem(0, $item);
	}
}
