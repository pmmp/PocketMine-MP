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

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\item\Item;
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class TripwireHook extends Flowable{

	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $connected = false;
	/** @var bool */
	protected $powered = false;

	protected function writeStateToMeta() : int{
		return Bearing::fromFacing($this->facing) | ($this->connected ? 0x04 : 0) | ($this->powered ? 0x08 : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataValidator::readLegacyHorizontalFacing($stateMeta & 0x03);
		$this->connected = ($stateMeta & 0x04) !== 0;
		$this->powered = ($stateMeta & 0x08) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(Facing::axis($face) !== Facing::AXIS_Y){
			//TODO: check face is valid
			$this->facing = $face;
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}
		return false;
	}

	//TODO
}
