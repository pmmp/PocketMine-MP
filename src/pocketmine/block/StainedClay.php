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

use pocketmine\item\TieredTool;
use pocketmine\item\Tool;

class StainedClay extends Solid{

	protected $id = Block::STAINED_HARDENED_CLAY;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 1.25;
	}

	public function getToolType() : int{
		return Tool::TYPE_PICKAXE;
	}

	public function getRequiredHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function getName() : string{
		static $names = [
			0 => "White Stained Clay",
			1 => "Orange Stained Clay",
			2 => "Magenta Stained Clay",
			3 => "Light Blue Stained Clay",
			4 => "Yellow Stained Clay",
			5 => "Lime Stained Clay",
			6 => "Pink Stained Clay",
			7 => "Gray Stained Clay",
			8 => "Light Gray Stained Clay",
			9 => "Cyan Stained Clay",
			10 => "Purple Stained Clay",
			11 => "Blue Stained Clay",
			12 => "Brown Stained Clay",
			13 => "Green Stained Clay",
			14 => "Red Stained Clay",
			15 => "Black Stained Clay",
		];
		return $names[$this->meta & 0x0f];
	}

}