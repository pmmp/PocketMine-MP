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
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationData;
use pocketmine\network\mcpe\protocol\types\login\ClientData;
use pocketmine\network\mcpe\protocol\types\login\ClientDataPersonaPieceTintColor;
use pocketmine\network\mcpe\protocol\types\login\ClientDataPersonaSkinPiece;
use pocketmine\network\mcpe\protocol\types\login\JwtChain;
use pocketmine\network\mcpe\protocol\types\PersonaPieceTintColor;
use pocketmine\network\mcpe\protocol\types\PersonaSkinPiece;
use pocketmine\network\mcpe\protocol\types\SkinAnimation;
use pocketmine\network\mcpe\protocol\types\SkinData;
use pocketmine\network\mcpe\protocol\types\SkinImage;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\Server;
use pocketmine\uuid\UUID;
use function array_map;
use function base64_decode;
use function is_array;

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

		$extraData = $this->fetchAuthData($packet->chainDataJwt);

		if(!Player::isValidUserName($extraData->displayName)){
			$this->session->disconnect("disconnectionScreen.invalidName");

			return true;
		}

		$clientData = $this->parseClientData($packet->clientDataJwt);
		$safeB64Decode = static function(string $base64, string $context) : string{
			$result = base64_decode($base64, true);
			if($result === false){
				throw new \InvalidArgumentException("$context: Malformed base64, cannot be decoded");
			}
			return $result;
		};
		try{
			/** @var SkinAnimation[] $animations */
			$animations = [];
			foreach($clientData->AnimatedImageData as $k => $animation){
				$animations[] = new SkinAnimation(
					new SkinImage(
						$animation->ImageHeight,
						$animation->ImageWidth,
						$safeB64Decode($animation->Image, "AnimatedImageData.$k.Image")
					),
					$animation->Type,
					$animation->Frames
				);
			}
			$skinData = new SkinData(
				$clientData->SkinId,
				$safeB64Decode($clientData->SkinResourcePatch, "SkinResourcePatch"),
				new SkinImage($clientData->SkinImageHeight, $clientData->SkinImageWidth, $safeB64Decode($clientData->SkinData, "SkinData")),
				$animations,
				new SkinImage($clientData->CapeImageHeight, $clientData->CapeImageWidth, $safeB64Decode($clientData->CapeData, "CapeData")),
				$safeB64Decode($clientData->SkinGeometryData, "SkinGeometryData"),
				$safeB64Decode($clientData->SkinAnimationData, "SkinAnimationData"),
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
			$uuid = UUID::fromString($extraData->identity);
		}catch(\InvalidArgumentException $e){
			throw BadPacketException::wrap($e, "Failed to parse login UUID");
		}
		($this->playerInfoConsumer)(new PlayerInfo(
			$extraData->displayName,
			$uuid,
			$skin,
			$clientData->LanguageCode,
			$extraData->XUID,
			(array) $clientData
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
	 * @throws BadPacketException
	 */
	protected function fetchAuthData(JwtChain $chain) : AuthenticationData{
		/** @var AuthenticationData|null $extraData */
		$extraData = null;
		foreach($chain->chain as $k => $jwt){
			//validate every chain element
			try{
				[, $claims, ] = JwtUtils::parse($jwt);
			}catch(JwtException $e){
				throw BadPacketException::wrap($e);
			}
			if(isset($claims["extraData"])){
				if($extraData !== null){
					throw new BadPacketException("Found 'extraData' more than once in chainData");
				}

				if(!is_array($claims["extraData"])){
					throw new BadPacketException("'extraData' key should be an array");
				}
				$mapper = new \JsonMapper;
				$mapper->bEnforceMapType = false; //TODO: we don't really need this as an array, but right now we don't have enough models
				$mapper->bExceptionOnMissingData = true;
				$mapper->bExceptionOnUndefinedProperty = true;
				try{
					/** @var AuthenticationData $extraData */
					$extraData = $mapper->map($claims['extraData'], new AuthenticationData);
				}catch(\JsonMapper_Exception $e){
					throw BadPacketException::wrap($e);
				}
			}
		}
		if($extraData === null){
			throw new BadPacketException("'extraData' not found in chain data");
		}
		return $extraData;
	}

	/**
	 * @throws BadPacketException
	 */
	protected function parseClientData(string $clientDataJwt) : ClientData{
		try{
			[, $clientDataClaims, ] = JwtUtils::parse($clientDataJwt);
		}catch(JwtException $e){
			throw BadPacketException::wrap($e);
		}

		$mapper = new \JsonMapper;
		$mapper->bEnforceMapType = false; //TODO: we don't really need this as an array, but right now we don't have enough models
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		try{
			$clientData = $mapper->map($clientDataClaims, new ClientData);
		}catch(\JsonMapper_Exception $e){
			throw BadPacketException::wrap($e);
		}
		return $clientData;
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
