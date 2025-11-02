<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\item\ItemIdentifier as IID;

final class HeavyCore extends Item{
	public function __construct(IID $identifier, string $name = "Heavy Core"){
		parent::__construct($identifier, $name);
	}

	public function getMaxStackSize() : int{
		return 1;
	}
}
