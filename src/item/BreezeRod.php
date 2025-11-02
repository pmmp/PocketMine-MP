<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\item\ItemIdentifier as IID;

/**
 * Minimal Breeze Rod implementation so the item can be registered and obtained.
 */
final class BreezeRod extends Item{
	public function __construct(IID $identifier, string $name = "Breeze Rod"){
		parent::__construct($identifier, $name);
	}
}
