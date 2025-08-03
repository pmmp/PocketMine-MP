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

use pocketmine\entity\InvalidSkinException;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\auth\ProcessLoginTask;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationData;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationInfo;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationType;
use pocketmine\network\mcpe\protocol\types\login\ClientData;
use pocketmine\network\mcpe\protocol\types\login\ClientDataToSkinDataHelper;
use pocketmine\network\mcpe\protocol\types\login\JwtChain;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\player\XboxLivePlayerInfo;
use pocketmine\Server;
use Ramsey\Uuid\Uuid;
use function gettype;
use function is_array;
use function is_object;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Handles the initial login phase of the session. This handler is used as the initial state.
 */
class LoginPacketHandler extends PacketHandler{
	/**
	 * @phpstan-param \Closure(PlayerInfo) : void $playerInfoConsumer
	 * @phpstan-param \Closure(bool $isAuthenticated, bool $authRequired, Translatable|string|null $error, ?string $clientPubKey) : void $authCallback
	 */
	public function __construct(
		private Server $server,
		private NetworkSession $session,
		private \Closure $playerInfoConsumer,
		private \Closure $authCallback
	){}

	public function handleLogin(LoginPacket $packet) : bool{
		$authInfo = $this->parseAuthInfo($packet->authInfoJson);
		$jwtChain = $this->parseJwtChain($authInfo->Certificate);
		$extraData = $this->fetchAuthData($jwtChain);

		if(!Player::isValidUserName($extraData->displayName)){
			$this->session->disconnectWithError(KnownTranslationFactory::disconnectionScreen_invalidName());

			return true;
		}

		$clientData = $this->parseClientData($packet->clientDataJwt);

		try{
			$skin = $this->session->getTypeConverter()->getSkinAdapter()->fromSkinData(ClientDataToSkinDataHelper::fromClientData($clientData));
		}catch(\InvalidArgumentException | InvalidSkinException $e){
			$this->session->disconnectWithError(
				reason: "Invalid skin: " . $e->getMessage(),
				disconnectScreenMessage: KnownTranslationFactory::disconnectionScreen_invalidSkin()
			);

			return true;
		}

		if(!Uuid::isValid($extraData->identity)){
			throw new PacketHandlingException("Invalid login UUID");
		}
		$uuid = Uuid::fromString($extraData->identity);
		$arrClientData = (array) $clientData;
		$arrClientData["TitleID"] = $extraData->titleId;

		if($extraData->XUID !== ""){
			$playerInfo = new XboxLivePlayerInfo(
				$extraData->XUID,
				$extraData->displayName,
				$uuid,
				$skin,
				$clientData->LanguageCode,
				$arrClientData
			);
		}else{
			$playerInfo = new PlayerInfo(
				$extraData->displayName,
				$uuid,
				$skin,
				$clientData->LanguageCode,
				$arrClientData
			);
		}
		($this->playerInfoConsumer)($playerInfo);

		$ev = new PlayerPreLoginEvent(
			$playerInfo,
			$this->session->getIp(),
			$this->session->getPort(),
			$this->server->requiresAuthentication()
		);
		if($this->server->getNetwork()->getValidConnectionCount() > $this->server->getMaxPlayers()){
			$ev->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_SERVER_FULL, KnownTranslationFactory::disconnectionScreen_serverFull());
		}
		if(!$this->server->isWhitelisted($playerInfo->getUsername())){
			$ev->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_SERVER_WHITELISTED, KnownTranslationFactory::pocketmine_disconnect_whitelisted());
		}

		$banMessage = null;
		if(($banEntry = $this->server->getNameBans()->getEntry($playerInfo->getUsername())) !== null){
			$banReason = $banEntry->getReason();
			$banMessage = $banReason === "" ? KnownTranslationFactory::pocketmine_disconnect_ban_noReason() : KnownTranslationFactory::pocketmine_disconnect_ban($banReason);
		}elseif(($banEntry = $this->server->getIPBans()->getEntry($this->session->getIp())) !== null){
			$banReason = $banEntry->getReason();
			$banMessage = KnownTranslationFactory::pocketmine_disconnect_ban($banReason !== "" ? $banReason : KnownTranslationFactory::pocketmine_disconnect_ban_ip());
		}
		if($banMessage !== null){
			$ev->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_BANNED, $banMessage);
		}

		$ev->call();
		if(!$ev->isAllowed()){
			$this->session->disconnect($ev->getFinalDisconnectReason(), $ev->getFinalDisconnectScreenMessage());
			return true;
		}

		$this->processLogin($authInfo->Token, AuthenticationType::from($authInfo->AuthenticationType), $jwtChain->chain, $packet->clientDataJwt, $ev->isAuthRequired());

		return true;
	}

	/**
	 * @throws PacketHandlingException
	 */
	protected function parseAuthInfo(string $authInfo) : AuthenticationInfo{
		try{
			$authInfoJson = json_decode($authInfo, associative: false, flags: JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw PacketHandlingException::wrap($e);
		}
		if(!is_object($authInfoJson)){
			throw new \RuntimeException("Unexpected type for auth info data: " . gettype($authInfoJson) . ", expected object");
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bStrictObjectTypeChecking = true;
		try{
			$clientData = $mapper->map($authInfoJson, new AuthenticationInfo());
		}catch(\JsonMapper_Exception $e){
			throw PacketHandlingException::wrap($e);
		}
		return $clientData;
	}

	/**
	 * @throws PacketHandlingException
	 */
	protected function parseJwtChain(string $chainDataJwt) : JwtChain{
		try{
			$jwtChainJson = json_decode($chainDataJwt, associative: false, flags: JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw PacketHandlingException::wrap($e);
		}
		if(!is_object($jwtChainJson)){
			throw new \RuntimeException("Unexpected type for JWT chain data: " . gettype($jwtChainJson) . ", expected object");
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bStrictObjectTypeChecking = true;
		try{
			$clientData = $mapper->map($jwtChainJson, new JwtChain());
		}catch(\JsonMapper_Exception $e){
			throw PacketHandlingException::wrap($e);
		}
		return $clientData;
	}

	/**
	 * @throws PacketHandlingException
	 */
	protected function fetchAuthData(JwtChain $chain) : AuthenticationData{
		/** @var AuthenticationData|null $extraData */
		$extraData = null;
		foreach($chain->chain as $jwt){
			//validate every chain element
			try{
				[, $claims, ] = JwtUtils::parse($jwt);
			}catch(JwtException $e){
				throw PacketHandlingException::wrap($e);
			}
			if(isset($claims["extraData"])){
				if($extraData !== null){
					throw new PacketHandlingException("Found 'extraData' more than once in chainData");
				}

				if(!is_array($claims["extraData"])){
					throw new PacketHandlingException("'extraData' key should be an array");
				}
				$mapper = new \JsonMapper();
				$mapper->bEnforceMapType = false; //TODO: we don't really need this as an array, but right now we don't have enough models
				$mapper->bExceptionOnMissingData = true;
				$mapper->bExceptionOnUndefinedProperty = true;
				$mapper->bStrictObjectTypeChecking = true;
				try{
					/** @var AuthenticationData $extraData */
					$extraData = $mapper->map($claims["extraData"], new AuthenticationData());
				}catch(\JsonMapper_Exception $e){
					throw PacketHandlingException::wrap($e);
				}
			}
		}
		if($extraData === null){
			throw new PacketHandlingException("'extraData' not found in chain data");
		}
		return $extraData;
	}

	/**
	 * @throws PacketHandlingException
	 */
	protected function parseClientData(string $clientDataJwt) : ClientData{
		try{
			[, $clientDataClaims, ] = JwtUtils::parse($clientDataJwt);
		}catch(JwtException $e){
			throw PacketHandlingException::wrap($e);
		}

		$mapper = new \JsonMapper();
		$mapper->bEnforceMapType = false; //TODO: we don't really need this as an array, but right now we don't have enough models
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bStrictObjectTypeChecking = true;
		try{
			$clientData = $mapper->map($clientDataClaims, new ClientData());
		}catch(\JsonMapper_Exception $e){
			throw PacketHandlingException::wrap($e);
		}
		return $clientData;
	}

	/**
	 * TODO: This is separated for the purposes of allowing plugins (like Specter) to hack it and bypass authentication.
	 * In the future this won't be necessary.
	 *
	 * @param null|string[] $legacyCertificate
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function processLogin(string $token, AuthenticationType $authType, ?array $legacyCertificate, string $clientData, bool $authRequired) : void{
		if($legacyCertificate === null){
			throw new PacketHandlingException("Legacy certificate cannot be null");
		}
		$this->server->getAsyncPool()->submitTask(new ProcessLoginTask($legacyCertificate, $clientData, $authRequired, $this->authCallback));
		$this->session->setHandler(null); //drop packets received during login verification
	}
}
