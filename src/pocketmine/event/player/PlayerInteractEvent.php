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
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

/**
 * Called when a player interacts or touches a block (including air?)
 */
class PlayerInteractEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	const LEFT_CLICK_BLOCK = 0;
	const RIGHT_CLICK_BLOCK = 1;
	const LEFT_CLICK_AIR = 2;
	const RIGHT_CLICK_AIR = 3;
	const PHYSICAL = 4;

	/**
	 * @var \pocketmine\block\Block;
	 */
	protected $blockTouched;

	protected $touchVector;

	/** @var int */
	protected $blockFace;

	/** @var \pocketmine\item\Item */
	protected $item;
	
	protected $action;

	public function __construct(Player $player, Item $item, Vector3 $block, $face, $action = PlayerInteractEvent::RIGHT_CLICK_BLOCK){
		if($block instanceof Block){
			$this->blockTouched = $block;
			$this->touchVector = new Vector3(0, 0, 0);
		}else{
			$this->touchVector = $block;
			$this->blockTouched = Block::get(0, 0, new Position(0, 0, 0, $player->level));
		}
		$this->player = $player;
		$this->item = $item;
		$this->blockFace = (int) $face;
		$this->action = (int) $action;
	}

	/**
	 * @return int
	 */
	public function getAction(){
		return $this->action;
	}

	/**
	 * @return Item
	 */
	public function getItem(){
		return $this->item;
	}

	/**
	 * @return Block
	 */
	public function getBlock(){
		return $this->blockTouched;
	}

	/**
	 * @return Vector3
	 */
	public function getTouchVector(){
		return $this->touchVector;
	}

	/**
	 * @return int
	 */
	public function getFace(){
		return $this->blockFace;
	}
}
