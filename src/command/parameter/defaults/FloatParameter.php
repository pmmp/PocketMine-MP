<?php

declare(strict_types=1);

namespace pocketmine\command\parameter\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\parameter\Parameter;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use function is_numeric;

class FloatParameter extends Parameter{

	public function canParse(CommandSender $sender, string $argument) : bool{
		return is_numeric($argument);
	}

	public function parse(CommandSender $sender, string $argument){
		return (float) $argument;
	}

	public function getNetworkType() : int{
		return AvailableCommandsPacket::ARG_TYPE_FLOAT;
	}

	public function getTargetName() : string{
		return "float";
	}
}