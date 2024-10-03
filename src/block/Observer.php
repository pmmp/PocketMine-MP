<?php

namespace pocketmine\block;

use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Observer extends Opaque{
	use AnyFacingTrait;
	use PoweredByRedstoneTrait;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->facing($this->facing);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			if(abs($player->getPosition()->x - $this->position->x) < 2 && abs($player->getPosition()->z - $this->position->z) < 2){
				$y = $player->getEyePos()->y;

				if($y - $this->position->y > 2){
					$this->facing = Facing::DOWN;
				}elseif($this->position->y - $y > 0){
					$this->facing = Facing::UP;
				}else{
					$this->facing = $player->getHorizontalFacing();
				}
			}else{
				$this->facing = $player->getHorizontalFacing();
			}
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

//	TODO: redstone logic

}