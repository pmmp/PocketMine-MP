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

use pocketmine\entity\passive\AbstractHorse;
use pocketmine\item\Saddle;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

abstract class AbstractHorseInventory extends ContainerInventory{
	/** @var AbstractHorse */
	protected $holder;

	/**
	 * @param Item $saddle
	 */
	public function setSaddle(Item $saddle) : void{
		$this->setItem(0, $saddle);

		$this->holder->setSaddled($saddle instanceof Saddle);
	}

	public function onSlotChange(int $index, Item $before, bool $send) : void{
		parent::onSlotChange($index, $before, $send);

		if($index === 0){
			$this->holder->setSaddled($this->getSaddle() instanceof Saddle);

			$this->holder->level->broadcastLevelSoundEvent($this->holder, LevelSoundEventPacket::SOUND_SADDLE);
		}
	}

	/**
	 * @return Item
	 */
	public function getSaddle() : Item{
		return $this->getItem(0);
	}

	/**
	 * @return AbstractHorse
	 */
	public function getHolder(){
		return $this->holder;
	}
}