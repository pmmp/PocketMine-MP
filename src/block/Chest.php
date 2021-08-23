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

use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\NormalHorizontalFacingInMetadataTrait;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Chest extends Transparent{
	use FacesOppositePlacingPlayerTrait;
	use NormalHorizontalFacingInMetadataTrait;

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		//these are slightly bigger than in PC
		return [AxisAlignedBB::one()->contract(0.025, 0, 0.025)->trim(Facing::UP, 0.05)];
	}

	public function onPostPlace() : void{
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileChest){
			foreach([
				Facing::rotateY($this->facing, true),
				Facing::rotateY($this->facing, false)
			] as $side){
				$c = $this->getSide($side);
				if($c instanceof Chest and $c->isSameType($this) and $c->facing === $this->facing){
					$pair = $this->position->getWorld()->getTile($c->position);
					if($pair instanceof TileChest and !$pair->isPaired()){
						$pair->pairWith($tile);
						$tile->pairWith($pair);
						break;
					}
				}
			}
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){

			$chest = $this->position->getWorld()->getTile($this->position);
			if($chest instanceof TileChest){
				if(
					!$this->getSide(Facing::UP)->isTransparent() or
					(($pair = $chest->getPair()) !== null and !$pair->getBlock()->getSide(Facing::UP)->isTransparent()) or
					!$chest->canOpenWith($item->getCustomName())
				){
					return true;
				}

				$player->setCurrentWindow($chest->getInventory());
			}
		}

		return true;
	}

	public function getFuelTime() : int{
		return 300;
	}
}
