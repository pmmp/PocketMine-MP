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
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Slab extends Transparent{
	protected SlabType $slabType = SlabType::BOTTOM;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo){
		parent::__construct($idInfo, $name . " Slab", $typeInfo);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->enum($this->slabType);
	}

	public function isTransparent() : bool{
		return $this->slabType !== SlabType::DOUBLE;
	}

	/**
	 * Returns the type of slab block.
	 */
	public function getSlabType() : SlabType{
		return $this->slabType;
	}

	/**
	 * @return $this
	 */
	public function setSlabType(SlabType $slabType) : self{
		$this->slabType = $slabType;
		return $this;
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		if(parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock)){
			return true;
		}

		if($blockReplace instanceof Slab && $blockReplace->slabType !== SlabType::DOUBLE && $blockReplace->hasSameTypeId($this)){
			if($blockReplace->slabType === SlabType::TOP){ //Trying to combine with top slab
				return $clickVector->y <= 0.5 || (!$isClickedBlock && $face === Facing::UP);
			}else{
				return $clickVector->y >= 0.5 || (!$isClickedBlock && $face === Facing::DOWN);
			}
		}

		return false;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($blockReplace instanceof Slab && $blockReplace->slabType !== SlabType::DOUBLE && $blockReplace->hasSameTypeId($this) && (
			($blockReplace->slabType === SlabType::TOP && ($clickVector->y <= 0.5 || $face === Facing::UP)) ||
			($blockReplace->slabType === SlabType::BOTTOM && ($clickVector->y >= 0.5 || $face === Facing::DOWN))
		)){
			//Clicked in empty half of existing slab
			$this->slabType = SlabType::DOUBLE;
		}else{
			$this->slabType = (($face !== Facing::UP && $clickVector->y > 0.5) || $face === Facing::DOWN) ? SlabType::TOP : SlabType::BOTTOM;
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		if($this->slabType === SlabType::DOUBLE){
			return [AxisAlignedBB::one()];
		}
		return [AxisAlignedBB::one()->trim($this->slabType === SlabType::TOP ? Facing::DOWN : Facing::UP, 0.5)];
	}

	public function getSupportType(int $facing) : SupportType{
		if($this->slabType === SlabType::DOUBLE){
			return SupportType::FULL;
		}elseif(($facing === Facing::UP && $this->slabType === SlabType::TOP) || ($facing === Facing::DOWN && $this->slabType === SlabType::BOTTOM)){
			return SupportType::FULL;
		}
		return SupportType::NONE;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [$this->asItem()->setCount($this->slabType === SlabType::DOUBLE ? 2 : 1)];
	}
}
