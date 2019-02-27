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

namespace pocketmine\event\player;

use pocketmine\entity\projectile\FishingHook;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

class PlayerFishingEvent extends PlayerEvent implements Cancellable{

	/** @var Item */
	protected $resultItem;
	/** @var FishingHook */
	protected $hook;
	/** @var int */
	protected $xpDropAmount = 0;

	/**
	 * PlayerFishingEvent constructor.
	 *
	 * @param Player      $fisher
	 * @param FishingHook $hook
	 * @param Item        $resultItem
	 * @param int         $xpDropAmount
	 */
	public function __construct(Player $fisher, FishingHook $hook, Item $resultItem, int $xpDropAmount = 0){
		$this->player = $fisher;
		$this->hook = $hook;
		$this->resultItem = $resultItem;
		$this->xpDropAmount = $xpDropAmount;
	}

	/**
	 * @return FishingHook
	 */
	public function getHook() : FishingHook{
		return $this->hook;
	}

	/**
	 * @return Item
	 */
	public function getResultItem() : Item{
		return $this->resultItem;
	}

	/**
	 * @param Item $resultItem
	 */
	public function setResultItem(Item $resultItem) : void{
		$this->resultItem = $resultItem;
	}

	/**
	 * @return int
	 */
	public function getXpDropAmount() : int{
		return $this->xpDropAmount;
	}

	/**
	 * @param int $xpDropAmount
	 */
	public function setXpDropAmount(int $xpDropAmount) : void{
		$this->xpDropAmount = $xpDropAmount;
	}
}