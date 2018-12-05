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
use pocketmine\item\TieredTool;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Furnace as TileFurnace;
use pocketmine\tile\Tile;

class Furnace extends Solid{

	protected $itemId = self::FURNACE;

	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $lit = false; //this is set based on the blockID

	public function __construct(){

	}

	public function getId() : int{
		return $this->lit ? Block::BURNING_FURNACE : Block::FURNACE;
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
		return "Furnace";
	}

	public function getHardness() : float{
		return 3.5;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function getLightLevel() : int{
		return $this->lit ? 13 : 0;
	}

	public function isLit() : bool{
		return $this->lit;
	}

	/**
	 * @param bool $lit
	 *
	 * @return $this
	 */
	public function setLit(bool $lit = true) : self{
		$this->lit = $lit;
		return $this;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}
		if(parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			Tile::createTile(Tile::FURNACE, $this->getLevel(), TileFurnace::createNBT($this, $item));
			return true;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$furnace = $this->getLevel()->getTile($this);
			if($furnace instanceof TileFurnace and $furnace->canOpenWith($item->getCustomName())){
				$player->addWindow($furnace->getInventory());
			}
		}

		return true;
	}
}
