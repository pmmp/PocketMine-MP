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

namespace pocketmine\network\mcpe\raklib;

use pocketmine\network\mcpe\NetworkSession;
use raklib\server\SessionEventListener;
use function substr;

/**
 * This class is an adapter between RakLib and NetworkSession. It delivers events received from RakLib to the
 * corresponding Minecraft network session.
 */
final class RakLibSessionEventListener implements SessionEventListener{

	private NetworkSession $session;

	public function __construct(NetworkSession $session){
		$this->session = $session;
	}

	public function onDisconnect(string $reason) : void{
		$this->session->onClientDisconnect($reason);
	}

	public function onPacketAck(int $identifierACK) : void{
		//NOOP: we don't use this functionality right now
	}

	public function onPingMeasure(int $pingMS) : void{
		$this->session->updatePing($pingMS);
	}

	public function onPacketReceive(string $payload) : void{
		if($payload === "" or $payload[0] !== RakLibInterface::MCPE_RAKNET_PACKET_ID){
			return;
		}
		$this->session->onPacketReceive(substr($payload, 1));
	}

}
