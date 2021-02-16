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

use pocketmine\block\utils\AnalogRedstoneSignalEmitterTrait;
use pocketmine\block\utils\BlockDataSerializer;

class RedstoneWire extends Flowable{
	use AnalogRedstoneSignalEmitterTrait;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? BlockBreakInfo::instant());
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->signalStrength = BlockDataSerializer::readBoundedInt("signalStrength", $stateMeta, 0, 15);
	}

	protected function writeStateToMeta() : int{
		return $this->signalStrength;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		//TODO: check connections to nearby redstone components
	}
}
