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
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Skull as TileSkull;
use pocketmine\tile\Tile;

class Skull extends Flowable{

	protected $id = self::SKULL_BLOCK;

	/** @var int */
	protected $facing = Facing::NORTH;

	protected $type = TileSkull::TYPE_SKELETON;
	/** @var int */
	protected $rotation = 0; //TODO: split this into floor skull and wall skull handling

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

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->level->getTile($this);
		if($tile instanceof TileSkull){
			$this->type = $tile->getType();
			$this->rotation = $tile->getRotation();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = Tile::createTile(Tile::SKULL, $this->getLevel(), TileSkull::createNBT($this));
		if($tile instanceof TileSkull){
			$tile->setRotation($this->rotation);
			$tile->setType($this->type);
		}
	}

	public function getHardness() : float{
		return 1;
	}

	public function getName() : string{
		return "Mob Head";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		//TODO: different bounds depending on attached face
		return AxisAlignedBB::one()->contract(0.25, 0, 0.25)->trim(Facing::UP, 0.5);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($face === Facing::DOWN){
			return false;
		}

		$this->facing = $face;
		$this->type = $item->getDamage(); //TODO: replace this with a proper variant getter
		if($player !== null and $face === Facing::UP){
			$this->rotation = ((int) floor(($player->yaw * 16 / 360) + 0.5)) & 0xf;
		}
		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getItem() : Item{
		return ItemFactory::get(Item::SKULL, $this->type);
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
