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
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Vine extends Transparent{
	const FLAG_SOUTH = 0x01;
	const FLAG_WEST = 0x02;
	const FLAG_NORTH = 0x04;
	const FLAG_EAST = 0x08;

	protected $id = self::VINE;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function isSolid() : bool{
		return false;
	}

	public function getName() : string{
		return "Vines";
	}

	public function getHardness() : float{
		return 0.2;
	}

	public function canPassThrough() : bool{
		return true;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function canClimb() : bool{
		return true;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void{
		$entity->resetFallDistance();
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{

		$f1 = 1;
		$f2 = 1;
		$f3 = 1;
		$f4 = 0;
		$f5 = 0;
		$f6 = 0;

		$flag = $this->meta > 0;

		if(($this->meta & self::FLAG_WEST) > 0){
			$f4 = max($f4, 0.0625);
			$f1 = 0;
			$f2 = 0;
			$f5 = 1;
			$f3 = 0;
			$f6 = 1;
			$flag = true;
		}

		if(($this->meta & self::FLAG_EAST) > 0){
			$f1 = min($f1, 0.9375);
			$f4 = 1;
			$f2 = 0;
			$f5 = 1;
			$f3 = 0;
			$f6 = 1;
			$flag = true;
		}

		if(($this->meta & self::FLAG_SOUTH) > 0){
			$f3 = min($f3, 0.9375);
			$f6 = 1;
			$f1 = 0;
			$f4 = 1;
			$f2 = 0;
			$f5 = 1;
			$flag = true;
		}

		if(!$flag and $this->getSide(Vector3::SIDE_UP)->isSolid()){
			$f2 = min($f2, 0.9375);
			$f5 = 1;
			$f1 = 0;
			$f4 = 1;
			$f3 = 0;
			$f6 = 1;
		}

		return new AxisAlignedBB(
			$this->x + $f1,
			$this->y + $f2,
			$this->z + $f3,
			$this->x + $f4,
			$this->y + $f5,
			$this->z + $f6
		);
	}


	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $facePos, Player $player = null) : bool{
		//TODO: multiple sides
		if($blockClicked->isSolid()){
			$faces = [
				2 => self::FLAG_SOUTH,
				3 => self::FLAG_NORTH,
				4 => self::FLAG_EAST,
				5 => self::FLAG_WEST
			];
			if(isset($faces[$face])){
				$this->meta = $faces[$face];
				$this->getLevel()->setBlock($blockReplace, $this, true, true);

				return true;
			}
		}

		return false;
	}

	public function onUpdate(int $type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$sides = [
				1 => 3,
				2 => 4,
				4 => 2,
				8 => 5
			];

			if(!isset($sides[$this->meta])){
				return false; //TODO: remove this once placing on multiple sides is supported (these are bitflags, not actual meta values
			}

			if(!$this->getSide($sides[$this->meta])->isSolid()){ //Replace with common break method
				$this->level->useBreakOn($this);
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}elseif($type === Level::BLOCK_UPDATE_RANDOM){
			//TODO: vine growth
		}

		return false;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function getDrops(Item $item) : array{
		if($item->isShears()){
			return parent::getDrops($item);
		}

		return [];
	}

	public function getToolType() : int{
		return Tool::TYPE_AXE;
	}
}