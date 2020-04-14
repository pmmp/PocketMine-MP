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
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\FlowerPot as TileFlowerPot;
use pocketmine\tile\Tile;

class FlowerPot extends Flowable{

	public const STATE_EMPTY = 0;
	public const STATE_FULL = 1;

	protected $id = self::FLOWER_POT_BLOCK;
	protected $itemId = Item::FLOWER_POT;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Flower Pot";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return new AxisAlignedBB(
			$this->x + 0.3125,
			$this->y,
			$this->z + 0.3125,
			$this->x + 0.6875,
			$this->y + 0.375,
			$this->z + 0.6875
		);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			return false;
		}

		$this->getLevelNonNull()->setBlock($blockReplace, $this, true, true);
		Tile::createTile(Tile::FLOWER_POT, $this->getLevelNonNull(), TileFlowerPot::createNBT($this, $face, $item, $player));
		return true;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			$this->getLevelNonNull()->useBreakOn($this);
		}
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$pot = $this->getLevelNonNull()->getTile($this);
		if(!($pot instanceof TileFlowerPot)){
			return false;
		}
		if(!$pot->canAddItem($item)){
			return true;
		}

		$this->setDamage(self::STATE_FULL); //specific damage value is unnecessary, it just needs to be non-zero to show an item.
		$this->getLevelNonNull()->setBlock($this, $this, true, false);
		$pot->setItem($item->pop());

		return true;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$items = parent::getDropsForCompatibleTool($item);

		$tile = $this->getLevelNonNull()->getTile($this);
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
