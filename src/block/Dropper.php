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

use pocketmine\block\tile\Dropper as TileDropper;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function abs;

class Dropper extends Transparent{
	use PoweredByRedstoneTrait;

	private int $facing = Facing::DOWN;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readFacing($stateMeta & 0x07);
		$this->powered = ($stateMeta & BlockLegacyMetadata::DROPPER_TRIGGERED) !== 0;
	}

	protected function writeStateToMeta() : int{
		return BlockDataSerializer::writeFacing($this->facing) | ($this->powered ? BlockLegacyMetadata::DROPPER_TRIGGERED : 0);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getFacing() : int{ return $this->facing; }

	/** @return $this */
	public function setFacing(int $facing) : self{
		Facing::validate($facing);
		$this->facing = $facing;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$x = abs($player->getPosition()->getFloorX() - $this->position->getX());
			$y = $player->getPosition()->getFloorY() - $this->position->getY();
			$z = abs($player->getPosition()->getFloorZ() - $this->position->getZ());
			if ($y > 0 && $x < 2 && $z < 2) {
				$this->facing = Facing::UP;
			} elseif ($y < -1 && $x < 2 && $z < 2) {
				$this->facing = Facing::DOWN;
			} else {
				$this->facing = Facing::opposite($player->getHorizontalFacing());
			}
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$tile = $this->position->getWorld()->getTile($this->position);
			if($tile instanceof TileDropper){
				$player->setCurrentWindow($tile->getInventory());
			}
			return true;
		}
		return false;
	}

	public function onScheduledUpdate() : void{
		//TODO
	}

	//TODO: redstone logic, sucking logic
}
