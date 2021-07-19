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

use pocketmine\block\tile\ShulkerBox as TileShulkerBox;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class ShulkerBox extends Opaque{
	use AnyFacingTrait;

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$shulker = $this->pos->getWorld()->getTile($this->pos);
		if($shulker instanceof TileShulkerBox){
			$shulker->setFacing($this->facing);
		}
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$shulker = $this->pos->getWorld()->getTile($this->pos);
		if($shulker instanceof TileShulkerBox){
			$this->facing = $shulker->getFacing();
		}
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->facing = $face;

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	private function addDataFromTile(TileShulkerBox $tile, Item $item) : void{
		$shulkerNBT = $tile->getCleanedNBT();
		if($shulkerNBT !== null){
			$item->setNamedTag($shulkerNBT);
		}
		if($tile->hasName()){
			$item->setCustomName($tile->getName());
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$drop = $this->asItem();
		if(($tile = $this->pos->getWorld()->getTile($this->pos)) instanceof TileShulkerBox){
			$this->addDataFromTile($tile, $drop);
		}
		return [$drop];
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		$result = parent::getPickedItem($addUserData);
		if($addUserData && ($tile = $this->pos->getWorld()->getTile($this->pos)) instanceof TileShulkerBox){
			$this->addDataFromTile($tile, $result);
		}
		return $result;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){

			$shulker = $this->pos->getWorld()->getTile($this->pos);
			if($shulker instanceof TileShulkerBox){
				if(
					$this->getSide($this->facing)->getId() !== BlockLegacyIds::AIR or
					!$shulker->canOpenWith($item->getCustomName())
				){
					return true;
				}

				$player->setCurrentWindow($shulker->getInventory());
			}
		}

		return true;
	}
}
