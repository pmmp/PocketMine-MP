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

final class AuthKeyring{

	/**
	 * @param string[] $keys
	 * @phpstan-param array<string, string> $keys
	 */
	public function __construct(
		private string $issuer,
		private array $keys
	){}

	public function getIssuer() : string{ return $this->issuer; }

	/**
	 * Returns a (raw) DER public key associated with the given key ID
	 */
	public function getKey(string $keyId) : ?string{
		return $this->keys[$keyId] ?? null;
	}
}
