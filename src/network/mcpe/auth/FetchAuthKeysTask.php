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

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\login\openid\api\AuthServiceKey;
use pocketmine\network\mcpe\protocol\types\login\openid\api\AuthServiceOpenIdConfiguration;
use pocketmine\network\mcpe\protocol\types\login\openid\api\MinecraftServicesDiscovery;
use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;
use function gettype;
use function is_array;
use function is_object;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class FetchAuthKeysTask extends AsyncTask{
	private const KEYS_ON_COMPLETION = "completion";

	private const MINECRAFT_SERVICES_DISCOVERY_URL = "https://client.discovery.minecraft-services.net/api/v1.0/discovery/MinecraftPE/builds/" . ProtocolInfo::MINECRAFT_VERSION_NETWORK;
	private const AUTHORIZATION_SERVICE_URI_FALLBACK = "https://authorization.franchise.minecraft-services.net";
	private const AUTHORIZATION_SERVICE_OPENID_CONFIGURATION_PATH = "/.well-known/openid-configuration";
	private const AUTHORIZATION_SERVICE_KEYS_PATH = "/.well-known/keys";

	/** @phpstan-var ?NonThreadSafeValue<array<string, AuthServiceKey>> */
	private ?NonThreadSafeValue $keys = null;
	private string $issuer;

	/** @phpstan-var ?NonThreadSafeValue<non-empty-array<string>> */
	private ?NonThreadSafeValue $errors = null;

	/**
	 * @phpstan-param \Closure(?array<string, AuthServiceKey> $keys, string $issuer, ?string[] $errors) : void $onCompletion
	 */
	public function __construct(
		\Closure $onCompletion
	){
		$this->storeLocal(self::KEYS_ON_COMPLETION, $onCompletion);
	}

	public function onRun() : void{
		/** @var string[] $errors */
		$errors = [];

		try{
			$authServiceUri = $this->getAuthServiceURI();
		}catch(\RuntimeException $e){
			$errors[] = $e->getMessage();
			$authServiceUri = self::AUTHORIZATION_SERVICE_URI_FALLBACK;
		}

		try {
			$openIdConfig = $this->getOpenIdConfiguration($authServiceUri);
			$jwksUri = $openIdConfig->jwks_uri;

			$this->issuer = $openIdConfig->issuer;
		} catch (\RuntimeException $e) {
			$errors[] = $e->getMessage();
			$jwksUri = $authServiceUri . self::AUTHORIZATION_SERVICE_KEYS_PATH;

			$this->issuer = $authServiceUri;
		}

		try{
			$this->keys = new NonThreadSafeValue($this->getKeys($jwksUri));
		}catch(\RuntimeException $e){
			$errors[] = $e->getMessage();
		}

		$this->errors = $errors === [] ? null : new NonThreadSafeValue($errors);
	}

	/**
	 * @throws \RuntimeException
	 */
	private static function fetchURL(string $url, int $expectedCode) : InternetRequestResult{
		try{
			$result = Internet::simpleCurl($url);
			if($result->getCode() !== $expectedCode){
				throw new \RuntimeException("Unexpected HTTP response code accessing \"$url\": " . $result->getCode());
			}
			return $result;
		}catch(InternetException $e){
			throw new \RuntimeException("Failed accessing \"$url\": " . $e->getMessage(), 0, $e);
		}
	}

	private function getAuthServiceURI() : string{
		$result = self::fetchURL(self::MINECRAFT_SERVICES_DISCOVERY_URL, 200);
		try{
			$json = json_decode($result->getBody(), false, flags: JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw new \RuntimeException($e->getMessage(), 0, $e);
		}
		if(!is_object($json)){
			throw new \RuntimeException("Unexpected root type of schema file " . gettype($json) . ", expected object");
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = false; //we only care about the properties we're using in this case
		$mapper->bExceptionOnMissingData = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;
		$mapper->bRemoveUndefinedAttributes = true;
		try{
			/** @var MinecraftServicesDiscovery $discovery */
			$discovery = $mapper->map($json, new MinecraftServicesDiscovery());
		}catch(\JsonMapper_Exception $e){
			throw new \RuntimeException("Invalid schema file: " . $e->getMessage(), 0, $e);
		}

		return $discovery->result->serviceEnvironments->auth->prod->serviceUri;
	}

	private function getOpenIdConfiguration(string $authServiceUri) : AuthServiceOpenIdConfiguration{
		$result = self::fetchURL($authServiceUri . self::AUTHORIZATION_SERVICE_OPENID_CONFIGURATION_PATH, 200);

		try{
			$json = json_decode($result->getBody(), false, flags: JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw new \RuntimeException($e->getMessage(), 0, $e);
		}
		if(!is_object($json)){
			throw new \RuntimeException("Unexpected root type of schema file " . gettype($json) . ", expected object");
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = false; //we only care about the properties we're using in this case
		$mapper->bExceptionOnMissingData = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;
		$mapper->bRemoveUndefinedAttributes = true;
		try{
			/** @var AuthServiceOpenIdConfiguration $configuration */
			$configuration = $mapper->map($json, new AuthServiceOpenIdConfiguration());
		}catch(\JsonMapper_Exception $e){
			throw new \RuntimeException("Invalid schema file: " . $e->getMessage(), 0, $e);
		}

		return $configuration;
	}

	/**
	 * @return array<string, AuthServiceKey> keys indexed by key ID
	 */
	private function getKeys(string $jwksUri) : array{
		$result = self::fetchURL($jwksUri, 200);

		try{
			$json = json_decode($result->getBody(), true, flags: JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw new \RuntimeException($e->getMessage(), 0, $e);
		}

		if(!is_array($json) || !isset($json["keys"]) || !is_array($keysArray = $json["keys"])){
			throw new \RuntimeException("Unexpected root type of schema file " . gettype($json) . ", expected object");
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bExceptionOnMissingData = true;
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bEnforceMapType = false;
		$mapper->bRemoveUndefinedAttributes = true;

		$keys = [];
		foreach($keysArray as $keyJson){
			if(!is_array($keyJson)){
				throw new \RuntimeException("Unexpected key type in schema file: " . gettype($keyJson) . ", expected object");
			}

			try{
				/** @var AuthServiceKey $key */
				$key = $mapper->map($keyJson, new AuthServiceKey());
				$keys[$key->kid] = $key;
			}catch(\JsonMapper_Exception $e){
				throw new \RuntimeException("Invalid schema file: " . $e->getMessage(), 0, $e);
			}
		}

		return $keys;
	}

	public function onCompletion() : void{
		/**
		 * @var \Closure $callback
		 * @phpstan-var \Closure(?AuthServiceKey[] $keys, string $issuer, ?string[] $errors) : void $callback
		 */
		$callback = $this->fetchLocal(self::KEYS_ON_COMPLETION);
		$callback($this->keys?->deserialize(), $this->issuer, $this->errors?->deserialize());
	}
}
