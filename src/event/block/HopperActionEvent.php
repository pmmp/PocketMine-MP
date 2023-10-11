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

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

/**
 * Called when a hopper will execute push/pull action on another block.
 * Push action will be called when a hopper wants to push an item into another block.
 * Pull action will be called when a hopper wants to pull an item from another block.
 */
class HopperActionEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	public const ACTION_PUSH = 0;
	public const ACTION_PULL = 1;

	/**
	 * @param Block $targetBlock if the action is push, this is the target block. If the action is pull, this is the source block.
	 */
	public function __construct(
		Block $block,
		private Block $targetBlock,
		private int $action,
	){
		parent::__construct($block);
	}

	public function getTargetBlock() : Block{
		return $this->targetBlock;
	}

	public function getAction() : int{
		return $this->action;
	}
}
