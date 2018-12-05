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

class RedstoneWire extends Flowable{
	/** @var int */
	protected $id = Block::REDSTONE_WIRE;
	/** @var int */
	protected $itemId = Item::REDSTONE;

	/** @var int */
	protected $power = 0;

	public function __construct(){

	}

	public function readStateFromMeta(int $meta) : void{
		$this->power = $meta;
	}

	protected function writeStateToMeta() : int{
		return $this->power;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getName() : string{
		return "Redstone";
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		//TODO: check connections to nearby redstone components
	}
}
