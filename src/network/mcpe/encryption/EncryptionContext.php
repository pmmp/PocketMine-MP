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

	public function __construct(string $encryptionKey, string $algorithm, string $iv){
		$this->key = $encryptionKey;

		$this->decryptCipher = new Cipher($algorithm);
		$this->decryptCipher->decryptInit($this->key, $iv);

		$this->encryptCipher = new Cipher($algorithm);
		$this->encryptCipher->encryptInit($this->key, $iv);
	}

	/**
	 * Returns an EncryptionContext suitable for decrypting Minecraft packets from 1.16.200 and up.
	 *
	 * MCPE uses GCM, but without the auth tag, which defeats the whole purpose of using GCM.
	 * GCM is just a wrapper around CTR which adds the auth tag, so CTR can replace GCM for this case.
	 * However, since GCM passes only the first 12 bytes of the IV followed by 0002, we must do the same for
	 * compatibility with MCPE.
	 * In PM, we could skip this and just use GCM directly (since we use OpenSSL), but this way is more portable, and
	 * better for developers who come digging in the PM code looking for answers.
	 */
	public static function fakeGCM(string $encryptionKey) : self{
		return new EncryptionContext(
			$encryptionKey,
			"AES-256-CTR",
			substr($encryptionKey, 0, 12) . "\x00\x00\x00\x02"
		);
	}

	public static function cfb8(string $encryptionKey) : self{
		return new EncryptionContext(
			$encryptionKey,
			"AES-256-CFB8",
			substr($encryptionKey, 0, 16)
		);
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
