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
use pocketmine\math\Vector3;
use pocketmine\Player;
use function mt_rand;

class DeadBush extends Flowable{

	protected $id = self::DEAD_BUSH;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Dead Bush";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if(!$this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			$this->getLevelNonNull()->useBreakOn($this);
		}
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHEARS;
	}

	public function getToolHarvestLevel() : int{
		return 1;
	}

	public function getDrops(Item $item) : array{
		if(!$this->isCompatibleWithTool($item)){
			return [
				ItemFactory::get(Item::STICK, 0, mt_rand(0, 2))
			];
		}

		return parent::getDrops($item);
	}

	public function getFlameEncouragement() : int{
		return 60;
	}

	public function getFlammability() : int{
		return 100;
	}
}
