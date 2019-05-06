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

use pocketmine\inventory\LlamaInventory;
use pocketmine\item\ItemFactory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use function atan2;
use function mt_rand;
use function sqrt;
use const M_PI;

class Llama extends Animal{
	public const NETWORK_ID = self::LLAMA;

	public $width = 0.9;
	public $height = 1.87;

	public function initEntity() : void{
		$this->setMaxHealth(15);
		parent::initEntity();
	}

	public function getName() : string{
		return "Llama";
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
			$player->addWindow(new LlamaInventory($this));
			return false;
	}

	public function getDrops() : array{
		return [
			ItemFactory::get(Item::LEATHER, 0, mt_rand(0, 2))
		];
	}
}
