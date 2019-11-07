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

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\Player;

class PlayerUIInventory extends BaseInventory{

	/** @var Player */
	protected $holder;
	/** @var PlayerCursorInventory */
	protected $cursorInventory;
	/** @var CraftingGrid */
	protected $craftingGrid;
	/** @var CraftingGrid */
	protected $bigCraftingGrid;

	public function __construct(Player $holder){
		$this->holder = $holder;
		$this->cursorInventory = new PlayerCursorInventory($this);
		$this->craftingGrid = new CraftingGrid($this, CraftingGrid::OFFSET_SMALL, CraftingGrid::SIZE_SMALL);
		$this->bigCraftingGrid = new CraftingGrid($this, CraftingGrid::OFFSET_BIG, CraftingGrid::SIZE_BIG);
		parent::__construct();
	}

	public function getName() : string{
		return "UI";
	}

	public function getSize() : int{
		return 51;
	}

	public function getDefaultSize() : int{
		return 51;
	}

	public function getCursorInventory() : PlayerCursorInventory{
		return $this->cursorInventory;
	}

	public function getCraftingGrid(int $size = CraftingGrid::SIZE_SMALL) : CraftingGrid{
		if($size > CraftingGrid::SIZE_SMALL){
			return $this->bigCraftingGrid;
		}
		return $this->craftingGrid;
	}

	public function setSize(int $size) : void{

	}

	public function sendSlot(int $index, $target) : void{
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new InventorySlotPacket();
		$pk->inventorySlot = $index;
		$pk->item = $this->getItem($index);

		foreach($target as $player){
			if($player === $this->getHolder()){
				$pk->windowId = ContainerIds::UI;
				$player->dataPacket($pk);
			}else{
				if(($id = $player->getWindowId($this)) === ContainerIds::NONE){
					$this->close($player);
					continue;
				}
				$pk->windowId = $id;
				$player->dataPacket($pk);
			}
		}
	}

	public function sendContents($target) : void{
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new InventoryContentPacket();
		$pk->items = $this->getContents(true);

		foreach($target as $player){
			if($player === $this->getHolder()){
				$pk->windowId = ContainerIds::UI;
				$player->dataPacket($pk);
			}else{
				if(($id = $player->getWindowId($this)) === ContainerIds::NONE){
					$this->close($player);
					continue;
				}
				$pk->windowId = $id;
				$player->dataPacket($pk);
			}
		}
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Player
	 */
	public function getHolder(){
		return $this->holder;
	}
}
