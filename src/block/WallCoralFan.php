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
use pocketmine\data\bedrock\CoralTypeIdMap;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

final class WallCoralFan extends BaseCoral{
	use HorizontalFacingTrait;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readCoralFacing($stateMeta >> 2);
		$this->dead = ($stateMeta & BlockLegacyMetadata::CORAL_FAN_HANG_FLAG_DEAD) !== 0;
	}

	public function writeStateToMeta() : int{
		return (BlockDataSerializer::writeCoralFacing($this->facing) << 2) | ($this->dead ? BlockLegacyMetadata::CORAL_FAN_HANG_FLAG_DEAD : 0);
	}

	public function getStateBitmask() : int{
		return 0b1110;
	}

	public function asItem() : Item{
		return ItemFactory::getInstance()->get(
			$this->dead ? ItemIds::CORAL_FAN_DEAD : ItemIds::CORAL_FAN,
			CoralTypeIdMap::getInstance()->toId($this->coralType)
		);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$axis = Facing::axis($face);
		if(($axis !== Axis::X && $axis !== Axis::Z) || !$blockClicked->isSolid()){
			return false;
		}
		$this->facing = $face;
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$world = $this->pos->getWorld();
		if(!$world->getBlock($this->pos->getSide(Facing::opposite($this->facing)))->isSolid()){
			$world->useBreakOn($this->pos);
		}else{
			parent::onNearbyBlockChange();
		}
	}
}
