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

use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\network\mcpe\NetworkCipher;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\ProcessLoginTask;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\Server;

/**
 * Handles the initial login phase of the session. This handler is used as the initial state.
 */
class LoginSessionHandler extends SessionHandler{

	/** @var Server */
	private $server;
	/** @var NetworkSession */
	private $session;


	public function __construct(Server $server, NetworkSession $session){
		$this->session = $session;
		$this->server = $server;
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
				$this->server->getLanguage()->translateString("pocketmine.disconnect.incompatibleProtocol", [$packet->protocol]),
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

		$ev = new PlayerPreLoginEvent(
			$packet->playerInfo,
			$this->session->getIp(),
			$this->session->getPort(),
			$this->server->requiresAuthentication()
		);
		if($this->server->getNetwork()->getConnectionCount() > $this->server->getMaxPlayers()){
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_SERVER_FULL, "disconnectionScreen.serverFull");
		}
		if(!$this->server->isWhitelisted($packet->playerInfo->getUsername())){
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_SERVER_WHITELISTED, "Server is whitelisted");
		}
		if($this->server->getNameBans()->isBanned($packet->playerInfo->getUsername()) or $this->server->getIPBans()->isBanned($this->session->getIp())){
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_BANNED, "You are banned");
		}

		$ev->call();
		if(!$ev->isAllowed()){
			$this->session->disconnect($ev->getFinalKickMessage());
			return true;
		}

		$this->processLogin($packet, $ev->isAuthRequired());

		return true;
	}

	/**
	 * TODO: This is separated for the purposes of allowing plugins (like Specter) to hack it and bypass authentication.
	 * In the future this won't be necessary.
	 *
	 * @param LoginPacket $packet
	 * @param bool        $authRequired
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function processLogin(LoginPacket $packet, bool $authRequired) : void{
		$this->server->getAsyncPool()->submitTask(new ProcessLoginTask($this->session, $packet, $authRequired, NetworkCipher::$ENABLED));
		$this->session->setHandler(new NullSessionHandler()); //drop packets received during login verification
	}

	protected function isCompatibleProtocol(int $protocolVersion) : bool{
		return $protocolVersion === ProtocolInfo::CURRENT_PROTOCOL;
	}
}
