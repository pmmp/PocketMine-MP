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

namespace pocketmine\network\mcpe\handler;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

/**
 * Handles the initial login phase of the session. This handler is used as the initial state.
 */
class LoginSessionHandler extends SessionHandler{

	/** @var Player */
	private $player;
	/** @var NetworkSession */
	private $session;

	public function __construct(Player $player, NetworkSession $session){
		$this->player = $player;
		$this->session = $session;
	}

	public function handleLogin(LoginPacket $packet) : bool{
		$this->session->setPlayerInfo($packet->playerInfo);

		if(!$this->isCompatibleProtocol($packet->protocol)){
			$pk = new PlayStatusPacket();
			$pk->status = $packet->protocol < ProtocolInfo::CURRENT_PROTOCOL ?
				PlayStatusPacket::LOGIN_FAILED_CLIENT : PlayStatusPacket::LOGIN_FAILED_SERVER;
			$this->session->sendDataPacket($pk, true);

			//This pocketmine disconnect message will only be seen by the console (PlayStatusPacket causes the messages to be shown for the client)
			$this->session->disconnect(
				$this->player->getServer()->getLanguage()->translateString("pocketmine.disconnect.incompatibleProtocol", [$packet->protocol]),
				false
			);

			return true;
		}

		if(!Player::isValidUserName($packet->playerInfo->getUsername())){
			$this->session->disconnect("disconnectionScreen.invalidName");

			return true;
		}

		if(!$packet->playerInfo->getSkin()->isValid()){
			$this->session->disconnect("disconnectionScreen.invalidSkin");

			return true;
		}

		if($this->player->handleLogin($packet)){
			if($this->session->isConnected() and $this->session->getHandler() === $this){ //when login verification is disabled, the handler will already have been replaced
				$this->session->setHandler(new NullSessionHandler()); //drop packets received during login verification
			}
			return true;
		}
		return false;
	}

	protected function isCompatibleProtocol(int $protocolVersion) : bool{
		return $protocolVersion === ProtocolInfo::CURRENT_PROTOCOL;
	}
}
