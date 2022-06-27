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

use pocketmine\block\utils\BlockDataReader;
use pocketmine\block\utils\BlockDataWriter;
use pocketmine\data\bedrock\CoralTypeIdMap;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function atan2;
use function rad2deg;

final class FloorCoralFan extends BaseCoral{
	private int $axis = Axis::X;

	public function asItem() : Item{
		//TODO: HACK! workaround dead flag being lost when broken / blockpicked (original impl only uses first ID)
		return ItemFactory::getInstance()->get(
			$this->dead ? ItemIds::CORAL_FAN_DEAD : ItemIds::CORAL_FAN,
			$this->writeStateToItemMeta()
		);
	}

	protected function writeStateToItemMeta() : int{
		return CoralTypeIdMap::getInstance()->toId($this->coralType);
	}

	public function getRequiredStateDataBits() : int{ return parent::getRequiredStateDataBits() + 1; }

	protected function decodeState(BlockDataReader $r) : void{
		parent::decodeState($r);
		$this->axis = $r->readHorizontalAxis();
	}

	protected function encodeState(BlockDataWriter $w) : void{
		parent::encodeState($w);
		$w->writeHorizontalAxis($this->axis);
	}

	public function getAxis() : int{ return $this->axis; }

	/** @return $this */
	public function setAxis(int $axis) : self{
		if($axis !== Axis::X && $axis !== Axis::Z){
			throw new \InvalidArgumentException("Axis must be X or Z only");
		}
		$this->axis = $axis;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedBy($tx->fetchBlock($blockReplace->getPosition()->down()))){
			return false;
		}
		if($player !== null){
			$playerBlockPos = $player->getPosition()->floor();
			$directionVector = $blockReplace->getPosition()->subtractVector($playerBlockPos)->normalize();
			$angle = rad2deg(atan2($directionVector->getZ(), $directionVector->getX()));

			if($angle <= 45 || 315 <= $angle || (135 <= $angle && $angle <= 225)){
				//TODO: This produces Z axis 75% of the time, because any negative angle will produce Z axis.
				//This is a bug in vanilla. https://bugs.mojang.com/browse/MCPE-125311
				$this->axis = Axis::Z;
			}
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$world = $this->position->getWorld();
		if(!$this->canBeSupportedBy($world->getBlock($this->position->down()))){
			$world->useBreakOn($this->position);
		}else{
			parent::onNearbyBlockChange();
		}
	}

	private function canBeSupportedBy(Block $block) : bool{
		return $block->getSupportType(Facing::UP)->hasCenterSupport();
	}

}
