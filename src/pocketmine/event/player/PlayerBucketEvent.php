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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\player;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

abstract class PlayerBucketEvent extends PlayerEvent implements Cancellable{

	/** @var Block */
	private $blockClicked;
	/** @var int */
	private $blockFace;
	/** @var Item */
	private $bucket;
	/** @var Item */
	private $item;

	/**
	 * @param Player $who
	 * @param Block  $blockClicked
	 * @param int    $blockFace
	 * @param Item   $bucket
	 * @param Item   $itemInHand
	 */
	public function __construct(Player $who, Block $blockClicked, $blockFace, Item $bucket, Item $itemInHand){
		$this->player = $who;
		$this->blockClicked = $blockClicked;
		$this->blockFace = (int) $blockFace;
		$this->item = $itemInHand;
		$this->bucket = $bucket;
	}

	/**
	 * Returns the bucket used in this event
	 *
	 * @return Item
	 */
	public function getBucket(){
		return $this->bucket;
	}

	/**
	 * Returns the item in hand after the event
	 *
	 * @return Item
	 */
	public function getItem(){
		return $this->item;
	}

	/**
	 * @param Item $item
	 */
	public function setItem(Item $item){
		$this->item = $item;
	}

	/**
	 * @return Block
	 */
	public function getBlockClicked(){
		return $this->blockClicked;
	}
}