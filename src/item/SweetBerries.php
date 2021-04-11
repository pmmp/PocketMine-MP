<?php


namespace pocketmine\item;


use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;

class SweetBerries extends Food {

	public function getFoodRestore(): int{
		return 2;
	}


	public function getSaturationRestore(): float{
		return 1.2;
	}

	public function getBlock(?int $clickedFace = null): Block{
		return VanillaBlocks::SWEET_BERRY_BUSH();
	}
}
