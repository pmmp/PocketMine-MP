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

namespace pocketmine\player;

use pocketmine\entity\Skin;
use pocketmine\utils\TextFormat;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Encapsulates data needed to create a player.
 */
class PlayerInfo{
	/**
	 * Namespace for server-generated UUIDs for unauthenticated (non-XBL) players.
	 * This must not be changed.
	 */
	private const UNAUTHENTICATED_PLAYER_UUID_NS = '6a6424c0-a26f-43b7-8e72-4176d051748d';

	private UuidInterface $uuid;
	/**
	 * @param mixed[] $extraData
	 * @phpstan-param array<string, mixed> $extraData
	 */
	public function __construct(
		private string $username,
		?UuidInterface $uuid,
		private Skin $skin,
		private string $locale,
		private array $extraData = []
	){
		$this->username = TextFormat::clean($username);
		$this->uuid = $uuid ?? self::generateServerAuthoritativeUuid($this->username);
	}

	/**
	 * Generates a UUID based on the player's username. This is used for any non-authenticated player, as we can't
	 * trust UUIDs sent by unauthenticated players.
	 */
	public static function generateServerAuthoritativeUuid(string $username) : UuidInterface{
		//TODO: should we be cleaning the username here?
		return Uuid::uuid5(self::UNAUTHENTICATED_PLAYER_UUID_NS, TextFormat::clean($username));
	}

	public function getUsername() : string{
		return $this->username;
	}

	public function getUuid() : UuidInterface{
		return $this->uuid;
	}

	public function getSkin() : Skin{
		return $this->skin;
	}

	public function getLocale() : string{
		return $this->locale;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<string, mixed>
	 */
	public function getExtraData() : array{
		return $this->extraData;
	}
}
