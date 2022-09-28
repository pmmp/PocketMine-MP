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

use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class DoublePlant extends Flowable{

	protected bool $top = false;

	protected function writeStateToMeta() : int{
		return ($this->top ? BlockLegacyMetadata::DOUBLE_PLANT_FLAG_TOP : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->top = ($stateMeta & BlockLegacyMetadata::DOUBLE_PLANT_FLAG_TOP) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1000;
	}

	public function isTop() : bool{ return $this->top; }

	/** @return $this */
	public function setTop(bool $top) : self{
		$this->top = $top;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$id = $blockReplace->getSide(Facing::DOWN)->getId();
		if(($id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT) && $blockReplace->getSide(Facing::UP)->canBeReplaced()){
			$top = clone $this;
			$top->top = true;
			$tx->addBlock($blockReplace->position, $this)->addBlock($blockReplace->position->getSide(Facing::UP), $top);
			return true;
		}

		return false;
	}

	/**
	 * Returns whether this double-plant has a corresponding other half.
	 */
	public function isValidHalfPlant() : bool{
		$other = $this->getSide($this->top ? Facing::DOWN : Facing::UP);

		return (
			$other instanceof DoublePlant &&
			$other->isSameType($this) &&
			$other->top !== $this->top
		);
	}

	public function onNearbyBlockChange() : void{
		if(!$this->isValidHalfPlant() || (!$this->top && $this->getSide(Facing::DOWN)->isTransparent())){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function getDrops(Item $item) : array{
		return $this->top ? parent::getDrops($item) : [];
	}

	public function getAffectedBlocks() : array{
		if($this->isValidHalfPlant()){
			return [$this, $this->getSide($this->top ? Facing::DOWN : Facing::UP)];
		}

		return parent::getAffectedBlocks();
	}

	public function getFlameEncouragement() : int{
		return 60;
	}

	public function getFlammability() : int{
		return 100;
	}
}
