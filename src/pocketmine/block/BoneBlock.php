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
use pocketmine\math\Vector3;
use pocketmine\Player;

class BoneBlock extends Solid{

	public function place(Item $item, Block $block, Block $target, int $face, float $fx, float $fy, float $fz, Player $player = null) : bool{
		$faces = [
			Vector3::SIDE_DOWN => 0,
			Vector3::SIDE_WEST => 0x04,
			Vector3::SIDE_NORTH => 0x08
		];
		$this->meta = $faces[$face & ~0x01];
		return $block->getLevel()->setBlock($block, $this, true, true);
	}
}