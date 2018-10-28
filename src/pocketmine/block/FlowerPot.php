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
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\FlowerPot as TileFlowerPot;
use pocketmine\tile\Tile;

class FlowerPot extends Flowable{

	protected $id = self::FLOWER_POT_BLOCK;
	protected $itemId = Item::FLOWER_POT;

	/** @var bool */
	protected $occupied = false;

	public function __construct(){

	}

	protected function writeStateToMeta() : int{
		return $this->occupied ? 1 : 0;
	}

	public function readStateFromMeta(int $meta) : void{
		$this->occupied = $meta !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111; //vanilla uses various values, we only care about 1 and 0 for PE
	}

	public function getName() : string{
		return "Flower Pot";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->contract(3 / 16, 0, 3 / 16)->trim(Facing::UP, 5 / 8);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			return false;
		}

		if(parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			Tile::createTile(Tile::FLOWER_POT, $this->getLevel(), TileFlowerPot::createNBT($this, $item));
			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$pot = $this->getLevel()->getTile($this);
		if(!($pot instanceof TileFlowerPot)){
			return false;
		}
		if(!$pot->canAddItem($item)){
			return true;
		}

		$this->occupied = true;
		$this->getLevel()->setBlock($this, $this, false);
		$pot->setItem($item->pop());

		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$items = parent::getDropsForCompatibleTool($item);

		$tile = $this->getLevel()->getTile($this);
		if($tile instanceof TileFlowerPot){
			$item = $tile->getItem();
			if($item->getId() !== Item::AIR){
				$items[] = $item;
			}
		}

		return $items;
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
