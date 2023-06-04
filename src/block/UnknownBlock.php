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

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;

/**
 * Represents a block which is unrecognized or not implemented.
 */
class UnknownBlock extends Transparent{

	private int $stateData;

	public function __construct(BlockIdentifier $idInfo, BlockTypeInfo $typeInfo, int $stateData){
		$this->stateData = $stateData;
		parent::__construct($idInfo, "Unknown", $typeInfo);
	}

	public function describeBlockItemState(RuntimeDataDescriber $w) : void{
		//use type instead of state, so we don't lose any information like colour
		//this might be an improperly registered plugin block
		$w->int(Block::INTERNAL_STATE_DATA_BITS, $this->stateData);
	}

	public function canBePlaced() : bool{
		return false;
	}

	public function getDrops(Item $item) : array{
		return [];
	}
}
