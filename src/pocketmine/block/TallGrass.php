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
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class TallGrass extends Flowable{

	protected $id = self::TALL_GRASS;

	public function __construct(int $meta = 1){
		$this->meta = $meta;
	}

	public function canBeReplaced() : bool{
		return true;
	}

	public function getName() : string{
		static $names = [
			0 => "Dead Shrub",
			1 => "Tall Grass",
			2 => "Fern"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() === self::GRASS){
			$this->getLevel()->setBlock($blockReplace, $this, true);

			return true;
		}

		return false;
	}


	public function onUpdate(int $type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(Vector3::SIDE_DOWN)->isTransparent() === true){ //Replace with common break method
				$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true, true);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHEARS;
	}

	public function getToolHarvestLevel() : int{
		return 1;
	}

	public function getDrops(Item $item) : array{
		if($this->isCompatibleWithTool($item)){
			return parent::getDrops($item);
		}

		if(mt_rand(0, 15) === 0){
			return [
				ItemFactory::get(Item::WHEAT_SEEDS)
			];
		}

		return [];
	}

}
