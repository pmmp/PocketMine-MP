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

use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use function count;

class SetScorePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::SET_SCORE_PACKET;

	public const TYPE_CHANGE = 0;
	public const TYPE_REMOVE = 1;

	/** @var int */
	public $type;
	/** @var ScorePacketEntry[] */
	public $entries = [];

	protected function decodePayload() : void{
		$this->type = $this->buf->getByte();
		for($i = 0, $i2 = $this->buf->getUnsignedVarInt(); $i < $i2; ++$i){
			$entry = new ScorePacketEntry();
			$entry->scoreboardId = $this->buf->getVarLong();
			$entry->objectiveName = $this->buf->getString();
			$entry->score = $this->buf->getLInt();
			if($this->type !== self::TYPE_REMOVE){
				$entry->type = $this->buf->getByte();
				switch($entry->type){
					case ScorePacketEntry::TYPE_PLAYER:
					case ScorePacketEntry::TYPE_ENTITY:
						$entry->entityUniqueId = $this->buf->getEntityUniqueId();
						break;
					case ScorePacketEntry::TYPE_FAKE_PLAYER:
						$entry->customName = $this->buf->getString();
						break;
					default:
						throw new BadPacketException("Unknown entry type $entry->type");
				}
			}
			$this->entries[] = $entry;
		}
	}

	protected function encodePayload() : void{
		$this->buf->putByte($this->type);
		$this->buf->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $entry){
			$this->buf->putVarLong($entry->scoreboardId);
			$this->buf->putString($entry->objectiveName);
			$this->buf->putLInt($entry->score);
			if($this->type !== self::TYPE_REMOVE){
				$this->buf->putByte($entry->type);
				switch($entry->type){
					case ScorePacketEntry::TYPE_PLAYER:
					case ScorePacketEntry::TYPE_ENTITY:
						$this->buf->putEntityUniqueId($entry->entityUniqueId);
						break;
					case ScorePacketEntry::TYPE_FAKE_PLAYER:
						$this->buf->putString($entry->customName);
						break;
					default:
						throw new \InvalidArgumentException("Unknown entry type $entry->type");
				}
			}
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleSetScore($this);
	}
}
