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


use pocketmine\entity\Skin;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\utils\SerializedImage;
use pocketmine\utils\SkinAnimation;
use function count;

class PlayerListPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_LIST_PACKET;

	public const TYPE_ADD = 0;
	public const TYPE_REMOVE = 1;

	/** @var PlayerListEntry[] */
	public $entries = [];
	/** @var int */
	public $type;

	public function clean(){
		$this->entries = [];
		return parent::clean();
	}

	protected function decodePayload(){
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

				$skinId = $this->getString();
				$skinResourcePatch = $this->getString();
				$skinData = $this->getImage();
				$animationCount = $this->getLInt();
				$animations = [];
				for($i = 0; $i < $animationCount; ++$i){
					$animations[] = new SkinAnimation($this->getImage(), $this->getLInt(), $this->getLFloat());
				}
				$capeData = $this->getImage();
				$geometryData = $this->getString();
				$animationData = $this->getString();
				$premium = $this->getBool();
				$persona = $this->getBool();
				$capeOnClassic = $this->getBool();
				$capeId = $this->getString();
				$fullSkinId = $this->getString();

				$entry->skin = new Skin(
					$skinId, $skinResourcePatch, $skinData, $animations, $capeData, $geometryData, $animationData, $premium, $persona, $capeOnClassic, $capeId
				);

				$entry->isTeacher = $this->getBool();
				$entry->isHost = $this->getBool();
			}else{
				$entry->uuid = $this->getUUID();
			}

			$this->entries[$i] = $entry;
		}
	}

	protected function encodePayload(){
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

				$this->putString($entry->skin->getSkinId());
				$this->putString($entry->skin->getSkinResourcePatch());
				$this->putImage($entry->skin->getSkinData());
				$this->putLInt(count($entry->skin->getAnimations()));
				foreach($entry->skin->getAnimations() as $animation){
					$this->putImage($animation->getImage());
					$this->putLInt($animation->getType());
					$this->putLFloat($animation->getFrames());
				}
				$this->putImage($entry->skin->getCapeData());
				$this->putString($entry->skin->getGeometryData());
				$this->putString($entry->skin->getAnimationData());
				$this->putBool($entry->skin->getPremium());
				$this->putBool($entry->skin->getPersona());
				$this->putBool($entry->skin->getCapeOnClassic());
				$this->putString($entry->skin->getCapeId());
				$this->putString($entry->skin->getFullSkinId());

				$this->putBool($entry->isTeacher);
				$this->putBool($entry->isHost);
			}else{
				$this->putUUID($entry->uuid);
			}
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePlayerList($this);
	}
}
