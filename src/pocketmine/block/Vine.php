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

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function array_intersect_key;
use function array_keys;
use function count;

class Vine extends Flowable{
	private const FLAG_SOUTH = 0x01;
	private const FLAG_WEST = 0x02;
	private const FLAG_NORTH = 0x04;
	private const FLAG_EAST = 0x08;

	/** @var bool[] */
	protected $faces = [];

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(0.2, BlockToolType::TYPE_AXE));
	}

	protected function writeStateToMeta() : int{
		return
			(isset($this->faces[Facing::SOUTH]) ? self::FLAG_SOUTH : 0) |
			(isset($this->faces[Facing::WEST]) ? self::FLAG_WEST : 0) |
			(isset($this->faces[Facing::NORTH]) ? self::FLAG_NORTH : 0) |
			(isset($this->faces[Facing::EAST]) ? self::FLAG_EAST : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->setFaceFromMeta($stateMeta, self::FLAG_SOUTH, Facing::SOUTH);
		$this->setFaceFromMeta($stateMeta, self::FLAG_WEST, Facing::WEST);
		$this->setFaceFromMeta($stateMeta, self::FLAG_NORTH, Facing::NORTH);
		$this->setFaceFromMeta($stateMeta, self::FLAG_EAST, Facing::EAST);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	private function setFaceFromMeta(int $meta, int $flag, int $face) : void{
		if(($meta & $flag) !== 0){
			$this->faces[$face] = true;
		}else{
			unset($this->faces[$face]);
		}
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function canClimb() : bool{
		return true;
	}

	public function canBeReplaced() : bool{
		return true;
	}

	public function onEntityInside(Entity $entity) : void{
		$entity->resetFallDistance();
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		switch(count($this->faces)){
			case 0:
				return AxisAlignedBB::one()->trim(Facing::DOWN, 15 / 16);
			case 1:
				return AxisAlignedBB::one()->trim(Facing::opposite(array_keys($this->faces)[0]), 15 / 16);
			default:
				return AxisAlignedBB::one();
		}
	}

	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$blockClicked->isSolid() or Facing::axis($face) === Facing::AXIS_Y){
			return false;
		}

		$this->faces = $blockReplace instanceof Vine ? $blockReplace->faces : [];
		$this->faces[Facing::opposite($face)] = true;

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$changed = false;

		$up = $this->getSide(Facing::UP);
		//check which faces have corresponding vines in the block above
		$supportedFaces = $up instanceof Vine ? array_intersect_key($this->faces, $up->faces) : [];

		foreach($this->faces as $face => $bool){
			if(!isset($supportedFaces[$face]) and !$this->getSide($face)->isSolid()){
				unset($this->faces[$face]);
				$changed = true;
			}
		}

		if($changed){
			if(empty($this->faces)){
				$this->world->useBreakOn($this);
			}else{
				$this->world->setBlock($this, $this);
			}
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		//TODO: vine growth
	}

	public function getDrops(Item $item) : array{
		if($item->getBlockToolType() & BlockToolType::TYPE_SHEARS){
			return $this->getDropsForCompatibleTool($item);
		}

		return [];
	}

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}
}
