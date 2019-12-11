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
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use function count;

class PlayerListPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_LIST_PACKET;

	public const TYPE_ADD = 0;
	public const TYPE_REMOVE = 1;

	/** @var PlayerListEntry[] */
	public $entries = [];
	/** @var int */
	public $type;

	public static function add(array $entries) : self{
		$result = new self;
		$result->type = self::TYPE_ADD;
		$result->entries = $entries;
		return $result;
	}

	public static function remove(array $entries) : self{
		$result = new self;
		$result->type = self::TYPE_REMOVE;
		$result->entries = $entries;
		return $result;
	}

	protected function decodePayload() : void{
		$this->type = $this->getByte();
		$count = $this->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$entry = new PlayerListEntry();

			if($this->type === self::TYPE_ADD){
				$entry->uuid = $this->getUUID();
				$entry->entityUniqueId = $this->getEntityUniqueId();
				$entry->username = $this->getString();
				$entry->xboxUserId = $this->getString();
				$entry->platformChatId = $this->getString();
				$entry->buildPlatform = $this->getLInt();
				$entry->skinData = $this->getSkin();
				$entry->isTeacher = $this->getBool();
				$entry->isHost = $this->getBool();
			}else{
				$entry->uuid = $this->getUUID();
			}

			$this->entries[$i] = $entry;
		}
	}

	protected function encodePayload() : void{
		$this->putByte($this->type);
		$this->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $entry){
			if($this->type === self::TYPE_ADD){
				$this->putUUID($entry->uuid);
				$this->putEntityUniqueId($entry->entityUniqueId);
				$this->putString($entry->username);
				$this->putString($entry->xboxUserId);
				$this->putString($entry->platformChatId);
				$this->putLInt($entry->buildPlatform);
				$this->putSkin($entry->skinData);
				$this->putBool($entry->isTeacher);
				$this->putBool($entry->isHost);
			}else{
				$this->putUUID($entry->uuid);
			}
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handlePlayerList($this);
	}
}
