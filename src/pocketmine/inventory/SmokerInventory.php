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
 use pocketmine\tile\Smoker;

 class SmokerInventory extends ContainerInventory{
 	/** @var Smoker */
 	protected $holder;

 	public function __construct(Smoker $tile){
 		parent::__construct($tile);
 	}

 	public function getNetworkType() : int{
 		return WindowTypes::SMOKER;
 	}

 	public function getName() : string{
 		return "Smoker";
 	}

 	public function getDefaultSize() : int{
 		return 3; //1 input, 1 fuel, 1 output
 	}

 	/**
 	 * This override is here for documentation and code completion purposes only.
 	 * @return Smoker
 	 */
 	public function getHolder(){
 		return $this->holder;
 	}

 	/**
 	 * @return Item
 	 */
 	public function getResult() : Item{
 		return $this->getItem(2);
 	}

 	/**
 	 * @return Item
 	 */
 	public function getFuel() : Item{
 		return $this->getItem(1);
 	}

 	/**
 	 * @return Item
 	 */
 	public function getSmelting() : Item{
 		return $this->getItem(0);
 	}

 	/**
 	 * @param Item $item
 	 *
 	 * @return bool
 	 */
 	public function setResult(Item $item) : bool{
 		return $this->setItem(2, $item);
 	}

 	/**
 	 * @param Item $item
 	 *
 	 * @return bool
 	 */
 	public function setFuel(Item $item) : bool{
 		return $this->setItem(1, $item);
 	}

 	/**
 	 * @param Item $item
 	 *
 	 * @return bool
 	 */
 	public function setSmelting(Item $item) : bool{
 		return $this->setItem(0, $item);
 	}
}
