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
use function array_unshift;

/**
 * Called after a player authenticates and is being offered resource packs to download.
 *
 * This event should be used to decide which resource packs to offer the player and whether to require the player to
 * download the packs before they can join the server.
 */
class PlayerResourcePackOfferEvent extends Event{
	/**
	 * @param ResourcePack[] $resourcePacks
	 * @param string[]       $encryptionKeys pack UUID => key, leave unset for any packs that are not encrypted
	 *
	 * @phpstan-param list<ResourcePack>    $resourcePacks
	 * @phpstan-param array<string, string> $encryptionKeys
	 */
	public function __construct(
		private readonly PlayerInfo $playerInfo,
		private array $resourcePacks,
		private array $encryptionKeys,
		private bool $mustAccept
	){}

	public function getPlayerInfo() : PlayerInfo{
		return $this->playerInfo;
	}

	/**
	 * Adds a resource pack to the top of the stack.
	 * The resources in this pack will be applied over the top of any existing packs.
	 */
	public function addResourcePack(ResourcePack $entry, ?string $encryptionKey = null) : void{
		array_unshift($this->resourcePacks, $entry);
		if($encryptionKey !== null){
			$this->encryptionKeys[$entry->getPackId()] = $encryptionKey;
		}
	}

	/**
	 * Sets the resource packs to offer. Packs are applied from the highest key to the lowest, with each pack
	 * overwriting any resources from the previous pack. This means that the pack at index 0 gets the final say on which
	 * resources are used.
	 *
	 * @param ResourcePack[] $resourcePacks
	 * @param string[]       $encryptionKeys pack UUID => key, leave unset for any packs that are not encrypted
	 *
	 * @phpstan-param list<ResourcePack>    $resourcePacks
	 * @phpstan-param array<string, string> $encryptionKeys
	 */
	public function setResourcePacks(array $resourcePacks, array $encryptionKeys) : void{
		$this->resourcePacks = $resourcePacks;
		$this->encryptionKeys = $encryptionKeys;
	}

	/**
	 * @return ResourcePack[]
	 * @phpstan-return list<ResourcePack>
	 */
	public function getResourcePacks() : array{
		return $this->resourcePacks;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
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
