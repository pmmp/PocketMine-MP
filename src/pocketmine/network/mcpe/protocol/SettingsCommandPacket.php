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

use pocketmine\network\mcpe\NetworkSession;

class SettingsCommandPacket extends DataPacket/* implements ServerboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::SETTINGS_COMMAND_PACKET;

	/** @var string */
	private $command;
	/** @var bool */
	private $suppressOutput;

	public static function create(string $command, bool $suppressOutput) : self{
		$result = new self;
		$result->command = $command;
		$result->suppressOutput = $suppressOutput;
		return $result;
	}

	public function getCommand() : string{
		return $this->command;
	}

	public function getSuppressOutput() : bool{
		return $this->suppressOutput;
	}

	protected function decodePayload() : void{
		$this->command = $this->getString();
		$this->suppressOutput = $this->getBool();
	}

	protected function encodePayload() : void{
		$this->putString($this->command);
		$this->putBool($this->suppressOutput);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleSettingsCommand($this);
	}
}
