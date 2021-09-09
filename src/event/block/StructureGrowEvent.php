<?php

declare(strict_types=1);

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

/**
 * Called when structures such as Saplings or Bamboo grow.
 * These types of plants tend to change multiple blocks at once upon growing.
 */
class StructureGrowEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	private BlockTransaction $transaction;
	private ?Item $item;
	private ?Player $player;

	public function __construct(Block $block, BlockTransaction $transaction, ?Item $item, ?Player $player){
		parent::__construct($block);
		$this->transaction = $transaction;
		$this->item = $item;
		$this->player = $player;
	}

	public function getTransaction() : BlockTransaction{
		return $this->transaction;
	}

	/**
	 * It returns the item used from the player to grow the structure.
	 * It returns null when the structure grows by itself.
	 */
	public function getItem() : ?Item{
		return $this->item;
	}

	/**
	 * It returns the player which grows the structure.
	 * It returns null when the structure grows by itself.
	 */
	public function getPlayer() : ?Player{
		return $this->player;
	}
}