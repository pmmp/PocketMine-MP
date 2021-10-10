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

use pocketmine\block\utils\SlabType;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\block\utils\AnalogRedstoneSignalEmitterTrait;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class RedstoneWire extends Flowable{
	use AnalogRedstoneSignalEmitterTrait;

	private function canBeSupportedBy(Block $block) : bool{
		if($block instanceof Slab) {
			if ($block->getSlabType()->equals(SlabType::BOTTOM())) {
				return false;
			}
			return true;
		}
		if($block instanceof Beacon ||
			$block instanceof Glass ||
			$block instanceof Farmland ||
			$block instanceof Glowstone ||
			$block instanceof GrassPath ||
			$block instanceof HardenedGlass ||
			$block instanceof Hopper ||
			$block instanceof Ice ||
			$block instanceof Melon ||
			$block instanceof SeaLantern ||
			$block instanceof Slime
		) {
			return true;
		}
		return !$block->isTransparent();
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->signalStrength = BlockDataSerializer::readBoundedInt("signalStrength", $stateMeta, 0, 15);
	}

	protected function writeStateToMeta() : int{
		return $this->signalStrength;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		//TODO: check connections to nearby redstone components
	}
}
