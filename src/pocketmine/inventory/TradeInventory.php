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

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\entity\Villager;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\Player;

class TradeInventory extends BaseInventory{

	/** @var Villager */
	protected $holder;
	/** @var bool */
	protected $isTraded = false;

	public function __construct(Villager $villager){
		$this->holder = $villager;
		parent::__construct();
	}

	public function getName() : string{
		return "Trading";
	}

	public function getDefaultSize() : int{
		return 3; //1 buyA, 1 buyB, 1 sell
	}

	public function onOpen(Player $who) : void{
		$tag = clone $this->holder->getOffers();
		if($tag !== null){
			parent::onOpen($who);

			$this->holder->getDataPropertyManager()->setLong(Villager::DATA_TRADING_PLAYER_EID, $who->getId());
			$pk = new UpdateTradePacket();
			$pk->windowId = $who->getWindowId($this);
			$pk->varint1 = 0;
			$pk->varint2 = 0;
			$pk->isWilling = $this->holder->isWilling();
			$pk->traderEid = $this->holder->getId();
			$pk->playerEid = $who->getId();
			$pk->displayName = $this->holder->getDisplayName();

			try{
				$nbtWriter = new NetworkLittleEndianNBTStream();
				$pk->offers = $nbtWriter->write($tag);
			}catch(\Exception $exception){}

			$who->dataPacket($pk);
		}else{
			parent::onClose($who);
		}
	}

	public function onClose(Player $who) : void{
		$this->holder->getDataPropertyManager()->removeProperty(Villager::DATA_TRADING_PLAYER_EID);
		if($this->isTraded){
			$this->holder->updateTradeTier();
			$this->isTraded = false;
		}
		parent::onClose($who);
	}

	public function getHolder() : Villager{
		return $this->holder;
	}


    /**
     * For trade tier update
     *
     * @param bool $value
     */
    public function setTraded(bool $value) : void{
		$this->isTraded = $value;
	}
}
