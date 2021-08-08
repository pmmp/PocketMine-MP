<?php

declare(strict_types=1);

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\world\BlockTransaction;

/**
 * Called when Saplings or Bamboo grow.
 * These types of plants tend to change multiple blocks at once upon growing, that's why this event returns the BlockTransaction.
 */
class BlockSproutEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	private BlockTransaction $transaction;

	public function __construct(Block $block, BlockTransaction $transaction){
		parent::__construct($block);
		$this->transaction = $transaction;
	}

	public function getTransaction() : BlockTransaction{
		return $this->transaction;
	}
}