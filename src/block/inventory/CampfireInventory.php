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

namespace pocketmine\block\inventory;

use pocketmine\block\Campfire;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\FurnaceType;
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\world\Position;

class CampfireInventory extends SimpleInventory implements BlockInventory{
	use BlockInventoryTrait;

	public function __construct(Position $holder){
		$this->holder = $holder;
		parent::__construct(4);
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function canAddItem(Item $item) : bool{
		return $this->holder->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::CAMPFIRE())->match($item) instanceof FurnaceRecipe && parent::canAddItem($item);
	}

	public function onSlotChange(int $index, Item $before) : void{
		parent::onSlotChange($index, $before);

		$block = $this->holder->getWorld()->getBlock($this->holder);
		if($block instanceof Campfire){
			$this->holder->getWorld()->setBlock($this->holder, $block);
		}
	}
}
