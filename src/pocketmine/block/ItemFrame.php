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

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\ItemFrame as TileItemFrame;
use function lcg_value;

class ItemFrame extends Flowable{

	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $hasMap = false; //makes frame appear large if set

	protected function writeStateToMeta() : int{
		return (5 - $this->facing) | ($this->hasMap ? 0x04 : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataValidator::readHorizontalFacing(5 - ($stateMeta & 0x03));
		$this->hasMap = ($stateMeta & 0x04) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function onActivate(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$tile = $this->level->getTile($this);
		if($tile instanceof TileItemFrame){
			if($tile->hasItem()){
				$tile->setItemRotation(($tile->getItemRotation() + 1) % 8);
			}elseif(!$item->isNull()){
				$tile->setItem($item->pop());
			}
		}

		return true;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide(Facing::opposite($this->facing))->isSolid()){
			$this->level->useBreakOn($this);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($face === Facing::DOWN or $face === Facing::UP or !$blockClicked->isSolid()){
			return false;
		}

		$this->facing = $face;

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$drops = parent::getDropsForCompatibleTool($item);

		$tile = $this->level->getTile($this);
		if($tile instanceof TileItemFrame){
			$tileItem = $tile->getItem();
			if(lcg_value() <= $tile->getItemDropChance() and !$tileItem->isNull()){
				$drops[] = $tileItem;
			}
		}

		return $drops;
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function getHardness() : float{
		return 0.25;
	}
}
