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
use pocketmine\block\utils\TreeType;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function mt_rand;

class CocoaBlock extends Transparent{

	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var int */
	protected $age = 0;

	protected function writeStateToMeta() : int{
		return Bearing::fromFacing(Facing::opposite($this->facing)) | ($this->age << 2);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = Facing::opposite(BlockDataValidator::readLegacyHorizontalFacing($stateMeta & 0x03));
		$this->age = BlockDataValidator::readBoundedInt("age", $stateMeta >> 2, 0, 2);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getHardness() : float{
		return 0.2;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()
			->squash(Facing::axis(Facing::rotateY($this->facing, true)), (6 - $this->age) / 16) //sides
			->trim(Facing::DOWN, (7 - $this->age * 2) / 16)
			->trim(Facing::UP, 0.25)
			->trim(Facing::opposite($this->facing), 1 / 16) //gap between log and pod
			->trim($this->facing, (11 - $this->age * 2) / 16); //outward face
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(Facing::axis($face) !== Facing::AXIS_Y and $blockClicked instanceof Wood and $blockClicked->getTreeType() === TreeType::JUNGLE()){
			$this->facing = $face;
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->age < 2 and $item instanceof Fertilizer){
			$this->age++;
			$this->level->setBlock($this, $this);

			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		$side = $this->getSide(Facing::opposite($this->facing));
		if(!($side instanceof Wood) or $side->getTreeType() !== TreeType::JUNGLE()){
			$this->level->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->age < 2 and mt_rand(1, 5) === 1){
			$this->age++;
			$this->level->setBlock($this, $this);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(Item::DYE, 3, $this->age === 2 ? mt_rand(2, 3) : 1)
		];
	}

	public function getPickedItem() : Item{
		return ItemFactory::get(Item::DYE, 3); //cocoa beans
	}
}
