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

use pocketmine\entity\Villager;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\Player;

class TradeInventory extends FakeWindow{
	/** @var Villager */
	protected $holder;

	public function __construct(Villager $holder){
		$this->holder = $holder;
		parent::__construct();
	}

	public function getName() : string{
		return "Trade";
	}

	public function getDefaultSize() : int{
		return 3;
	}

	public function onOpen(Player $who) : void{
		parent::onOpen($who);

		$this->holder->setTradingPlayer($playerEid = $who->getId());

		$pk = new UpdateTradePacket();
		$pk->windowId = $who->getWindowId($this);
		$pk->varint1 = $pk->varint2 = 0;
		$pk->isWilling = true;
		$pk->traderEid = $this->holder->getId();
		$pk->playerEid = $playerEid;
		$pk->displayName = $this->holder->getTraderName();
		$pk->offers = (new NetworkLittleEndianNBTStream())->write(new CompoundTag("", [
			$this->holder->getRecipes()
		]));
		$who->sendDataPacket($pk);
	}

	public function onClose(Player $who) : void{
		var_dump(__METHOD__);
		$this->holder->setTradingPlayer();
		parent::onClose($who);
	}
}
