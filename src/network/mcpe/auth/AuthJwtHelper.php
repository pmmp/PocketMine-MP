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

namespace pocketmine\network\mcpe\auth;

use pocketmine\lang\KnownTranslationFactory;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\types\login\JwtBodyRfc7519;
use pocketmine\network\mcpe\protocol\types\login\legacy\LegacyAuthJwtBody;
use pocketmine\network\mcpe\protocol\types\login\openid\XboxAuthJwtBody;
use pocketmine\network\mcpe\protocol\types\login\SelfSignedJwtHeader;
use function base64_decode;
use function time;

final class AuthJwtHelper{

	public const MOJANG_AUDIENCE = "api://auth-minecraft-services/multiplayer";

	private const CLOCK_DRIFT_MAX = 60;

	/**
	 * @throws VerifyLoginException if the token is expired or not yet valid
	 */
	private static function checkExpiry(JwtBodyRfc7519 $claims) : void{
		$time = time();
		if(isset($claims->nbf) && $claims->nbf > $time + self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("JWT not yet valid", KnownTranslationFactory::pocketmine_disconnect_invalidSession_tooEarly());
		}

		if(isset($claims->exp) && $claims->exp < $time - self::CLOCK_DRIFT_MAX){
			throw new VerifyLoginException("JWT expired", KnownTranslationFactory::pocketmine_disconnect_invalidSession_tooLate());
		}
	}

	/**
	 * @throws VerifyLoginException if errors are encountered
	 */
	public static function validateOpenIdAuthToken(string $jwt, string $signingKeyDer, string $issuer, string $audience) : XboxAuthJwtBody{
		try{
			if(!JwtUtils::verify($jwt, $signingKeyDer, ec: false)){
				throw new VerifyLoginException("Invalid JWT signature", KnownTranslationFactory::pocketmine_disconnect_invalidSession_badSignature());
			}
		}catch(JwtException $e){
			throw new VerifyLoginException($e->getMessage(), null, 0, $e);
		}

		try{
			[, $claimsArray, ] = JwtUtils::parse($jwt);
		}catch(JwtException $e){
			throw new VerifyLoginException("Failed to parse JWT: " . $e->getMessage(), null, 0, $e);
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = false; //we only care about the properties we're using in this case
		$mapper->bExceptionOnMissingData = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;
		$mapper->bRemoveUndefinedAttributes = true;

		try{
			//nasty dynamic new for JsonMapper
			$claims = $mapper->map($claimsArray, new XboxAuthJwtBody());
		}catch(\JsonMapper_Exception $e){
			throw new VerifyLoginException("Invalid chain link body: " . $e->getMessage(), null, 0, $e);
		}

		if(!isset($claims->iss) || $claims->iss !== $issuer){
			throw new VerifyLoginException("Invalid JWT issuer");
		}

		if(!isset($claims->aud) || $claims->aud !== $audience){
			throw new VerifyLoginException("Invalid JWT audience");
		}

		self::checkExpiry($claims);

		return $claims;
	}

	/**
	 * @throws VerifyLoginException if errors are encountered
	 */
	public static function validateLegacyAuthToken(string $jwt, ?string $expectedKeyDer) : LegacyAuthJwtBody{
		self::validateSelfSignedToken($jwt, $expectedKeyDer);

		//TODO: this parses the JWT twice and throws away a bunch of parts, optimize this
		[, $claimsArray, ] = JwtUtils::parse($jwt);

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = false; //we only care about the properties we're using in this case
		$mapper->bExceptionOnMissingData = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;
		$mapper->bRemoveUndefinedAttributes = true;
		try{
			/** @var LegacyAuthJwtBody $claims */
			$claims = $mapper->map($claimsArray, new LegacyAuthJwtBody());
		}catch(\JsonMapper_Exception $e){
			throw new VerifyLoginException("Invalid chain link body: " . $e->getMessage(), null, 0, $e);
		}

		self::checkExpiry($claims);

		return $claims;
	}

	public static function validateSelfSignedToken(string $jwt, ?string $expectedKeyDer) : void{
		try{
			[$headersArray, ] = JwtUtils::parse($jwt);
		}catch(JwtException $e){
			throw new VerifyLoginException("Failed to parse JWT: " . $e->getMessage(), null, 0, $e);
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;

		try{
			/** @var SelfSignedJwtHeader $headers */
			$headers = $mapper->map($headersArray, new SelfSignedJwtHeader());
		}catch(\JsonMapper_Exception $e){
			throw new VerifyLoginException("Invalid JWT header: " . $e->getMessage(), null, 0, $e);
		}

		$headerDerKey = base64_decode($headers->x5u, true);
		if($headerDerKey === false){
			throw new VerifyLoginException("Invalid JWT public key: base64 decoding error decoding x5u");
		}
		if($expectedKeyDer !== null && $headerDerKey !== $expectedKeyDer){
			//Fast path: if the header key doesn't match what we expected, the signature isn't going to validate anyway
			throw new VerifyLoginException("Invalid JWT signature", KnownTranslationFactory::pocketmine_disconnect_invalidSession_badSignature());
		}

		try{
			if(!JwtUtils::verify($jwt, $headerDerKey, ec: true)){
				throw new VerifyLoginException("Invalid JWT signature", KnownTranslationFactory::pocketmine_disconnect_invalidSession_badSignature());
			}
		}catch(JwtException $e){
			throw new VerifyLoginException($e->getMessage(), null, 0, $e);
		}
	}
}
