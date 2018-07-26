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

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Called when a player middle-clicks on a block to get an item in creative mode.
 */
class PlayerBlockPickEvent extends PlayerEvent implements Cancellable{
	/** @var Block */
	private $blockClicked;
	/** @var Item */
	private $resultItem;

	public function __construct(Player $player, Block $blockClicked, Item $resultItem){
		$this->player = $player;
		$this->blockClicked = $blockClicked;
		$this->resultItem = $resultItem;
	}

	public function getBlock() : Block{
		return $this->blockClicked;
	}

	public function getResultItem() : Item{
		return $this->resultItem;
	}

	public function setResultItem(Item $item) : void{
		$this->resultItem = clone $item;
	}
}