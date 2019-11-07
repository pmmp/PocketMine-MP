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

use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\Player;

class PlayerUIInventory extends BaseInventory{
	/** @var Player */
	protected $holder;
	/** @var int */
	protected $size;
	/** @var int */
	protected $offset;
	/** @var PlayerCursorInventory */
	protected $cursorInventory;

	public function __construct(Player $holder){
		$this->holder = $holder;
		$this->cursorInventory = new PlayerCursorInventory($this);
		parent::__construct();
	}

	public function getCursorInventory(){
		return $this->cursorInventory;
	}

	public function getName() : string{
		return "UI";
	}

	public function getDefaultSize() : int{
		return 51;
	}

	public function getSize() : int{
		return 51;
	}

	public function getHolder(){
		return $this->holder;
	}

	public function setSize(int $size){}

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
}