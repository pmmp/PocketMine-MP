<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\utils\BannerPatternType;
use pocketmine\item\ItemIdentifier as IID;

final class BannerPatternItem extends Item{
	public function __construct(IID $identifier, string $name, private BannerPatternType $patternType){
		parent::__construct($identifier, $name);
	}

	public function getPatternType() : BannerPatternType{
		return $this->patternType;
	}

	public function getMaxStackSize() : int{
		return 1;
	}
}
