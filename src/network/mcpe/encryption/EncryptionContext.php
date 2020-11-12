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

use Crypto\Cipher;
use pocketmine\utils\Binary;
use function bin2hex;
use function openssl_digest;
use function openssl_error_string;
use function strlen;
use function substr;

class EncryptionContext{
	private const ENCRYPTION_SCHEME = "AES-256-CFB8";
	private const CHECKSUM_ALGO = "sha256";

	/** @var bool */
	public static $ENABLED = true;

	/** @var string */
	private $key;

	/** @var Cipher */
	private $decryptCipher;
	/** @var int */
	private $decryptCounter = 0;

	/** @var Cipher */
	private $encryptCipher;
	/** @var int */
	private $encryptCounter = 0;

	public function __construct(string $encryptionKey){
		//TODO: ext/crypto doesn't offer us a way to disable padding. This doesn't matter at the moment because we're
		//using CFB8, but this might change in the future. This is supposed to be CFB8 no-padding.

		$this->key = $encryptionKey;
		$iv = substr($this->key, 0, 16);

		$this->decryptCipher = new Cipher(self::ENCRYPTION_SCHEME);
		$this->decryptCipher->decryptInit($this->key, $iv);

		$this->encryptCipher = new Cipher(self::ENCRYPTION_SCHEME);
		$this->encryptCipher->encryptInit($this->key, $iv);
	}

	/**
	 * @throws DecryptionException
	 */
	public function decrypt(string $encrypted) : string{
		if(strlen($encrypted) < 9){
			throw new DecryptionException("Payload is too short");
		}
		$decrypted = $this->decryptCipher->decryptUpdate($encrypted);
		$payload = substr($decrypted, 0, -8);

		$packetCounter = $this->decryptCounter++;

		if(($expected = $this->calculateChecksum($packetCounter, $payload)) !== ($actual = substr($decrypted, -8))){
			throw new DecryptionException("Encrypted packet $packetCounter has invalid checksum (expected " . bin2hex($expected) . ", got " . bin2hex($actual) . ")");
		}

		return $payload;
	}

	public function encrypt(string $payload) : string{
		return $this->encryptCipher->encryptUpdate($payload . $this->calculateChecksum($this->encryptCounter++, $payload));
	}

	private function calculateChecksum(int $counter, string $payload) : string{
		$hash = openssl_digest(Binary::writeLLong($counter) . $payload . $this->key, self::CHECKSUM_ALGO, true);
		if($hash === false){
			throw new \RuntimeException("openssl_digest() error: " . openssl_error_string());
		}
		return substr($hash, 0, 8);
	}
}
