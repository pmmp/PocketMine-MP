<?php

declare(strict_types=1);

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\world\BlockTransaction;

/**
 * Called when Saplings or Bamboo grow.
 * These types of plants tend to change multiple blocks at once upon growing, that's why this event returns the BlockTransaction.
 */
class BlockSproutEvent extends BaseBlockChangeEvent{

	private BlockTransaction $transaction;

	public function __construct(Block $block, Block $newState, BlockTransaction $transaction){
		parent::__construct($block, $newState);
		$this->transaction = $transaction;
	}

	public function getTransaction() : BlockTransaction{
		return $this->transaction;
	}
}