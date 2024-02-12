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

/**
 * Called after a player authenticates and is being offered resource packs to download.
 *
 * This event should be used to decide which resource packs to offer to the player, and whether to require them to
 * download the packs before they can join the server.
 *
 * The Player object has not yet been instantiated, so PlayerInfo is used instead. The player is authenticated at this
 * point, but the information should still be treated with caution.
 */
class PlayerResourcePackOfferEvent extends Event{

	/**
	 * @param ResourcePack[] $resourcePackEntries
	 */
	public function __construct(
		private readonly PlayerInfo $playerInfo,
		private array $resourcePackEntries,
		private bool $resourcePacksRequired
	){}

	/**
	 * Returns an object containing self-proclaimed information about the connecting player. The player has been
	 * authenticated at this point, but the information should still be treated with caution.
	 */
	public function getPlayerInfo() : PlayerInfo{
		return $this->playerInfo;
	}

	public function addResourcePack(ResourcePack $entry) : self{
		$this->resourcePackEntries[] = $entry;
		return $this;
	}

	/**
	 * @param ResourcePack[] $resourcePackEntries
	 */
	public function setResourcePackEntries(array $resourcePackEntries) : self{
		$this->resourcePackEntries = $resourcePackEntries;
		return $this;
	}

	/**
	 * @return ResourcePack[]
	 */
	public function getResourcePackEntries() : array{
		return $this->resourcePackEntries;
	}

	public function setResourcePacksRequired(bool $resourcePacksRequired) : self{
		$this->resourcePacksRequired = $resourcePacksRequired;
		return $this;
	}

	public function resourcePacksRequired() : bool{
		return $this->resourcePacksRequired;
	}
}
