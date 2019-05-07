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

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use function mt_rand;

class Panda extends Animal{
	public const NETWORK_ID = self::PANDA;

	public $width = 1.85;
	public $height = 1.45;

	public function getName() : string{
		return "Panda";
	}

	public function getDrops() : array{
		$drops = [
			ItemFactory::get(Item::BAMBOO, 0, mt_rand(0, 3))
		];
		return $drops;
	}

	public function initEntity() : void{
		$this->setMaxHealth(25);
		parent::initEntity();
	}

	public function getXpDropAmount() : int{
		return 5;
	}
}
