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

class RedMushroomBlock extends Solid{

	protected $id = Block::RED_MUSHROOM_BLOCK;

	public function __construct(){

	}

	public function getDamage() : int{
		return parent::getDamage(); // TODO: this is impossible to serialize into 4 bits, so it will have to wait until we implement the new level format
	}

	public function setDamage(int $meta) : void{
		parent::setDamage($meta); // TODO: see above
	}

	public function getName() : string{
		return "Red Mushroom Block";
	}

	public function getHardness() : float{
		return 0.2;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			Item::get(Item::RED_MUSHROOM, 0, mt_rand(0, 2))
		];
	}
}
