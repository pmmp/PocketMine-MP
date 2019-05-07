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

class Turtle extends Animal{
	public const NETWORK_ID = self::TURTLE;

	public $width = 1.25 + M_PI - 3;
	public $height = 0.15;

	public function getName() : string{
		return "Turtle";
	}

	public function getDrops() : array{
		$drops = [
			ItemFactory::get(Item::TURTLE_SHELL_PIECE, 0, mt_rand(0, 3))
		];
		return $drops;
	}

	public function initEntity() : void{
		$this->setMaxHealth(30);
		parent::initEntity();
	}

	public function getXpDropAmount() : int{
		return 5;
	}
}
