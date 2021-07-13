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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class NpcDialoguePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::NPC_DIALOGUE_PACKET;

	public const ACTION_OPEN = 0;
	public const ACTION_CLOSE = 1;

	private int $npcActorUniqueId;
	private int $actionType;
	private string $dialogue;
	private string $sceneName;
	private string $npcName;
	private string $actionJson;

	public static function create(int $npcActorUniqueId, int $actionType, string $dialogue, string $sceneName, string $npcName, string $actionJson) : self{
		$result = new self;
		$result->npcActorUniqueId = $npcActorUniqueId;
		$result->actionType = $actionType;
		$result->dialogue = $dialogue;
		$result->sceneName = $sceneName;
		$result->npcName = $npcName;
		$result->actionJson = $actionJson;
		return $result;
	}

	public function getNpcActorUniqueId() : int{ return $this->npcActorUniqueId; }

	public function getActionType() : int{ return $this->actionType; }

	public function getDialogue() : string{ return $this->dialogue; }

	public function getSceneName() : string{ return $this->sceneName; }

	public function getNpcName() : string{ return $this->npcName; }

	public function getActionJson() : string{ return $this->actionJson; }

	protected function decodePayload(PacketSerializer $in) : void{
		$this->npcActorUniqueId = $in->getEntityUniqueId();
		$this->actionType = $in->getVarInt();
		$this->dialogue = $in->getString();
		$this->sceneName = $in->getString();
		$this->npcName = $in->getString();
		$this->actionJson = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putEntityUniqueId($this->npcActorUniqueId);
		$out->putVarInt($this->actionType);
		$out->putString($this->dialogue);
		$out->putString($this->sceneName);
		$out->putString($this->npcName);
		$out->putString($this->actionJson);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleNpcDialogue($this);
	}
}
