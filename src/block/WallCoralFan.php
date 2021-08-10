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
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\data\bedrock\CoralTypeIdMap;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\BlockTransaction;

final class WallCoralFan extends BaseCoral{
	use HorizontalFacingTrait;

	protected BlockIdentifierFlattened $idInfoFlattened;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->idInfoFlattened = $idInfo;
		parent::__construct($idInfo, $name, $breakInfo);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readCoralFacing($stateMeta >> 2);
		$this->dead = ($stateMeta & BlockLegacyMetadata::CORAL_FAN_HANG_FLAG_DEAD) !== 0;

		$coralTypeFlag = $stateMeta & BlockLegacyMetadata::CORAL_FAN_HANG_TYPE_MASK;
		switch($id){
			case $this->idInfoFlattened->getBlockId():
				$this->coralType = $coralTypeFlag === BlockLegacyMetadata::CORAL_FAN_HANG_TUBE ? CoralType::TUBE() : CoralType::BRAIN();
				break;
			case $this->idInfoFlattened->getAdditionalId(0):
				$this->coralType = $coralTypeFlag === BlockLegacyMetadata::CORAL_FAN_HANG2_BUBBLE ? CoralType::BUBBLE() : CoralType::FIRE();
				break;
			case $this->idInfoFlattened->getAdditionalId(1):
				if($coralTypeFlag !== BlockLegacyMetadata::CORAL_FAN_HANG3_HORN){
					throw new InvalidBlockStateException("Invalid CORAL_FAN_HANG3 type");
				}
				$this->coralType = CoralType::HORN();
				break;
			default:
				throw new \LogicException("ID/meta doesn't match any CORAL_FAN_HANG type");
		}
	}

	public function getId() : int{
		if($this->coralType->equals(CoralType::TUBE()) || $this->coralType->equals(CoralType::BRAIN())){
			return $this->idInfoFlattened->getBlockId();
		}elseif($this->coralType->equals(CoralType::BUBBLE()) || $this->coralType->equals(CoralType::FIRE())){
			return $this->idInfoFlattened->getAdditionalId(0);
		}elseif($this->coralType->equals(CoralType::HORN())){
			return $this->idInfoFlattened->getAdditionalId(1);
		}
		throw new AssumptionFailedError("All types of coral should be covered");
	}

	public function writeStateToMeta() : int{
		$coralTypeFlag = (function() : int{
			switch($this->coralType->id()){
				case CoralType::TUBE()->id(): return BlockLegacyMetadata::CORAL_FAN_HANG_TUBE;
				case CoralType::BRAIN()->id(): return BlockLegacyMetadata::CORAL_FAN_HANG_BRAIN;
				case CoralType::BUBBLE()->id(): return BlockLegacyMetadata::CORAL_FAN_HANG2_BUBBLE;
				case CoralType::FIRE()->id(): return BlockLegacyMetadata::CORAL_FAN_HANG2_FIRE;
				case CoralType::HORN()->id(): return BlockLegacyMetadata::CORAL_FAN_HANG3_HORN;
				default: throw new AssumptionFailedError("All types of coral should be covered");
			}
		})();
		return (BlockDataSerializer::writeCoralFacing($this->facing) << 2) | ($this->dead ? BlockLegacyMetadata::CORAL_FAN_HANG_FLAG_DEAD : 0) | $coralTypeFlag;
	}

	protected function writeStateToItemMeta() : int{
		return CoralTypeIdMap::getInstance()->toId($this->coralType);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function asItem() : Item{
		return ItemFactory::getInstance()->get(
			$this->dead ? ItemIds::CORAL_FAN_DEAD : ItemIds::CORAL_FAN,
			$this->writeStateToItemMeta()
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
