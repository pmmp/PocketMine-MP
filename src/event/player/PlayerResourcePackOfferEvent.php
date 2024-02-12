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
use function assert;

/**
 * Called after a player authenticates and is being offered resource packs to download.
 *
 * This event should be used to decide which resource packs to offer the player and whether to require the player to
 * download the packs before they can join the server.
 */
class PlayerResourcePackOfferEvent extends Event{

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

	public function addResourcePack(ResourcePack $entry) : self{
		$this->resourcePacks[] = $entry;
		return $this;
	}

	/**
	 * @param ResourcePack[] $resourcePacks
	 */
	public function setResourcePacks(array $resourcePacks) : self{
		$this->resourcePacks = $resourcePacks;
		return $this;
	}

	/**
	 * @return ResourcePack[]
	 */
	public function getResourcePacks() : array{
		return $this->resourcePacks;
	}

	public function setMustAccept(bool $mustAccept) : self{
		$this->mustAccept = $mustAccept;
		return $this;
	}

	public function mustAccept() : bool{
		return $this->mustAccept;
	}
}
