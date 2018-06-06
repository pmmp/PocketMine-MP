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

class PlayStatusPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::PLAY_STATUS_PACKET;

	public const LOGIN_SUCCESS = 0;
	public const LOGIN_FAILED_CLIENT = 1;
	public const LOGIN_FAILED_SERVER = 2;
	public const PLAYER_SPAWN = 3;
	public const LOGIN_FAILED_INVALID_TENANT = 4;
	public const LOGIN_FAILED_VANILLA_EDU = 5;
	public const LOGIN_FAILED_EDU_VANILLA = 6;
	public const LOGIN_FAILED_SERVER_FULL = 7;

	/** @var int */
	public $status;

	/**
	 * @var int
	 * Used to determine how to write the packet when we disconnect incompatible clients.
	 */
	public $protocol;

	protected function decodePayload(){
		$this->status = $this->getInt();
	}

	public function canBeSentBeforeLogin() : bool{
		return true;
	}

	protected function encodeHeader(){
		if($this->protocol < 130){ //MCPE <= 1.1
			$this->putByte(static::NETWORK_ID);
		}else{
			parent::encodeHeader();
		}
	}

	protected function encodePayload(){
		$this->putInt($this->status);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePlayStatus($this);
	}

}
