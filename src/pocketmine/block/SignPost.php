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
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Tile;

class SignPost extends Transparent{

	protected $id = self::SIGN_POST;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 1;
	}

	public function isSolid(){
		return false;
	}

	public function getName(){
		return "Sign Post";
	}

	public function getBoundingBox(){
		return null;
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face !== 0){
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::SIGN),
				new IntTag("x", $block->x),
				new IntTag("y", $block->y),
				new IntTag("z", $block->z),
				new StringTag("Text1", ""),
				new StringTag("Text2", ""),
				new StringTag("Text3", ""),
				new StringTag("Text4", "")
			]);

			if($player !== null){
				$nbt->Creator = new StringTag("Creator", $player->getRawUniqueId());
			}

			if($item->hasCustomBlockData()){
				foreach($item->getCustomBlockData() as $key => $v){
					$nbt->{$key} = $v;
				}
			}

			if($face === 1){
				$this->meta = floor((($player->yaw + 180) * 16 / 360) + 0.5) & 0x0f;
				$this->getLevel()->setBlock($block, $this, true);
			}else{
				$this->meta = $face;
				$this->getLevel()->setBlock($block, new WallSign($this->meta), true);
			}

			Tile::createTile(Tile::SIGN, $this->getLevel(), $nbt);

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(Vector3::SIDE_DOWN)->getId() === self::AIR){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function getDrops(Item $item){
		return [
			[Item::SIGN, 0, 1],
		];
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}
}