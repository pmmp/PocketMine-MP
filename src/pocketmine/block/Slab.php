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
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Slab extends Transparent{
	/** @var int */
	protected $doubleId;

	/** @var bool */
	protected $top = false;

	public function __construct(int $id, int $doubleId, int $variant = 0, ?string $name = null){
		parent::__construct($id, $variant, $name . " Slab");
		$this->doubleId = $doubleId;
	}

	protected function writeStateToMeta() : int{
		return ($this->top ? 0x08 : 0);
	}

	public function readStateFromMeta(int $meta) : void{
		$this->top = ($meta & 0x08) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1000;
	}

	public function getDoubleSlabId() : int{
		return $this->doubleId;
	}

	protected function getDouble() : Block{
		return BlockFactory::get($this->doubleId, $this->variant);
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		if(parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock)){
			return true;
		}

		if($blockReplace instanceof Slab and $blockReplace->isSameType($this)){
			if($blockReplace->top){ //Trying to combine with top slab
				return $clickVector->y <= 0.5 or (!$isClickedBlock and $face === Facing::UP);
			}else{
				return $clickVector->y >= 0.5 or (!$isClickedBlock and $face === Facing::DOWN);
			}
		}

		return false;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		/* note these conditions can't be merged, since one targets clicked and the other replace */

		if($blockClicked instanceof Slab and $blockClicked->isSameType($this) and (
			($face === Facing::DOWN and $blockClicked->top) or //Bottom face of top slab
			($face === Facing::UP and !$blockClicked->top) //Top face of bottom slab
		)){
			return $this->level->setBlock($blockClicked, $this->getDouble());
		}

		if($blockReplace instanceof Slab and $blockReplace->isSameType($this) and (
			($blockReplace->top and $clickVector->y <= 0.5) or
			(!$blockReplace->top and $clickVector->y >= 0.5)
		)){
			//Clicked in empty half of existing slab
			return $this->level->setBlock($blockReplace, $this->getDouble());
		}

		$this->top = ($face !== Facing::UP && $clickVector->y > 0.5) || $face === Facing::DOWN;

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->trim($this->top ? Facing::DOWN : Facing::UP, 0.5);
	}
}
