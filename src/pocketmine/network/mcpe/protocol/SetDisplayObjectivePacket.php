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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\scoreboard\DisplaySlot;
use pocketmine\network\mcpe\protocol\types\scoreboard\Objective;
use pocketmine\network\mcpe\protocol\types\scoreboard\SortOrder;

class SetDisplayObjectivePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::SET_DISPLAY_OBJECTIVE_PACKET;

	/** @var Objective */
	public $objective;

	public static function create(Objective $objective) : self{
		$result = new self;
		$result->objective = $objective;
		return $result;
	}

	protected function getObjective() : Objective{
		return new Objective(
			DisplaySlot::fromString($this->getString()),
			$this->getString(),
			$this->getString(),
			$this->getString(),
			SortOrder::fromMagicNumber($this->getVarInt())
		);
	}

	protected function putObjective(Objective $objective) : void{
		$this->putString($objective->displaySlot->name());
		$this->putString($objective->objectiveName);
		$this->putString($objective->displayName);
		$this->putString($objective->criteriaName);
		$this->putVarInt($objective->sortOrder->getMagicNumber());
	}


	protected function decodePayload() : void{
		$this->objective = $this->getObjective();
	}

	protected function encodePayload() : void{
		$this->putObjective($this->objective);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleSetDisplayObjective($this);
	}
}
