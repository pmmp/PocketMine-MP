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
use pocketmine\item\Item;

/**
 * Called when a block is destroyed because its support has been destroyed.
 */
class BlockSupportBreakEvent extends BlockEvent{
	/** @var Item[] */
	protected array $blockDrops = [];

	/**
	 * @param Item[] $drops
	 */
	public function __construct(
		Block $block,
		array $drops = [],
		protected int $xpDrops = 0
	){
		parent::__construct($block);
		$this->setDrops($drops);
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array{
		return $this->blockDrops;
	}

	/**
	 * @param Item[] $drops
	 */
	public function setDrops(array $drops) : void{
		$this->setDropsVariadic(...$drops);
	}

	/**
	 * Variadic hack for easy array member type enforcement.
	 */
	public function setDropsVariadic(Item ...$drops) : void{
		$this->blockDrops = $drops;
	}

	/**
	 * Returns how much XP will be dropped by breaking this block.
	 */
	public function getXpDropAmount() : int{
		return $this->xpDrops;
	}

	/**
	 * Sets how much XP will be dropped by breaking this block.
	 */
	public function setXpDropAmount(int $amount) : void{
		if($amount < 0){
			throw new \InvalidArgumentException("Amount must be at least zero");
		}
		$this->xpDrops = $amount;
	}
}
