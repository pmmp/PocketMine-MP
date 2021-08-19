<?php

declare(strict_types=1);

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\world\BlockTransaction;

/**
 * Called when structures such as Saplings or Bamboo grow.
 * These types of plants tend to change multiple blocks at once upon growing.
 */
class StructureGrowEvent extends BlockEvent implements Cancellable{
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