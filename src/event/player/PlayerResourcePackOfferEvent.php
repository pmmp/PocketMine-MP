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

namespace pocketmine\event\player;

use pocketmine\event\Event;
use pocketmine\player\PlayerInfo;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\utils\Utils;
use function array_unshift;

/**
 * Called after a player authenticates and is being offered resource packs to download.
 *
 * This event should be used to decide which resource packs to offer the player and whether to require the player to
 * download the packs before they can join the server.
 */
class PlayerResourcePackOfferEvent extends Event{
	/**
	 * @var string[] $encryptionKeys
	 * @phpstan-var array<string, string> $encryptionKeys
	 */
	private array $encryptionKeys = [];

	/**
	 * @param ResourcePack[] $resourcePacks
	 */
	public function __construct(
		private readonly PlayerInfo $playerInfo,
		private array $resourcePacks,
		private bool $mustAccept
	){}

	public function getPlayerInfo() : PlayerInfo{
		return $this->playerInfo;
	}

	public function addResourcePack(ResourcePack $entry, ?string $encryptionKey = null) : void{
		array_unshift($this->resourcePacks, $entry);
		if($encryptionKey !== null){
			$this->encryptionKeys[$entry->getPackId()] = $encryptionKey;
		}
	}

	/**
	 * @param ResourcePack[] $resourcePacks
	 * @param string[]       $encryptionKeys
	 * @phpstan-param array<string, string> $encryptionKeys
	 */
	public function setResourcePacks(array $resourcePacks, ?array $encryptionKeys = null) : void{
		$this->resourcePacks = $resourcePacks;
		if($encryptionKeys !== null){
			foreach(Utils::stringifyKeys($encryptionKeys) as $packId => $key){
				$this->encryptionKeys[$packId] ??= $key;
			}
		}
	}

	/**
	 * @return ResourcePack[]
	 */
	public function getResourcePacks() : array{
		return $this->resourcePacks;
	}

	/**
	 * @return string[]
	 */
	public function getEncryptionKeys() : array{
		return $this->encryptionKeys;
	}

	public function setMustAccept(bool $mustAccept) : void{
		$this->mustAccept = $mustAccept;
	}

	public function mustAccept() : bool{
		return $this->mustAccept;
	}
}
