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

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\Player;
use function max;
use function min;
use const PHP_INT_MAX;

class CraftingGrid extends BaseUIInventory{
	public const SIZE_SMALL = 4;
	public const SIZE_BIG = 9;

	/** @var int */
	private $gridWidth;

	public function __construct(PlayerUIInventory $inventory, int $gridWidth){
		$this->gridWidth = $gridWidth;
		if($gridWidth === self::SIZE_SMALL){
			parent::__construct($inventory, $gridWidth, 28);
		}elseif($gridWidth === self::SIZE_BIG){
			parent::__construct($inventory, $gridWidth, 32);
		}
	}

	public function getGridWidth() : int{
		return $this->gridWidth;
	}
}
