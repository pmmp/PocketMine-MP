<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Living;

class Shield extends Durable{

	public function getMaxDurability() : int{
		return 336;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function onDestroyBlock(Block $block, array &$returnedItems) : bool{
		if(!$block->getBreakInfo()->breaksInstantly()){
			return $this->applyDamage(2);
		}

		return false;
	}

	public function onTickWorn(Living $entity) : bool{
		return false;
	}
}