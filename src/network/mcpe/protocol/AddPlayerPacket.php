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

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\MetadataProperty;
use pocketmine\utils\UUID;
use function count;

class AddPlayerPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::ADD_PLAYER_PACKET;

	/** @var UUID */
	public $uuid;
	/** @var string */
	public $username;
	/** @var int|null */
	public $entityUniqueId = null; //TODO
	/** @var int */
	public $entityRuntimeId;
	/** @var string */
	public $platformChatId = "";
	/** @var Vector3 */
	public $position;
	/** @var Vector3|null */
	public $motion;
	/** @var float */
	public $pitch = 0.0;
	/** @var float */
	public $yaw = 0.0;
	/** @var float|null */
	public $headYaw = null; //TODO
	/** @var Item */
	public $item;
	/**
	 * @var MetadataProperty[]
	 * @phpstan-var array<int, MetadataProperty>
	 */
	public $metadata = [];

	//TODO: adventure settings stuff
	/** @var int */
	public $uvarint1 = 0;
	/** @var int */
	public $uvarint2 = 0;
	/** @var int */
	public $uvarint3 = 0;
	/** @var int */
	public $uvarint4 = 0;
	/** @var int */
	public $uvarint5 = 0;

	/** @var int */
	public $long1 = 0;

	/** @var EntityLink[] */
	public $links = [];

	/** @var string */
	public $deviceId = ""; //TODO: fill player's device ID (???)
	/** @var int */
	public $buildPlatform = -1;

	protected function decodePayload() : void{
		$this->uuid = $this->buf->getUUID();
		$this->username = $this->buf->getString();
		$this->entityUniqueId = $this->buf->getEntityUniqueId();
		$this->entityRuntimeId = $this->buf->getEntityRuntimeId();
		$this->platformChatId = $this->buf->getString();
		$this->position = $this->buf->getVector3();
		$this->motion = $this->buf->getVector3();
		$this->pitch = $this->buf->getLFloat();
		$this->yaw = $this->buf->getLFloat();
		$this->headYaw = $this->buf->getLFloat();
		$this->item = $this->buf->getSlot();
		$this->metadata = $this->buf->getEntityMetadata();

		$this->uvarint1 = $this->buf->getUnsignedVarInt();
		$this->uvarint2 = $this->buf->getUnsignedVarInt();
		$this->uvarint3 = $this->buf->getUnsignedVarInt();
		$this->uvarint4 = $this->buf->getUnsignedVarInt();
		$this->uvarint5 = $this->buf->getUnsignedVarInt();

		$this->long1 = $this->buf->getLLong();

		$linkCount = $this->buf->getUnsignedVarInt();
		for($i = 0; $i < $linkCount; ++$i){
			$this->links[$i] = $this->buf->getEntityLink();
		}

		$this->deviceId = $this->buf->getString();
		$this->buildPlatform = $this->buf->getLInt();
	}

	protected function encodePayload() : void{
		$this->buf->putUUID($this->uuid);
		$this->buf->putString($this->username);
		$this->buf->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$this->buf->putEntityRuntimeId($this->entityRuntimeId);
		$this->buf->putString($this->platformChatId);
		$this->buf->putVector3($this->position);
		$this->buf->putVector3Nullable($this->motion);
		$this->buf->putLFloat($this->pitch);
		$this->buf->putLFloat($this->yaw);
		$this->buf->putLFloat($this->headYaw ?? $this->yaw);
		$this->buf->putSlot($this->item);
		$this->buf->putEntityMetadata($this->metadata);

		$this->buf->putUnsignedVarInt($this->uvarint1);
		$this->buf->putUnsignedVarInt($this->uvarint2);
		$this->buf->putUnsignedVarInt($this->uvarint3);
		$this->buf->putUnsignedVarInt($this->uvarint4);
		$this->buf->putUnsignedVarInt($this->uvarint5);

		$this->buf->putLLong($this->long1);

		$this->buf->putUnsignedVarInt(count($this->links));
		foreach($this->links as $link){
			$this->buf->putEntityLink($link);
		}

		$this->buf->putString($this->deviceId);
		$this->buf->putLInt($this->buildPlatform);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleAddPlayer($this);
	}
}
