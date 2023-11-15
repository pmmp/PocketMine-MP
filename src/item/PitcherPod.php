<?php

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;

class PitcherPod extends Item{

	public function getBlock(?int $clickedFace = null) : Block{
		return VanillaBlocks::PITCHER_CROP();
	}
}