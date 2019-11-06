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
use pocketmine\utils\SerializedImage;
use pocketmine\utils\SkinAnimation;
use pocketmine\utils\UUID;

class PlayerSkinPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_SKIN_PACKET;

	/** @var UUID */
	public $uuid;
	/** @var Skin */
	public $skin;

	protected function decodePayload(){
		$this->uuid = $this->getUUID();

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

		$this->skin = new Skin(
			$skinId, $skinResourcePatch, $skinData, $animations, $capeData, $geometryData, $animationData, $premium, $persona, $capeOnClassic, $capeId
		);
	}

	protected function encodePayload(){
		$this->putUUID($this->uuid);

		$this->putString($this->skin->getSkinId());
		$this->putString($this->skin->getSkinResourcePatch());
		$this->putImage($this->skin->getSkinData());
		$this->putLInt(count($this->skin->getAnimations()));
		foreach($this->skin->getAnimations() as $animation){
			$this->putImage($animation->getImage());
			$this->putLInt($animation->getType());
			$this->putLFloat($animation->getFrames());
		}
		$this->putImage($this->skin->getCapeData());
		$this->putString($this->skin->getGeometryData());
		$this->putString($this->skin->getAnimationData());
		$this->putBool($this->skin->getPremium());
		$this->putBool($this->skin->getPersona());
		$this->putBool($this->skin->getCapeOnClassic());
		$this->putString($this->skin->getCapeId());
		$this->putString($this->skin->getFullSkinId());
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePlayerSkin($this);
	}
}
