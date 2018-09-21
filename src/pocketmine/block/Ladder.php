<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
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

class Ladder extends Transparent{

	protected $id = self::LADDER;

	/** @var int */
	protected $facing = Facing::NORTH;

	public function __construct(){

	}

	protected function writeStateToMeta() : int{
		return $this->facing;
	}

	public function readStateFromMeta(int $meta) : void{
		$this->facing = $meta;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getName() : string{
		return "Ladder";
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function isSolid() : bool{
		return false;
	}

	public function getHardness() : float{
		return 0.4;
	}

	public function canClimb() : bool{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void{
		$entity->resetFallDistance();
		$entity->onGround = true;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		$f = 0.1875;

		$minX = $minZ = 0;
		$maxX = $maxZ = 1;

		if($this->facing === Facing::NORTH){
			$minZ = 1 - $f;
		}elseif($this->facing === Facing::SOUTH){
			$maxZ = $f;
		}elseif($this->facing === Facing::WEST){
			$minX = 1 - $f;
		}elseif($this->facing === Facing::EAST){
			$maxX = $f;
		}

		return new AxisAlignedBB(
			$minX,
			0,
			$minZ,
			$maxX,
			1,
			$maxZ
		);
	}


	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if(!$blockClicked->isTransparent() and Facing::axis($face) !== Facing::AXIS_Y){
			$this->facing = $face;
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide(Facing::opposite($this->facing))->isSolid()){ //Replace with common break method
			$this->level->useBreakOn($this);
		}
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}
}
