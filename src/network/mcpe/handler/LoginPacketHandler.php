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

use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\auth\ProcessLoginTask;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\login\ClientDataPersonaPieceTintColor;
use pocketmine\network\mcpe\protocol\types\login\ClientDataPersonaSkinPiece;
use pocketmine\network\mcpe\protocol\types\PersonaPieceTintColor;
use pocketmine\network\mcpe\protocol\types\PersonaSkinPiece;
use pocketmine\network\mcpe\protocol\types\SkinAnimation;
use pocketmine\network\mcpe\protocol\types\SkinData;
use pocketmine\network\mcpe\protocol\types\SkinImage;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\Server;
use pocketmine\utils\UUID;
use function array_map;
use function base64_decode;

/**
 * Handles the initial login phase of the session. This handler is used as the initial state.
 */
class LoginPacketHandler extends PacketHandler{

	/** @var Server */
	private $server;
	/** @var NetworkSession */
	private $session;
	/**
	 * @var \Closure
	 * @phpstan-var \Closure(PlayerInfo) : void
	 */
	private $playerInfoConsumer;
	/**
	 * @var \Closure
	 * @phpstan-var \Closure(bool, bool, ?string, ?PublicKeyInterface) : void
	 */
	private $authCallback;

	/**
	 * @phpstan-param \Closure(PlayerInfo) : void $playerInfoConsumer
	 * @phpstan-param \Closure(bool $isAuthenticated, bool $authRequired, ?string $error, ?PublicKeyInterface $clientPubKey) : void $authCallback
	 */
	public function __construct(Server $server, NetworkSession $session, \Closure $playerInfoConsumer, \Closure $authCallback){
		$this->session = $session;
		$this->server = $server;
		$this->playerInfoConsumer = $playerInfoConsumer;
		$this->authCallback = $authCallback;
	}

	private static function dummy() : void{
		echo PublicKeyInterface::class; //this prevents the import getting removed by tools that don't understand phpstan
	}

	public function handleLogin(LoginPacket $packet) : bool{
		if(!$this->isCompatibleProtocol($packet->protocol)){
			$this->session->sendDataPacket(PlayStatusPacket::create($packet->protocol < ProtocolInfo::CURRENT_PROTOCOL ? PlayStatusPacket::LOGIN_FAILED_CLIENT : PlayStatusPacket::LOGIN_FAILED_SERVER), true);

			//This pocketmine disconnect message will only be seen by the console (PlayStatusPacket causes the messages to be shown for the client)
			$this->session->disconnect(
				$this->server->getLanguage()->translateString("pocketmine.disconnect.incompatibleProtocol", [$packet->protocol]),
				false
			);

			return true;
		}

		if(!Player::isValidUserName($packet->extraData->displayName)){
			$this->session->disconnect("disconnectionScreen.invalidName");

			return true;
		}

		try{
			$clientData = $packet->clientData; //this serves no purpose except readability
			/** @var SkinAnimation[] $animations */
			$animations = [];
			foreach($clientData->AnimatedImageData as $animation){
				$animations[] = new SkinAnimation(
					new SkinImage(
						$animation->ImageHeight,
						$animation->ImageWidth,
						base64_decode($animation->Image, true)
					),
					$animation->Type,
					$animation->Frames
				);
			}
			$skinData = new SkinData(
				$clientData->SkinId,
				base64_decode($clientData->SkinResourcePatch, true),
				new SkinImage($clientData->SkinImageHeight, $clientData->SkinImageWidth, base64_decode($clientData->SkinData, true)),
				$animations,
				new SkinImage($clientData->CapeImageHeight, $clientData->CapeImageWidth, base64_decode($clientData->CapeData, true)),
				base64_decode($clientData->SkinGeometryData, true),
				base64_decode($clientData->SkinAnimationData, true),
				$clientData->PremiumSkin,
				$clientData->PersonaSkin,
				$clientData->CapeOnClassicSkin,
				$clientData->CapeId,
				null,
				$clientData->ArmSize,
				$clientData->SkinColor,
				array_map(function(ClientDataPersonaSkinPiece $piece) : PersonaSkinPiece{
					return new PersonaSkinPiece($piece->PieceId, $piece->PieceType, $piece->PackId, $piece->IsDefault, $piece->ProductId);
				}, $clientData->PersonaPieces),
				array_map(function(ClientDataPersonaPieceTintColor $tint) : PersonaPieceTintColor{
					return new PersonaPieceTintColor($tint->PieceType, $tint->Colors);
				}, $clientData->PieceTintColors)
			);

			$skin = SkinAdapterSingleton::get()->fromSkinData($skinData);
		}catch(\InvalidArgumentException $e){
			$this->session->getLogger()->debug("Invalid skin: " . $e->getMessage());
			$this->session->disconnect("disconnectionScreen.invalidSkin");

			return true;
		}

		try{
			$uuid = UUID::fromString($packet->extraData->identity);
		}catch(\InvalidArgumentException $e){
			throw BadPacketException::wrap($e, "Failed to parse login UUID");
		}
		($this->playerInfoConsumer)(new PlayerInfo(
			$packet->extraData->displayName,
			$uuid,
			$skin,
			$packet->clientData->LanguageCode,
			$packet->extraData->XUID,
			(array) $packet->clientData
		));

		$ev = new PlayerPreLoginEvent(
			$this->session->getPlayerInfo(),
			$this->session->getIp(),
			$this->session->getPort(),
			$this->server->requiresAuthentication()
		);
		if($this->server->getNetwork()->getConnectionCount() > $this->server->getMaxPlayers()){
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_SERVER_FULL, "disconnectionScreen.serverFull");
		}
		if(!$this->server->isWhitelisted($this->session->getPlayerInfo()->getUsername())){
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_SERVER_WHITELISTED, "Server is whitelisted");
		}
		if($this->server->getNameBans()->isBanned($this->session->getPlayerInfo()->getUsername()) or $this->server->getIPBans()->isBanned($this->session->getIp())){
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
	 * @throws \InvalidArgumentException
	 */
	protected function processLogin(LoginPacket $packet, bool $authRequired) : void{
		$this->server->getAsyncPool()->submitTask(new ProcessLoginTask($packet, $authRequired, $this->authCallback));
		$this->session->setHandler(null); //drop packets received during login verification
	}

	protected function isCompatibleProtocol(int $protocolVersion) : bool{
		return $protocolVersion === ProtocolInfo::CURRENT_PROTOCOL;
	}
}
