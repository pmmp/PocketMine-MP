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

class SlimeBlock extends Solid implements Bouncy{
        /**
	 * @var int $id
	 */
	protected $id = self::SLIME_BLOCK;

	/**
	 * SlimeBlock constructor.
	 *
	 * @param int $meta
	 */
	public function __construct($meta = 15){
		$this->meta = $meta;
	}
	
	/**
	 * @return string
	 */
	public function getName() : string{
		return "Slime Block";
	}

	/**
	 * @return bool
	 */
	public function hasEntityCollision() : bool{
		return true;
	}

	/**
	 * @return float
	 */
	public function getHardness() : float{
		return 0;
	}

	/**
	 * @param Item $item
	 * @return array
	 */
	public function getDrops(Item $item): array{
		return [
			Item::get(Item::SLIME_BLOCK, 0, 1),
		];
	}

	/**
	 * @return float
	 */
	public function getMaxBounce() : float{
		return 60;
	}
}
