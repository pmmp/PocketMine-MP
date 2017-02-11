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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Skull;
use pocketmine\tile\Tile;

class SkullBlock extends Transparent{
	
	const SKELETON = 0;
	const WITHER_SKELETON = 1;
	const ZOMBIE_HEAD = 2;
	const STEVE_HEAD = 3;
	const CREEPER_HEAD = 4;

	protected $id = self::SKULL_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() {
		return 1;
	}
	
	public function isHelmet(){
		return true;
	}

	public function isSolid(){
		return false;
	}

	public function getBoundingBox(){
		return new AxisAlignedBB(
			$this->x - 0.75,
			$this->y - 0.5,
			$this->z - 0.75,
			$this->x + 0.75,
			$this->y + 0.5,
			$this->z + 0.75
		);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($face !== 0 && $fy > 0.5 && $target->getId() !== self::SKULL_BLOCK && !$down instanceof SkullBlock){
			$this->getLevel()->setBlock($block, Block::get(Block::SKULL_BLOCK, 0), true, true);
			if($face === 1){
				$rot = new ByteTag("Rot", floor(($player->yaw * 16 / 360) + 0.5) & 0x0F);
			}else{
				$rot = new ByteTag("Rot", 0);
			}
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::SKULL),
				new IntTag("x", $block->x),
				new IntTag("y", $block->y),
				new IntTag("z", $block->z),
				new ByteTag("SkullType", $item->getDamage()),
				$rot
			]);
			
			if($item->hasCustomBlockData()){
			    foreach($item->getCustomBlockData() as $key => $v){
				    $nbt->{$key} = $v;
			    }
			}

			$chunk = $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4);
			$pot = Tile::createTile(Tile::SKULL, $chunk, $nbt);
			$this->getLevel()->setBlock($block, Block::get(Block::SKULL_BLOCK, $face), true, true);
			return true;
		}
		return false;
	}

	public function getResistance(){
		return 5;
	}

	public function getName() : string{
		static $names = [
			0 => "Skeleton Skull",
			1 => "Wither Skeleton Skull",
			2 => "Zombie Head",
			3 => "Head",
			4 => "Creeper Head"
		];
		return $names[$this->meta & 0x04];
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, true);
		return true;
	}

	public function getDrops(Item $item) : array {
		/** @var Skull $tile */
		if($this->getLevel()!=null && (($tile = $this->getLevel()->getTile($this)) instanceof Skull)){
			return [[Item::SKULL, $tile->getSkullType(), 1]];
		}else
			return [[Item::SKULL, 0, 1]];
	}
}
