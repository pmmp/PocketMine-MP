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
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\FlowerPot as TileFlowerPot;
use pocketmine\tile\Tile;

class FlowerPot extends Flowable{

	const STATE_EMPTY = 0;
	const STATE_FULL = 1;

	protected $id = self::FLOWER_POT_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Flower Pot Block";
	}

	protected function recalculateBoundingBox(){
		return new AxisAlignedBB(
			$this->x + 0.3125,
			$this->y,
			$this->z + 0.3125,
			$this->x + 0.6875,
			$this->y + 0.375,
			$this->z + 0.6875
		);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			return false;
		}

		$this->getLevel()->setBlock($block, $this, true, true);

		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::FLOWER_POT),
			new IntTag("x", $block->x),
			new IntTag("y", $block->y),
			new IntTag("z", $block->z),
			new ShortTag("item", 0),
			new IntTag("mData", 0),
		]);

		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}

		Tile::createTile(Tile::FLOWER_POT, $this->getLevel(), $nbt);
		return true;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isTransparent() === true){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null){
		$pot = $this->getLevel()->getTile($this);
		if(!($pot instanceof TileFlowerPot)){
			return false;
		}
		if(!$pot->canAddItem($item)){
			return true;
		}

		$this->setDamage(self::STATE_FULL); //specific damage value is unnecessary, it just needs to be non-zero to show an item.
		$this->getLevel()->setBlock($this, $this, true, false);
		$pot->setItem($item);

		if($player instanceof Player){
			if($player->isSurvival()){
				$item->setCount($item->getCount() - 1);
				$player->getInventory()->setItemInHand($item->getCount() > 0 ? $item : Item::get(Item::AIR));
			}
		}
		return true;
	}

	public function getDrops(Item $item){
		$items = [[Item::FLOWER_POT, 0, 1]];
		$tile = $this->getLevel()->getTile($this);
		if($tile instanceof TileFlowerPot){
			if(($item = $tile->getItem())->getId() !== Item::AIR){
				$items[] = [$item->getId(), $item->getDamage(), 1];
			}
		}
		return $items;
	}

}