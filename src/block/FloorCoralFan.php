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

use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\data\bedrock\CoralTypeIdMap;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Axis;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function atan2;
use function rad2deg;

final class FloorCoralFan extends BaseCoral{

	protected BlockIdentifierFlattened $idInfoFlattened;

	private int $axis = Axis::X;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->idInfoFlattened = $idInfo;
		parent::__construct($idInfo, $name, $breakInfo);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->dead = $id === $this->idInfoFlattened->getSecondId();
		$this->axis = ($stateMeta >> 3) === BlockLegacyMetadata::CORAL_FAN_EAST_WEST ? Axis::X : Axis::Z;
		$coralType = CoralTypeIdMap::getInstance()->fromId($stateMeta & BlockLegacyMetadata::CORAL_FAN_TYPE_MASK);
		if($coralType === null){
			throw new InvalidBlockStateException("No such coral type");
		}
		$this->coralType = $coralType;
	}

	public function getId() : int{
		return $this->dead ? $this->idInfoFlattened->getSecondId() : parent::getId();
	}

	public function asItem() : Item{
		//TODO: HACK! workaround dead flag being lost when broken / blockpicked (original impl only uses first ID)
		return ItemFactory::getInstance()->get(
			$this->dead ? ItemIds::CORAL_FAN_DEAD : ItemIds::CORAL_FAN,
			CoralTypeIdMap::getInstance()->toId($this->coralType)
		);
	}

	protected function writeStateToMeta() : int{
		return (($this->axis === Axis::X ? BlockLegacyMetadata::CORAL_FAN_EAST_WEST : BlockLegacyMetadata::CORAL_FAN_NORTH_SOUTH) << 3) |
			CoralTypeIdMap::getInstance()->toId($this->coralType);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getNonPersistentStateBitmask() : int{
		return 0b1000;
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
		if(!$tx->fetchBlock($blockReplace->getPos()->down())->isSolid()){
			return false;
		}
		if($player !== null){
			$playerBlockPos = $player->getPosition()->floor();
			$directionVector = $blockReplace->getPos()->subtractVector($playerBlockPos)->normalize();
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
		$world = $this->pos->getWorld();
		if(!$world->getBlock($this->pos->down())->isSolid()){
			$world->useBreakOn($this->pos);
		}else{
			parent::onNearbyBlockChange();
		}
	}

}
