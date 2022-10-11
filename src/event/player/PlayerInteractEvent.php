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

namespace pocketmine\event\player;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

/**
 * Called when a player interacts or touches a block.
 * This is called for both left click (start break) and right click (use).
 */
class PlayerInteractEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	public const LEFT_CLICK_BLOCK = 0;
	public const RIGHT_CLICK_BLOCK = 1;

	/** @var Block */
	protected $blockTouched;

	/** @var Vector3 */
	protected $touchVector;

	/** @var int */
	protected $blockFace;

	/** @var Item */
	protected $item;

	/** @var int */
	protected $action;

	public function __construct(Player $player, Item $item, Block $block, ?Vector3 $touchVector, int $face, int $action = PlayerInteractEvent::RIGHT_CLICK_BLOCK){
		$this->player = $player;
		$this->item = $item;
		$this->blockTouched = $block;
		$this->touchVector = $touchVector ?? new Vector3(0, 0, 0);
		$this->blockFace = $face;
		$this->action = $action;
	}

	public function getAction() : int{
		return $this->action;
	}

	public function getItem() : Item{
		return clone $this->item;
	}

	public function getBlock() : Block{
		return $this->blockTouched;
	}

	public function getTouchVector() : Vector3{
		return $this->touchVector;
	}

	public function getFace() : int{
		return $this->blockFace;
	}
}
