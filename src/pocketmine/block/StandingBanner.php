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

use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\ListTag;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Tile;

class StandingBanner extends Transparent{

	protected $id = self::STANDING_BANNER;

	protected $itemId = Item::BANNER;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 1;
	}

	public function isSolid() : bool{
		return false;
	}

	public function getName() : string{
		return "Standing Banner";
	}

	protected function recalculateBoundingBox(){
		return null;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $facePos, Player $player = null) : bool{
		if($face !== Vector3::SIDE_DOWN){
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::BANNER),
				new IntTag("x", $blockReplace->x),
				new IntTag("y", $blockReplace->y),
				new IntTag("z", $blockReplace->z),
				$item->getNamedTag()->Base ?? new IntTag("Base", $item->getDamage() & 0x0f),
			]);

			if($face === Vector3::SIDE_UP){
				$this->meta = floor((($player->yaw + 180) * 16 / 360) + 0.5) & 0x0f;
				$this->getLevel()->setBlock($blockReplace, $this, true);
			}else{
				$this->meta = $face;
				$this->getLevel()->setBlock($blockReplace, new WallBanner($this->meta), true);
			}
			if(isset($item->getNamedTag()->Patterns) and ($item->getNamedTag()->Patterns instanceof ListTag)){
				$nbt->Patterns = $item->getNamedTag()->Patterns;
			}
			Tile::createTile(Tile::BANNER, $this->getLevel(), $nbt);
			return true;
		}

		return false;
	}

	public function onUpdate(int $type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(Vector3::SIDE_DOWN)->getId() === self::AIR){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function getToolType() : int{
		return Tool::TYPE_AXE;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function getDrops(Item $item) : array{
		return [];
	}

	public function onBreak(Item $item, Player $player = null) : bool{
		if(($tile = $this->level->getTile($this)) !== null) {
			$this->level->dropItem($this, ItemFactory::get(Item::BANNER)->setNamedTag($tile->getCleanedNBT()));
		}
		return parent::onBreak($item, $player);
	}
}