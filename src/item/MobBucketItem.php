<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\item\ItemIdentifier as IID;

/**
 * Generic mob bucket placeholder supporting the various fish/axolotl buckets.
 */
final class MobBucketItem extends Item{
	public function __construct(IID $identifier, string $name){
		parent::__construct($identifier, $name);
	}

	public function getMaxStackSize() : int{
		return 1;
	}
}
