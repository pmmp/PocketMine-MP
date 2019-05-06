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

class Ravager extends Monster{
	public const NETWORK_ID = self::RAVAGER;

	public $width = 1.71;
	public $height = 2.11;

	public function getName() : string{
		return "Ravager";
	}

	public function getDrops() : array{
		$drops = [
			ItemFactory::get(Item::SADDLE, 0, mt_rand(0, 1))
		];
		return $drops;
	}

	public function initEntity() : void{
		$this->setMaxHealth(100);
		parent::initEntity();
	}

	public function getXpDropAmount() : int{
		return 100;
	}
}
