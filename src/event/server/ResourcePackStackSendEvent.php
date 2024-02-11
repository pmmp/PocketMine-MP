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

namespace pocketmine\event\server;

use pocketmine\event\Event;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\resourcepacks\ResourcePack;

class ResourcePackStackSendEvent extends Event{

	/**
	 * @param ResourcePack[] $resourcePackEntries
	 */
	public function __construct(private readonly NetworkSession $session, private array $resourcePackEntries, private bool $resourcePacksRequired, private bool $forceServerResources){}

	public function getSession() : NetworkSession{
		return $this->session;
	}

	public function addResourcePackEntry(ResourcePack $entry) : self{
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

	public function setForceServerResource(bool $forceServerResources) : self{
		$this->forceServerResources = $forceServerResources;
		return $this;
	}

	public function forceServerResources() : bool{
		return $this->forceServerResources;
	}

	public function resourcePacksRequired() : bool{
		return $this->resourcePacksRequired;
	}

	public function setResourcePacksRequired(bool $resourcePacksRequired) : self{
		$this->resourcePacksRequired = $resourcePacksRequired;
		return $this;
	}
}
