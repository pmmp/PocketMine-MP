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

class BrewingStand extends Transparent{

	/** @var bool */
	protected $eastSlot = false;
	/** @var bool */
	protected $northwestSlot = false;
	/** @var bool */
	protected $southwestSlot = false;

	protected function writeStateToMeta() : int{
		return ($this->eastSlot ? 0x01 : 0) | ($this->southwestSlot ? 0x02 : 0) | ($this->northwestSlot ? 0x04 : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->eastSlot = ($stateMeta & 0x01) !== 0;
		$this->southwestSlot = ($stateMeta & 0x02) !== 0;
		$this->northwestSlot = ($stateMeta & 0x04) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getHardness() : float{
		return 0.5;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	//TODO
}
