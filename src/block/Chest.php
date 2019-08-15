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

use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Chest extends Transparent{

	/** @var int */
	protected $facing = Facing::NORTH;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(2.5, BlockToolType::AXE));
	}

	protected function writeStateToMeta() : int{
		return BlockDataSerializer::writeHorizontalFacing($this->facing);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readHorizontalFacing($stateMeta);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		//these are slightly bigger than in PC
		return [AxisAlignedBB::one()->contract(0.025, 0, 0.025)->trim(Facing::UP, 0.05)];
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onPostPlace() : void{
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile instanceof TileChest){
			foreach([
				Facing::rotateY($this->facing, true),
				Facing::rotateY($this->facing, false)
			] as $side){
				$c = $this->getSide($side);
				if($c instanceof Chest and $c->isSameType($this) and $c->facing === $this->facing){
					$pair = $this->pos->getWorld()->getTile($c->pos);
					if($pair instanceof TileChest and !$pair->isPaired()){
						$pair->pairWith($tile);
						$tile->pairWith($pair);
						break;
					}
				}
			}
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){

			$chest = $this->pos->getWorld()->getTile($this->pos);
			if($chest instanceof TileChest){
				if(
					!$this->getSide(Facing::UP)->isTransparent() or
					($chest->isPaired() and !$chest->getPair()->getBlock()->getSide(Facing::UP)->isTransparent()) or
					!$chest->canOpenWith($item->getCustomName())
				){
					return true;
				}

				$player->setCurrentWindow($chest->getInventory());
			}
		}

		return true;
	}

	public function getFuelTime() : int{
		return 300;
	}
}
