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

use pocketmine\entity\passive\Villager;
use pocketmine\item\Item;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\Player;

class TradeInventory extends ContainerInventory implements FakeInventory{

	/** @var Villager */
	protected $holder;
	/** @var bool */
	protected $traded = false;

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Trading";
	}

	/**
	 * @return int
	 */
	public function getNetworkType() : int{
		return WindowTypes::TRADING;
	}

	/**
	 * @return int
	 */
	public function getDefaultSize() : int{
		return 3; //1 buyA, 1 buyB, 1 sell
	}

	/**
	 * @param Player $who
	 */
	public function onOpen(Player $who) : void{
		if($this->holder->getOffers() instanceof CompoundTag){
			BaseInventory::onOpen($who);

			$this->holder->setTradingPlayer($who);

			$pk = new UpdateTradePacket();
			$pk->windowId = $who->getWindowId($this);
			$pk->isWilling = $this->holder->isWilling();
			$pk->traderEid = $this->holder->getId();
			$pk->playerEid = $who->getId();
			$pk->displayName = $this->holder->getDisplayName();
			$pk->offers = (new NetworkLittleEndianNBTStream())->write(clone $this->holder->getOffers());

			$who->sendDataPacket($pk);
		}else{
			BaseInventory::onClose($who);
		}
	}

	/**
	 * @param Player $who
	 */
	public function onClose(Player $who) : void{
		$this->holder->setTradingPlayer(null);

		if($this->traded){
			$this->holder->updateTradeTier();
			$this->traded = false;
		}

		BaseInventory::onClose($who);
	}

	/**
	 * @return Villager
	 */
	public function getHolder() : Villager{
		return $this->holder;
	}

	public function onResult(Item $result) : bool{
		$holder = $this->getHolder();
		$recipes = $holder->getOffers()->getListTag("Recipes");
		/** @var CompoundTag $tag */
		foreach($recipes->getAllValues() as $index => $tag){
			$sell = Item::nbtDeserialize($tag->getCompoundTag("sell"));
			if($sell->equalsExact($result)){
				$tag->setInt("uses", $tag->getInt("uses") + 1);
				$recipes->set($index, $tag);
				break;
			}
		}

		$this->holder->setWilling(mt_rand(1, 3) <= 2);
		$this->setTraded(true);

		return true; // TODO
	}

	/**
	 * @return bool
	 */
	public function isTraded() : bool{
		return $this->traded;
	}

	/**
	 * @param bool $traded
	 */
	public function setTraded(bool $traded) : void{
		$this->traded = $traded;
	}
}
