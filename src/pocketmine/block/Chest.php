<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Chest as TileChest;

class Chest extends Transparent{

	/** @var int */
	protected $facing = Facing::NORTH;

	protected function writeStateToMeta() : int{
		return $this->facing;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataValidator::readHorizontalFacing($stateMeta);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getHardness() : float{
		return 2.5;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		//these are slightly bigger than in PC
		return AxisAlignedBB::one()->contract(0.025, 0, 0.025)->trim(Facing::UP, 0.05);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}

		if(parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			//TODO: this is fragile and might have unintended side effects on ender chests if modified carelessly
			$tile = $this->level->getTile($this);
			if($tile instanceof TileChest){
				foreach([
					Facing::rotateY($this->facing, true),
					Facing::rotateY($this->facing, false)
				] as $side){
					$c = $this->getSide($side);
					if($c instanceof Chest and $c->isSameType($this) and $c->facing === $this->facing){
						$pair = $this->level->getTile($c);
						if($pair instanceof TileChest and !$pair->isPaired()){
							$pair->pairWith($tile);
							$tile->pairWith($pair);
							break;
						}
					}
				}
			}

			return true;
		}

		return false;
	}

	public function onActivate(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){

			$chest = $this->getLevel()->getTile($this);
			if($chest instanceof TileChest){
				if(
					!$this->getSide(Facing::UP)->isTransparent() or
					($chest->isPaired() and !$chest->getPair()->getBlock()->getSide(Facing::UP)->isTransparent()) or
					!$chest->canOpenWith($item->getCustomName())
				){
					return true;
				}

				$player->addWindow($chest->getInventory());
			}
		}

		return true;
	}

	public function getFuelTime() : int{
		return 300;
	}
}
