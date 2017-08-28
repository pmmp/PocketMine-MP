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
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;

class InventoryTransactionPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::INVENTORY_TRANSACTION_PACKET;

	const TYPE_NORMAL = 0;
	const TYPE_MISMATCH = 1;
	const TYPE_USE_ITEM = 2;
	const TYPE_USE_ITEM_ON_ENTITY = 3;
	const TYPE_RELEASE_ITEM = 4;

	const USE_ITEM_ACTION_CLICK_BLOCK = 0;
	const USE_ITEM_ACTION_CLICK_AIR = 1;
	const USE_ITEM_ACTION_BREAK_BLOCK = 2;

	const RELEASE_ITEM_ACTION_RELEASE = 0; //bow shoot
	const RELEASE_ITEM_ACTION_CONSUME = 1; //eat food, drink potion

	const USE_ITEM_ON_ENTITY_ACTION_INTERACT = 0;
	const USE_ITEM_ON_ENTITY_ACTION_ATTACK = 1;

	const SOURCE_CONTAINER = 0;

	const SOURCE_WORLD = 2; //drop/pickup item entity
	const SOURCE_CREATIVE = 3;

	const SOURCE_TODO = 99999;

	/**
	 * These identifiers are used for special slot types for transaction/inventory types that are not yet implemented.
	 * Expect these to change in the future.
	 */
	const SOURCE_TYPE_CRAFTING_ADD_INGREDIENT = -2;
	const SOURCE_TYPE_CRAFTING_REMOVE_INGREDIENT = -3;
	const SOURCE_TYPE_CRAFTING_RESULT = -4;
	const SOURCE_TYPE_CRAFTING_USE_INGREDIENT = -5;

	const SOURCE_TYPE_ANVIL_INPUT = -10;
	const SOURCE_TYPE_ANVIL_MATERIAL = -11;
	const SOURCE_TYPE_ANVIL_RESULT = -12;
	const SOURCE_TYPE_ANVIL_OUTPUT = -13;

	const SOURCE_TYPE_ENCHANT_INPUT = -15;
	const SOURCE_TYPE_ENCHANT_MATERIAL = -16;
	const SOURCE_TYPE_ENCHANT_OUTPUT = -17;

	const SOURCE_TYPE_TRADING_INPUT_1 = -20;
	const SOURCE_TYPE_TRADING_INPUT_2 = -21;
	const SOURCE_TYPE_TRADING_USE_INPUTS = -22;
	const SOURCE_TYPE_TRADING_OUTPUT = -23;

	const SOURCE_TYPE_BEACON = -24;

	const SOURCE_TYPE_CONTAINER_DROP_CONTENTS = -100;


	const ACTION_MAGIC_SLOT_DROP_ITEM = 0;
	const ACTION_MAGIC_SLOT_PICKUP_ITEM = 1;

	const ACTION_MAGIC_SLOT_CREATIVE_DELETE_ITEM = 0;
	const ACTION_MAGIC_SLOT_CREATIVE_CREATE_ITEM = 1;

	/** @var NetworkInventoryAction[] */
	public $actions = [];

	/** @var \stdClass */
	public $transactionData;

	protected function decodePayload(){
		$type = $this->getUnsignedVarInt();

		$actionCount = $this->getUnsignedVarInt();
		for($i = 0; $i < $actionCount; ++$i){
			$this->actions[] = $this->decodeInventoryAction();
		}

		$this->transactionData = new \stdClass();
		$this->transactionData->transactionType = $type;

		switch($type){
			case self::TYPE_NORMAL:
			case self::TYPE_MISMATCH:
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
				$this->transactionData->actionType = $this->getUnsignedVarInt();
				$this->transactionData->hotbarSlot = $this->getVarInt();
				$this->transactionData->itemInHand = $this->getSlot();
				$this->transactionData->vector1 = $this->getVector3Obj();
				$this->transactionData->vector2 = $this->getVector3Obj();
				break;
			case self::TYPE_RELEASE_ITEM:
				$this->transactionData->releaseItemActionType = $this->getUnsignedVarInt();
				$this->transactionData->hotbarSlot = $this->getVarInt();
				$this->transactionData->itemInHand = $this->getSlot();
				$this->transactionData->headPos = $this->getVector3Obj();
				break;
			default:
				throw new \UnexpectedValueException("Unknown transaction type $type");
		}
	}

	protected function encodePayload(){
		//TODO
	}

	protected function decodeInventoryAction(){
		$action = new NetworkInventoryAction();
		$action->read($this);
		return $action;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleInventoryTransaction($this);
	}
}