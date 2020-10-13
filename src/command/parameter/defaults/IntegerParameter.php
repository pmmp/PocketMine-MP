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

namespace pocketmine\command\parameter\defaults;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class IntegerParameter extends FloatParameter{

	public function parse(CommandSender $sender, string $argument){
		return (int) $argument;
	}

	public function getNetworkType() : int{
		return AvailableCommandsPacket::ARG_TYPE_INT;
	}

	public function getTargetName() : string{
		return "int";
	}

	public function setPostfix(?string $postfix) : self{
		$this->postfix = $postfix;
		return $this;
	}

	public function getPostfix() : ?string{
		return $this->postfix;
	}
}