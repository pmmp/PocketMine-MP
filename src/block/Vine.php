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

class Vine extends Flowable{
	private const FLAG_SOUTH = 0x01;
	private const FLAG_WEST = 0x02;
	private const FLAG_NORTH = 0x04;
	private const FLAG_EAST = 0x08;

	protected $id = self::VINE;

	/** @var bool[] */
	protected $faces = [];

	public function __construct(){

	}

	protected function writeStateToMeta() : int{
		return
			(isset($this->faces[Facing::SOUTH]) ? self::FLAG_SOUTH : 0) |
			(isset($this->faces[Facing::WEST]) ? self::FLAG_WEST : 0) |
			(isset($this->faces[Facing::NORTH]) ? self::FLAG_NORTH : 0) |
			(isset($this->faces[Facing::EAST]) ? self::FLAG_EAST : 0);
	}

	public function readStateFromMeta(int $meta) : void{
		$this->setFaceFromMeta($meta, self::FLAG_SOUTH, Facing::SOUTH);
		$this->setFaceFromMeta($meta, self::FLAG_WEST, Facing::WEST);
		$this->setFaceFromMeta($meta, self::FLAG_NORTH, Facing::NORTH);
		$this->setFaceFromMeta($meta, self::FLAG_EAST, Facing::EAST);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	private function setFaceFromMeta(int $meta, int $flag, int $face) : void{
		if(($meta & $flag) !== 0){
			$this->faces[$face] = true;
		}
	}

	public function getName() : string{
		return "Vines";
	}

	public function getHardness() : float{
		return 0.2;
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

	public function onEntityCollide(Entity $entity) : void{
		$entity->resetFallDistance();
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		$minX = 1;
		$minZ = 1;
		$maxX = 0;
		$maxZ = 0;

		$minY = 0;
		$hasSide = false;

		if(isset($this->faces[Facing::WEST])){
			$maxX = max($maxX, 0.0625);
			$minX = 0;
			$minZ = 0;
			$maxZ = 1;
			$hasSide = true;
		}

		if(isset($this->faces[Facing::EAST])){
			$minX = min($minX, 0.9375);
			$maxX = 1;
			$minZ = 0;
			$maxZ = 1;
			$hasSide = true;
		}

		if(isset($this->faces[Facing::SOUTH])){
			$minZ = min($minZ, 0.9375);
			$maxZ = 1;
			$minX = 0;
			$maxX = 1;
			$hasSide = true;
		}

		if(isset($this->faces[Facing::NORTH])){
			$maxZ = max($maxZ, 0.0625);
			$minZ = 0;
			$minX = 0;
			$maxX = 1;
			$hasSide = true;
		}

		if(!$hasSide){
			$minY = 0.9375;
			$minX = 0;
			$maxX = 1;
			$minZ = 0;
			$maxZ = 1;
		}

		return new AxisAlignedBB($minX, $minY, $minZ, $maxX, 1, $maxZ);
	}

	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
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
				$this->level->useBreakOn($this);
			}else{
				$this->level->setBlock($this, $this);
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

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}
}
