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
use pocketmine\math\AxisAlignedBB;


/**
 * Air block
 */
class Air extends Transparent{

	protected $id = self::AIR;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Air";
	}

	public function canPassThrough() : bool{
		return true;
	}

	public function isBreakable(Item $item) : bool{
		return false;
	}

	public function canBeFlowedInto() : bool{
		return true;
	}

	public function canBeReplaced() : bool{
		return true;
	}

	public function canBePlaced() : bool{
		return false;
	}

	public function isSolid() : bool{
		return false;
	}

	public function getBoundingBox() : ?AxisAlignedBB{
		return null;
	}

	public function getCollisionBoxes() : array{
		return [];
	}

	public function getHardness() : float{
		return -1;
	}

	public function getBlastResistance() : float{
		return 0;
	}
}
