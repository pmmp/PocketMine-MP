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
use pocketmine\block\utils\SupportType;
use pocketmine\event\block\ChestPairEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Chest extends Transparent{
	use FacesOppositePlacingPlayerTrait;

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		//these are slightly bigger than in PC
		return [AxisAlignedBB::one()->contract(0.025, 0, 0.025)->trim(Facing::UP, 0.05)];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function onPostPlace() : void{
		$world = $this->position->getWorld();
		$tile = $world->getTile($this->position);
		if($tile instanceof TileChest){
			foreach([false, true] as $clockwise){
				$side = Facing::rotateY($this->facing, $clockwise);
				$c = $this->getSide($side);
				if($c instanceof Chest && $c->hasSameTypeId($this) && $c->facing === $this->facing){
					$pair = $world->getTile($c->position);
					if($pair instanceof TileChest && !$pair->isPaired()){
						[$left, $right] = $clockwise ? [$c, $this] : [$this, $c];
						$ev = new ChestPairEvent($left, $right);
						$ev->call();
						if(!$ev->isCancelled() && $world->getBlock($this->position)->hasSameTypeId($this) && $world->getBlock($c->position)->hasSameTypeId($c)){
							$pair->pairWith($tile);
							$tile->pairWith($pair);
							break;
						}
					}
				}
			}
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player instanceof Player){

			$chest = $this->position->getWorld()->getTile($this->position);
			if($chest instanceof TileChest){
				if(
					!$this->getSide(Facing::UP)->isTransparent() ||
					(($pair = $chest->getPair()) !== null && !$pair->getBlock()->getSide(Facing::UP)->isTransparent()) ||
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
