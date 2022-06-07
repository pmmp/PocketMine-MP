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

use pocketmine\block\utils\SupportType;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Lantern extends Transparent{

	protected bool $hanging = false;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->hanging = ($stateMeta & BlockLegacyMetadata::LANTERN_FLAG_HANGING) !== 0;
	}

	protected function writeStateToMeta() : int{
		return $this->hanging ? BlockLegacyMetadata::LANTERN_FLAG_HANGING : 0;
	}

	public function getStateBitmask() : int{
		return 0b1;
	}

	public function isHanging() : bool{ return $this->hanging; }

	/** @return $this */
	public function setHanging(bool $hanging) : self{
		$this->hanging = $hanging;
		return $this;
	}

	public function getLightLevel() : int{
		return 15;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [
			AxisAlignedBB::one()
				->trim(Facing::UP,   $this->hanging ? 6 / 16 : 8 / 16)
				->trim(Facing::DOWN, $this->hanging ? 2 / 16 : 0)
				->squash(Axis::X, 5 / 16)
				->squash(Axis::Z, 5 / 16)
		];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedBy($blockReplace->getSide(Facing::UP), Facing::DOWN) && !$this->canBeSupportedBy($blockReplace->getSide(Facing::DOWN), Facing::UP)){
			return false;
		}

		$this->hanging = ($face === Facing::DOWN || !$this->canBeSupportedBy($this->position->getWorld()->getBlock($blockReplace->getPosition()->down()), Facing::UP));
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$face = $this->hanging ? Facing::UP : Facing::DOWN;
		if(!$this->canBeSupportedBy($this->getSide($face), Facing::opposite($face))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	private function canBeSupportedBy(Block $block, int $face) : bool{
		return $block->getSupportType($face)->hasCenterSupport();
	}
}
