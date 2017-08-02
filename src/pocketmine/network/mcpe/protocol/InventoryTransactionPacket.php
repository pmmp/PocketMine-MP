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

use pocketmine\network\mcpe\NetworkSession;

class InventoryTransactionPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::INVENTORY_TRANSACTION_PACKET;

	const TYPE_USE_ITEM = 2;
	const TYPE_USE_ITEM_ON_ENTITY = 3;
	const TYPE_RELEASE_ITEM = 4;

	const SOURCE_CONTAINER = 0;

	const SOURCE_WORLD = 2;
	const SOURCE_CREATIVE = 3;
	const SOURCE_CRAFTING = 99999;


	public $actions = [];

	public $transactionData;

	public function decodePayload(){
		$type = $this->getUnsignedVarInt();

		$actionCount = $this->getUnsignedVarInt();
		for($i = 0; $i < $actionCount; ++$i){
			$this->actions[] = $this->decodeInventoryAction();
		}

		$this->transactionData = new \stdClass();
		$this->transactionData->transactionType = $type;

		switch($type){
			case 0:
			case 1:
				//Regular ComplexInventoryTransaction doesn't read any extra data
				break;
			case self::TYPE_USE_ITEM:
				$this->transactionData->useItemActionType = $this->getUnsignedVarInt();
				$this->getBlockPosition($this->transactionData->x, $this->transactionData->y, $this->transactionData->z);
				$this->transactionData->face = $this->getVarInt();
				$this->transactionData->hotbarSlot = $this->getVarInt();
				$this->transactionData->itemInHand = $this->getSlot();
				$this->transactionData->playerPos = $this->getVector3Obj();
				$this->transactionData->clickPos = $this->getVector3Obj();
				break;
			case self::TYPE_USE_ITEM_ON_ENTITY:
				$this->transactionData->entityRuntimeId = $this->getEntityRuntimeId();
				$this->transactionData->uvarint1_3 = $this->getUnsignedVarInt();
				$this->transactionData->hotbarSlot = $this->getVarInt();
				$this->transactionData->itemInHand = $this->getSlot();
				$this->transactionData->vector1 = $this->getVector3Obj();
				$this->transactionData->vector2 = $this->getVector3Obj();
				break;
			default:
				throw new \UnexpectedValueException("Unknown transaction type $type");


		}

		//TODO
	}

	public function encodePayload(){
		//TODO
	}

	protected function decodeInventoryAction(){
		$actionBucket = new \stdClass();
		$actionBucket->inventorySource = $this->decodeInventorySource();

		$actionBucket->inventorySlot = $this->getUnsignedVarInt();
		$actionBucket->oldItem = $this->getSlot();
		$actionBucket->newItem = $this->getSlot();
		return $actionBucket;
	}

	protected function decodeInventorySource(){
		$bucket = new \stdClass();
		$bucket->sourceType = $this->getUnsignedVarInt();

		switch($bucket->sourceType){
			case self::SOURCE_CONTAINER:
				$bucket->containerId = $this->getVarInt();
				$bucket->field_2 = 0;
				break;
			case 1: //???
				$bucket->containerId = -1;
				$bucket->field_2 = 0;
				break;
			case self::SOURCE_WORLD:
				$bucket->containerId = -1;
				$bucket->field_2 = $this->getUnsignedVarInt();
				break;
			case self::SOURCE_CREATIVE:
				$bucket->containerId = -1;
				$bucket->field_2 = 0;
				break;
			case self::SOURCE_CRAFTING:
				$bucket->containerId = $this->getVarInt();
				$bucket->field_2 = 0;
				break;
			default:
				throw new \UnexpectedValueException("Unexpected inventory source type $bucket->sourceType");

		}

		return $bucket;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleInventoryTransaction($this);
	}
}