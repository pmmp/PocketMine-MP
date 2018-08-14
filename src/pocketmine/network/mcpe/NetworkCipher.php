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

namespace pocketmine\network\mcpe;

use Crypto\Cipher;
use pocketmine\utils\Binary;

class NetworkCipher{
	private const ENCRYPTION_SCHEME = "AES-256-CFB8";
	private const CHECKSUM_ALGO = "sha256";

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

	public function decrypt($encrypted){
		if(strlen($encrypted) < 9){
			throw new \InvalidArgumentException("Payload is too short");
		}
		$decrypted = $this->decryptCipher->decryptUpdate($encrypted);
		$payload = substr($decrypted, 0, -8);

		if(($expected = $this->calculateChecksum($this->decryptCounter++, $payload)) !== ($actual = substr($decrypted, -8))){
			throw new \InvalidArgumentException("Encrypted payload has invalid checksum (expected " . bin2hex($expected) . ", got " . bin2hex($actual) . ")");
		}

		return $payload;
	}

	public function encrypt(string $payload) : string{
		return $this->encryptCipher->encryptUpdate($payload . $this->calculateChecksum($this->encryptCounter++, $payload));
	}

	private function calculateChecksum(int $counter, string $payload) : string{
		return substr(openssl_digest(Binary::writeLLong($counter) . $payload . $this->key, self::CHECKSUM_ALGO, true), 0, 8);
	}
}
