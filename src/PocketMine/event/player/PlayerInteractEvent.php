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

namespace PocketMine\Event\Player;

use PocketMine\Block\Block;
use PocketMine\Event\CancellableEvent;
use PocketMine\Item\Item;
use PocketMine\Player;

/**
 * Called when a player interacts or touches a block (including air?)
 */
class PlayerInteractEvent extends PlayerEvent implements CancellableEvent{
	public static $handlers;
	public static $handlerPriority;

	/**
	 * @var \PocketMine\Block\Block;
	 */
	protected $blockTouched;

	/**
	 * @var int
	 */
	protected $blockFace;

	/**
	 * @var \PocketMine\Item\Item
	 */
	protected $item;

	public function __construct(Player $player, Item $item, Block $block, $face){
		$this->blockTouched = $block;
		$this->player = $player;
		$this->item = $item;
		$this->blockFace = (int) $face;
	}

	public function getItem(){
		return $this->item;
	}

	public function getBlock(){
		return $this->blockTouched;
	}

	public function getFace(){
		return $this->blockFace;
	}
}