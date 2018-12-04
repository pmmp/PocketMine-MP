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

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

class Farmland extends Transparent{

	protected $id = self::FARMLAND;

	/** @var int */
	protected $wetness = 0; //"moisture" blockstate property in PC

	public function __construct(){

	}

	protected function writeStateToMeta() : int{
		return $this->wetness;
	}

	public function readStateFromMeta(int $meta) : void{
		$this->wetness = $meta;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getName() : string{
		return "Farmland";
	}

	public function getHardness() : float{
		return 0.6;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHOVEL;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one(); //TODO: this should be trimmed at the top by 1/16, but MCPE currently treats them as a full block (https://bugs.mojang.com/browse/MCPE-12109)
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::UP)->isSolid()){
			$this->level->setBlock($this, BlockFactory::get(Block::DIRT));
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(!$this->canHydrate()){
			if($this->wetness > 0){
				$this->wetness--;
				$this->level->setBlock($this, $this, false);
			}else{
				$this->level->setBlock($this, BlockFactory::get(Block::DIRT));
			}
		}elseif($this->wetness < 7){
			$this->wetness = 7;
			$this->level->setBlock($this, $this, false);
		}
	}

	protected function canHydrate() : bool{
		//TODO: check rain
		$start = $this->add(-4, 0, -4);
		$end = $this->add(4, 1, 4);
		for($y = $start->y; $y <= $end->y; ++$y){
			for($z = $start->z; $z <= $end->z; ++$z){
				for($x = $start->x; $x <= $end->x; ++$x){
					if($this->level->getBlockAt($x, $y, $z) instanceof Water){
						return true;
					}
				}
			}
		}

		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(Item::DIRT)
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function getPickedItem() : Item{
		return ItemFactory::get(Item::DIRT);
	}
}
