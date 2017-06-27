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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\object\PaintingMotive;
use pocketmine\math\Vector2;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

class Painting extends Item{

	public function onClickBlock(Player $player, Block $block, Block $blockClicked, int $face, float $fx, float $fy, float $fz) : bool{
		if(!$blockClicked->isTransparent() and $face > 1 and !$block->isSolid()){

			//TODO: pick motive based on space available, check space is available to place the painting
			$motive = PaintingMotive::pickRandomMotive();

			try{
				$direction = Vector2::vec3SideToDirection($face);
			}catch(\InvalidArgumentException $e){
				return false;
			}

			$nbt = new CompoundTag("", [
				new ListTag("Pos", [
					new DoubleTag("", $block->getX()),
					new DoubleTag("", $block->getY()),
					new DoubleTag("", $block->getZ())
				]),
				new ListTag("Motion", [
					new DoubleTag("", 0),
					new DoubleTag("", 0),
					new DoubleTag("", 0)
				]),
				new ListTag("Rotation", [
					new FloatTag("", $direction * 90),
					new FloatTag("", 0)
				]),
				new IntTag("TileX", $blockClicked->getFloorX()),
				new IntTag("TileY", $blockClicked->getFloorY()),
				new IntTag("TileZ", $blockClicked->getFloorZ()),
				new ByteTag("Direction", $direction),
				new StringTag("Motive", $motive->getName())
			]);

			$entity = Entity::createEntity("Painting", $block->getLevel(), $nbt);

			if($entity instanceof Entity){
				--$this->count;
				$entity->spawnToAll();
				return true;
			}else{
				$block->getLevel()->getServer()->getLogger()->debug("No painting entity created");
			}
		}

		return false;
	}

}