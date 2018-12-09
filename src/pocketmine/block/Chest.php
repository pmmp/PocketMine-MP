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
use pocketmine\tile\Chest as TileChest;
use pocketmine\tile\Tile;

class Chest extends Transparent{

	protected $id = self::CHEST;

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

	public function getHardness() : float{
		return 2.5;
	}

	public function getName() : string{
		return "Chest";
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		//these are slightly bigger than in PC
		return AxisAlignedBB::one()->contract(0.025, 0, 0.025)->trim(Facing::UP, 0.05);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$chest = null;
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}

		foreach([
			Facing::rotateY($player->getHorizontalFacing(), false),
			Facing::rotateY($player->getHorizontalFacing(), true)
		] as $side){
			$c = $this->getSide($side);
			if($c instanceof Chest and $c->isSameType($this) and $c->facing === $this->facing){
				$tile = $this->getLevel()->getTile($c);
				if($tile instanceof TileChest and !$tile->isPaired()){
					$chest = $tile;
					break;
				}
			}
		}

		if(parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			$tile = Tile::createTile(Tile::CHEST, $this->getLevel(), TileChest::createNBT($this, $item));

			if($chest instanceof TileChest and $tile instanceof TileChest){
				$chest->pairWith($tile);
				$tile->pairWith($chest);
			}

			return true;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){

			$chest = $this->getLevel()->getTile($this);
			if($chest instanceof TileChest){
				if(
					!$this->getSide(Facing::UP)->isTransparent() or
					($chest->isPaired() and !$chest->getPair()->getBlock()->getSide(Facing::UP)->isTransparent()) or
					!$chest->canOpenWith($item->getCustomName())
				){
					return true;
				}

				$player->addWindow($chest->getInventory());
			}
		}

		return true;
	}

	public function getFuelTime() : int{
		return 300;
	}
}
