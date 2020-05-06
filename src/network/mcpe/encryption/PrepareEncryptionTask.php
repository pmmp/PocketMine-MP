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

namespace pocketmine\network\mcpe\encryption;

use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\EccFactory;
use pocketmine\scheduler\AsyncTask;
use function random_bytes;

class PrepareEncryptionTask extends AsyncTask{

	private const TLS_KEY_ON_COMPLETION = "completion";

	/** @var PrivateKeyInterface|null */
	private static $SERVER_PRIVATE_KEY = null;

	/** @var PrivateKeyInterface */
	private $serverPrivateKey;

	/** @var string|null */
	private $aesKey = null;
	/** @var string|null */
	private $handshakeJwt = null;
	/** @var PublicKeyInterface */
	private $clientPub;

	/**
	 * @phpstan-param \Closure(string $encryptionKey, string $handshakeJwt) : void $onCompletion
	 */
	public function __construct(PublicKeyInterface $clientPub, \Closure $onCompletion){
		if(self::$SERVER_PRIVATE_KEY === null){
			self::$SERVER_PRIVATE_KEY = EccFactory::getNistCurves()->generator384()->createPrivateKey();
		}

		$this->serverPrivateKey = self::$SERVER_PRIVATE_KEY;
		$this->clientPub = $clientPub;
		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
	}

	public function onRun() : void{
		$serverPriv = $this->serverPrivateKey;
		$sharedSecret = EncryptionUtils::generateSharedSecret($serverPriv, $this->clientPub);

		$salt = random_bytes(16);
		$this->aesKey = EncryptionUtils::generateKey($sharedSecret, $salt);
		$this->handshakeJwt = EncryptionUtils::generateServerHandshakeJwt($serverPriv, $salt);
	}

	public function onCompletion() : void{
		/**
		 * @var \Closure $callback
		 * @phpstan-var \Closure(string $encryptionKey, string $handshakeJwt) : void $callback
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_ON_COMPLETION);
		$callback($this->aesKey, $this->handshakeJwt);
	}
}
