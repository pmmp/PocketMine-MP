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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;
use function count;

class ResourcePacksInfoPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACKS_INFO_PACKET;

	/** @var bool */
	public $mustAccept = false; //if true, forces client to use selected resource packs
	/** @var bool */
	public $hasScripts = false; //if true, causes disconnect for any platform that doesn't support scripts yet
	/** @var ResourcePackInfoEntry[] */
	public $behaviorPackEntries = [];
	/** @var ResourcePackInfoEntry[] */
	public $resourcePackEntries = [];

	/**
	 * @param ResourcePackInfoEntry[] $resourcePacks
	 * @param ResourcePackInfoEntry[] $behaviorPacks
	 * @param bool                    $mustAccept
	 * @param bool                    $hasScripts
	 *
	 * @return ResourcePacksInfoPacket
	 */
	public static function create(array $resourcePacks, array $behaviorPacks, bool $mustAccept, bool $hasScripts = false) : self{
		$result = new self;
		$result->mustAccept = $mustAccept;
		$result->hasScripts = $hasScripts;
		$result->resourcePackEntries = $resourcePacks;
		$result->behaviorPackEntries = $behaviorPacks;
		return $result;
	}

	protected function decodePayload() : void{
		$this->mustAccept = $this->getBool();
		$this->hasScripts = $this->getBool();
		$behaviorPackCount = $this->getLShort();
		while($behaviorPackCount-- > 0){
			$this->behaviorPackEntries[] = ResourcePackInfoEntry::read($this);
		}

		$resourcePackCount = $this->getLShort();
		while($resourcePackCount-- > 0){
			$this->resourcePackEntries[] = ResourcePackInfoEntry::read($this);
		}
	}

	protected function encodePayload() : void{
		$this->putBool($this->mustAccept);
		$this->putBool($this->hasScripts);
		$this->putLShort(count($this->behaviorPackEntries));
		foreach($this->behaviorPackEntries as $entry){
			$entry->write($this);
		}
		$this->putLShort(count($this->resourcePackEntries));
		foreach($this->resourcePackEntries as $entry){
			$entry->write($this);
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleResourcePacksInfo($this);
	}
}
