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
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Skull as SkullTile;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;

class Skull extends Flowable{

	protected $id = self::SKULL_BLOCK;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 1;
	}

	public function getName() : string{
		return "Mob Head";
	}

	protected function recalculateBoundingBox(){
		//TODO: different bounds depending on attached face (meta)
		return new AxisAlignedBB(
			$this->x + 0.25,
			$this->y,
			$this->z + 0.25,
			$this->x + 0.75,
			$this->y + 0.5,
			$this->z + 0.75
		);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $facePos, Player $player = null) : bool{
		if($face !== Vector3::SIDE_DOWN){
			$this->meta = $face;

			$rot = 0;
			if($face === Vector3::SIDE_UP and $player !== null){
				$rot = floor(($player->yaw * 16 / 360) + 0.5) & 0x0F;
			}
			$this->getLevel()->setBlock($blockReplace, $this, true);
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::SKULL),
				new ByteTag("SkullType", $item->getDamage()),
				new ByteTag("Rot", $rot),
				new IntTag("x", (int) $this->x),
				new IntTag("y", (int) $this->y),
				new IntTag("z", (int) $this->z)
			]);
			if($item->hasCustomName()){
				$nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
			}
			/** @var Spawnable $tile */
			Tile::createTile("Skull", $this->getLevel(), $nbt);
			return true;
		}
		return false;
	}

	public function getDrops(Item $item) : array{
		$tile = $this->level->getTile($this);
		if($tile instanceof SkullTile){
			return [
				ItemFactory::get(Item::SKULL, $tile->getType(), 1)
			];
		}

		return [];
	}
}