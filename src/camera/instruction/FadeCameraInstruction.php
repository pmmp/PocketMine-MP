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

namespace pocketmine\camera\instruction;

use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;
use pocketmine\player\Player;

final class FadeCameraInstruction extends CameraInstruction
{
	private ?CameraFadeInstructionTime $time = null;
	private ?CameraFadeInstructionColor $color = null;

	public function setTime(float $fadeInTime, float $stayInTime, float $fadeOutTime) : void
	{
		$this->time = new CameraFadeInstructionTime($fadeInTime, $stayInTime, $fadeOutTime);
	}

	public function setColor(float $red, float $green, float $blue) : void
	{
		$this->color = new CameraFadeInstructionColor($red, $green, $blue);
	}

	public function send(Player $player) : void
	{
		$player->getNetworkSession()->sendDataPacket(CameraInstructionPacket::create(null, null, new CameraFadeInstruction($this->time, $this->color)));
	}
}
