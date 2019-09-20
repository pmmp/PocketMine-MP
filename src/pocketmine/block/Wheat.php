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
use function mt_rand;

class Wheat extends Crops{

	protected $id = self::WHEAT_BLOCK;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Wheat Block";
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		if($this->meta >= 0x07){
			return [
				ItemFactory::get(Item::WHEAT),
				ItemFactory::get(Item::WHEAT_SEEDS, 0, mt_rand(0, 3))
			];
		}else{
			return [
				ItemFactory::get(Item::WHEAT_SEEDS)
			];
		}
	}

	public function getPickedItem() : Item{
		return ItemFactory::get(Item::WHEAT_SEEDS);
	}
}
