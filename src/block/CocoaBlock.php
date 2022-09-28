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

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\block\utils\TreeType;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function mt_rand;

class CocoaBlock extends Transparent{
	use HorizontalFacingTrait;

	public const MAX_AGE = 2;

	protected int $age = 0;

	protected function writeStateToMeta() : int{
		return BlockDataSerializer::writeLegacyHorizontalFacing(Facing::opposite($this->facing)) | ($this->age << 2);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = Facing::opposite(BlockDataSerializer::readLegacyHorizontalFacing($stateMeta & 0x03));
		$this->age = BlockDataSerializer::readBoundedInt("age", $stateMeta >> 2, 0, self::MAX_AGE);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getAge() : int{ return $this->age; }

	/** @return $this */
	public function setAge(int $age) : self{
		if($age < 0 || $age > self::MAX_AGE){
			throw new \InvalidArgumentException("Age must be in range 0 ... " . self::MAX_AGE);
		}
		$this->age = $age;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [
			AxisAlignedBB::one()
				->squash(Facing::axis(Facing::rotateY($this->facing, true)), (6 - $this->age) / 16) //sides
				->trim(Facing::DOWN, (7 - $this->age * 2) / 16)
				->trim(Facing::UP, 0.25)
				->trim(Facing::opposite($this->facing), 1 / 16) //gap between log and pod
				->trim($this->facing, (11 - $this->age * 2) / 16) //outward face
		];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	private function canAttachTo(Block $block) : bool{
		return $block instanceof Wood && $block->getTreeType()->equals(TreeType::JUNGLE());
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(Facing::axis($face) !== Axis::Y && $this->canAttachTo($blockClicked)){
			$this->facing = $face;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof Fertilizer && $this->grow()){
			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canAttachTo($this->getSide(Facing::opposite($this->facing)))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(mt_rand(1, 5) === 1){
			$this->grow();
		}
	}

	private function grow() : bool{
		if($this->age < self::MAX_AGE){
			$block = clone $this;
			$block->age++;
			$ev = new BlockGrowEvent($this, $block);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->position->getWorld()->setBlock($this->position, $ev->getNewState());
				return true;
			}
		}
		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaItems::COCOA_BEANS()->setCount($this->age === self::MAX_AGE ? mt_rand(2, 3) : 1)
		];
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return VanillaItems::COCOA_BEANS();
	}
}
