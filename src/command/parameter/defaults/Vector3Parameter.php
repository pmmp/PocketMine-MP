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
use pocketmine\command\parameter\Parameter;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\player\Player;
use function count;
use function explode;
use function is_numeric;
use function substr;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

class Vector3Parameter extends Parameter{

	public function __construct(string $name, bool $optional = false){
		parent::__construct($name, $optional);
		$this->setLength(3);
	}

	public function canParse(CommandSender $sender, string $argument) : bool{
		if($sender->getServer()->getPlayer($argument) !== null){
			return true;
		}
		$coordinateArgs = explode(" ", $argument);
		if(count($coordinateArgs) !== 3){
			return false;
		}
		return true;
	}

	public function parse(CommandSender $sender, string $argument){
		$target = $sender->getServer()->getPlayer($argument);
		if($target !== null){
			return $target->getPosition()->asVector3();
		}
		[$x, $y, $z] = explode(" ", $argument);
		if($sender instanceof Player){
			$x = $this->getCoordinates($x);
			$y = $this->getCoordinates($y);
			$z = $this->getCoordinates($z);
			return new Vector3((float) $sender->getPosition()->getX() + $x, (float) $sender->getPosition()->getY() + $y, (float) $sender->getPosition()->getZ() + $z);
		}
		if(is_numeric($x) && is_numeric($y) && is_numeric($z)){
			return new Vector3((float) $x, (float) $y, (float) $z);
		}
		return null;
	}

	private function getCoordinates(string $input) : float{
		if($input[0] === "~"){
			$input = substr($input, 1);
		}
		$i = (double) $input;

		if($i < PHP_INT_MIN){
			$i = PHP_INT_MIN;
		}elseif($i > PHP_INT_MAX){
			$i = PHP_INT_MAX;
		}

		return $i;
	}

	public function getNetworkType() : int{
		return AvailableCommandsPacket::ARG_TYPE_POSITION;
	}

	public function getTargetName() : string{
		return "x y z";
	}
}