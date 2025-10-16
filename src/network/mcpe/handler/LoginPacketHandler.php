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
use pocketmine\network\mcpe\auth\ProcessLegacyLoginTask;
use pocketmine\network\mcpe\auth\ProcessOpenIdLoginTask;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationInfo;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationType;
use pocketmine\network\mcpe\protocol\types\login\clientdata\ClientData;
use pocketmine\network\mcpe\protocol\types\login\clientdata\ClientDataToSkinDataHelper;
use pocketmine\network\mcpe\protocol\types\login\legacy\LegacyAuthChain;
use pocketmine\network\mcpe\protocol\types\login\legacy\LegacyAuthIdentityData;
use pocketmine\network\mcpe\protocol\types\login\openid\XboxAuthJwtBody;
use pocketmine\network\mcpe\protocol\types\login\openid\XboxAuthJwtHeader;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\player\XboxLivePlayerInfo;
use pocketmine\Server;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function chr;
use function count;
use function gettype;
use function is_array;
use function is_object;
use function json_decode;
use function md5;
use function ord;
use function var_export;
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

	private static function calculateUuidFromXuid(string $xuid) : UuidInterface{
		$hash = md5("pocket-auth-1-xuid:" . $xuid, binary: true);
		$hash[6] = chr((ord($hash[6]) & 0x0f) | 0x30); // set version to 3
		$hash[8] = chr((ord($hash[8]) & 0x3f) | 0x80); // set variant to RFC 4122

		return Uuid::fromBytes($hash);
	}

	public function handleLogin(LoginPacket $packet) : bool{
		$authInfo = $this->parseAuthInfo($packet->authInfoJson);

		if($authInfo->AuthenticationType === AuthenticationType::FULL->value){
			try{
				[$headerArray, $claimsArray,] = JwtUtils::parse($authInfo->Token);
			}catch(JwtException $e){
				throw PacketHandlingException::wrap($e, "Error parsing authentication token");
			}
			$header = $this->mapXboxTokenHeader($headerArray);
			$claims = $this->mapXboxTokenBody($claimsArray);

			$legacyUuid = self::calculateUuidFromXuid($claims->xid);
			$username = $claims->xname;
			$xuid = $claims->xid;

			$authRequired = $this->processLoginCommon($packet, $username, $legacyUuid, $xuid);
			if($authRequired === null){
				//plugin cancelled
				return true;
			}
			$this->processOpenIdLogin($authInfo->Token, $header->kid, $packet->clientDataJwt, $authRequired);

		}elseif($authInfo->AuthenticationType === AuthenticationType::SELF_SIGNED->value){
			try{
				$chainData = json_decode($authInfo->Certificate, flags: JSON_THROW_ON_ERROR);
			}catch(\JsonException $e){
				throw PacketHandlingException::wrap($e, "Error parsing self-signed certificate chain");
			}
			if(!is_object($chainData)){
				throw new PacketHandlingException("Unexpected type for self-signed certificate chain: " . gettype($chainData) . ", expected object");
			}
			try{
				$chain = $this->defaultJsonMapper("Self-signed auth chain JSON")->map($chainData, new LegacyAuthChain());
			}catch(\JsonMapper_Exception $e){
				throw PacketHandlingException::wrap($e, "Error mapping self-signed certificate chain");
			}
			if(count($chain->chain) > 1 || !isset($chain->chain[0])){
				throw new PacketHandlingException("Expected exactly one certificate in self-signed certificate chain, got " . count($chain->chain));
			}

			try{
				[, $claimsArray, ] = JwtUtils::parse($chain->chain[0]);
			}catch(JwtException $e){
				throw PacketHandlingException::wrap($e, "Error parsing self-signed certificate");
			}
			if(!isset($claimsArray["extraData"]) || !is_array($claimsArray["extraData"])){
				throw new PacketHandlingException("Expected \"extraData\" to be present in self-signed certificate");
			}

			try{
				$claims = $this->defaultJsonMapper("Self-signed auth JWT 'extraData'")->map($claimsArray["extraData"], new LegacyAuthIdentityData());
			}catch(\JsonMapper_Exception $e){
				throw PacketHandlingException::wrap($e, "Error mapping self-signed certificate extraData");
			}

			if(!Uuid::isValid($claims->identity)){
				throw new PacketHandlingException("Invalid UUID string in self-signed certificate: " . $claims->identity);
			}
			$legacyUuid = Uuid::fromString($claims->identity);
			$username = $claims->displayName;
			$xuid = "";

			$authRequired = $this->processLoginCommon($packet, $username, $legacyUuid, $xuid);
			if($authRequired === null){
				//plugin cancelled
				return true;
			}
			$this->processSelfSignedLogin($chain->chain, $packet->clientDataJwt, $authRequired);
		}else{
			throw new PacketHandlingException("Unsupported authentication type: $authInfo->AuthenticationType");
		}

		return true;
	}

	private function processLoginCommon(LoginPacket $packet, string $username, UuidInterface $legacyUuid, string $xuid) : ?bool{
		if(!Player::isValidUserName($username)){
			$this->session->disconnectWithError(KnownTranslationFactory::disconnectionScreen_invalidName());

			return null;
		}

		$clientData = $this->parseClientData($packet->clientDataJwt);

		try{
			$skin = $this->session->getTypeConverter()->getSkinAdapter()->fromSkinData(ClientDataToSkinDataHelper::fromClientData($clientData));
		}catch(\InvalidArgumentException | InvalidSkinException $e){
			$this->session->disconnectWithError(
				reason: "Invalid skin: " . $e->getMessage(),
				disconnectScreenMessage: KnownTranslationFactory::disconnectionScreen_invalidSkin()
			);

			return null;
		}

		if($xuid !== ""){
			$playerInfo = new XboxLivePlayerInfo(
				$xuid,
				$username,
				$legacyUuid,
				$skin,
				$clientData->LanguageCode,
				(array) $clientData
			);
		}else{
			$playerInfo = new PlayerInfo(
				$username,
				$legacyUuid,
				$skin,
				$clientData->LanguageCode,
				(array) $clientData
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
			return null;
		}

		return $ev->isAuthRequired();
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
			throw new PacketHandlingException("Unexpected type for auth info data: " . gettype($authInfoJson) . ", expected object");
		}

		$mapper = $this->defaultJsonMapper("Root authentication info JSON");
		try{
			$clientData = $mapper->map($authInfoJson, new AuthenticationInfo());
		}catch(\JsonMapper_Exception $e){
			throw PacketHandlingException::wrap($e);
		}
		return $clientData;
	}

	/**
	 * @param array<string, mixed> $headerArray
	 * @throws PacketHandlingException
	 */
	protected function mapXboxTokenHeader(array $headerArray) : XboxAuthJwtHeader{
		$mapper = $this->defaultJsonMapper("OpenID JWT header");
		try{
			$header = $mapper->map($headerArray, new XboxAuthJwtHeader());
		}catch(\JsonMapper_Exception $e){
			throw PacketHandlingException::wrap($e);
		}
		return $header;
	}

	/**
	 * @param array<string, mixed> $bodyArray
	 * @throws PacketHandlingException
	 */
	protected function mapXboxTokenBody(array $bodyArray) : XboxAuthJwtBody{
		$mapper = $this->defaultJsonMapper("OpenID JWT body");
		try{
			$header = $mapper->map($bodyArray, new XboxAuthJwtBody());
		}catch(\JsonMapper_Exception $e){
			throw PacketHandlingException::wrap($e);
		}
		return $header;
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

		$mapper = $this->defaultJsonMapper("ClientData JWT body");
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
	 * @throws \InvalidArgumentException
	 */
	protected function processOpenIdLogin(string $token, string $keyId, string $clientData, bool $authRequired) : void{
		$this->session->setHandler(null); //drop packets received during login verification

		$authKeyProvider = $this->server->getAuthKeyProvider();

		$authKeyProvider->getKey($keyId)->onCompletion(
			function(array $issuerAndKey) use ($token, $clientData, $authRequired) : void{
				[$issuer, $mojangPublicKeyPem] = $issuerAndKey;
				$this->server->getAsyncPool()->submitTask(new ProcessOpenIdLoginTask($token, $issuer, $mojangPublicKeyPem, $clientData, $authRequired, $this->authCallback));
			},
			fn() => ($this->authCallback)(false, $authRequired, "Unrecognized authentication key ID: $keyId", null)
		);
	}

	/**
	 * @param string[] $legacyCertificate
	 */
	protected function processSelfSignedLogin(array $legacyCertificate, string $clientDataJwt, bool $authRequired) : void{
		$this->session->setHandler(null); //drop packets received during login verification

		$this->server->getAsyncPool()->submitTask(new ProcessLegacyLoginTask($legacyCertificate, $clientDataJwt, rootAuthKeyDer: null, authRequired: $authRequired, onCompletion: $this->authCallback));
	}

	private function defaultJsonMapper(string $logContext) : \JsonMapper{
		$mapper = new \JsonMapper();
		$mapper->bExceptionOnMissingData = true;
		$mapper->undefinedPropertyHandler = $this->warnUndefinedJsonPropertyHandler($logContext);
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;
		return $mapper;
	}

	/**
	 * @phpstan-return \Closure(object, string, mixed) : void
	 */
	private function warnUndefinedJsonPropertyHandler(string $context) : \Closure{
		return fn(object $object, string $name, mixed $value) => $this->session->getLogger()->warning(
			"$context: Unexpected JSON property for " . (new \ReflectionClass($object))->getShortName() . ": " . $name . " = " . var_export($value, return: true)
		);
	}
}
