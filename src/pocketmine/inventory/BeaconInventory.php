<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\tile\Beacon;

class BeaconInventory extends ContainerInventory implements FakeInventory, FakeResultInventory{

	public function __construct(Beacon $tile){
		parent::__construct($tile);
	}

	public function getName() : string{
		return "Beacon";
	}

	public function getDefaultSize() : int{
		return 1;
	}

	public function getUIOffsets() : array{
		return [
			UIInventorySlotOffset::BEACON_PAYMENT => 0
		];
	}

	public function onResult(Player $player, Item $result) : bool{
		return true; // TODO: check beacon
	}

	public function getResultSlot() : int{
		return 0;
	}

	public function getNetworkType() : int{
		return WindowTypes::BEACON;
	}
}