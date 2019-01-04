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
use function count;

class InventoryTransactionPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_TRANSACTION_PACKET;

	public const TYPE_NORMAL = 0;
	public const TYPE_MISMATCH = 1;
	public const TYPE_USE_ITEM = 2;
	public const TYPE_USE_ITEM_ON_ENTITY = 3;
	public const TYPE_RELEASE_ITEM = 4;

	public const USE_ITEM_ACTION_CLICK_BLOCK = 0;
	public const USE_ITEM_ACTION_CLICK_AIR = 1;
	public const USE_ITEM_ACTION_BREAK_BLOCK = 2;

	public const RELEASE_ITEM_ACTION_RELEASE = 0; //bow shoot
	public const RELEASE_ITEM_ACTION_CONSUME = 1; //eat food, drink potion

	public const USE_ITEM_ON_ENTITY_ACTION_INTERACT = 0;
	public const USE_ITEM_ON_ENTITY_ACTION_ATTACK = 1;

	/** @var int */
	public $transactionType;

	/**
	 * @var bool
	 * NOTE: THIS FIELD DOES NOT EXIST IN THE PROTOCOL, it's merely used for convenience for PocketMine-MP to easily
	 * determine whether we're doing a crafting transaction.
	 */
	public $isCraftingPart = false;
	/**
	 * @var bool
	 * NOTE: THIS FIELD DOES NOT EXIST IN THE PROTOCOL, it's merely used for convenience for PocketMine-MP to easily
	 * determine whether we're doing a crafting transaction.
	 */
	public $isFinalCraftingPart = false;

	/** @var NetworkInventoryAction[] */
	public $actions = [];

	/** @var \stdClass */
	public $trData;

	protected function decodePayload(){
		$this->transactionType = $this->getUnsignedVarInt();

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->actions[] = (new NetworkInventoryAction())->read($this);
		}

		$this->trData = new \stdClass();

		switch($this->transactionType){
			case self::TYPE_NORMAL:
			case self::TYPE_MISMATCH:
				//Regular ComplexInventoryTransaction doesn't read any extra data
				break;
			case self::TYPE_USE_ITEM:
				$this->trData->actionType = $this->getUnsignedVarInt();
				$this->getBlockPosition($this->trData->x, $this->trData->y, $this->trData->z);
				$this->trData->face = $this->getVarInt();
				$this->trData->hotbarSlot = $this->getVarInt();
				$this->trData->itemInHand = $this->getSlot();
				$this->trData->playerPos = $this->getVector3();
				$this->trData->clickPos = $this->getVector3();
				break;
			case self::TYPE_USE_ITEM_ON_ENTITY:
				$this->trData->entityRuntimeId = $this->getEntityRuntimeId();
				$this->trData->actionType = $this->getUnsignedVarInt();
				$this->trData->hotbarSlot = $this->getVarInt();
				$this->trData->itemInHand = $this->getSlot();
				$this->trData->playerPos = $this->getVector3();
				$this->trData->clickPos = $this->getVector3();
				break;
			case self::TYPE_RELEASE_ITEM:
				$this->trData->actionType = $this->getUnsignedVarInt();
				$this->trData->hotbarSlot = $this->getVarInt();
				$this->trData->itemInHand = $this->getSlot();
				$this->trData->headPos = $this->getVector3();
				break;
			default:
				throw new \UnexpectedValueException("Unknown transaction type $this->transactionType");
		}
	}

	protected function encodePayload(){
		$this->putUnsignedVarInt($this->transactionType);

		$this->putUnsignedVarInt(count($this->actions));
		foreach($this->actions as $action){
			$action->write($this);
		}

		switch($this->transactionType){
			case self::TYPE_NORMAL:
			case self::TYPE_MISMATCH:
				break;
			case self::TYPE_USE_ITEM:
				$this->putUnsignedVarInt($this->trData->actionType);
				$this->putBlockPosition($this->trData->x, $this->trData->y, $this->trData->z);
				$this->putVarInt($this->trData->face);
				$this->putVarInt($this->trData->hotbarSlot);
				$this->putSlot($this->trData->itemInHand);
				$this->putVector3($this->trData->playerPos);
				$this->putVector3($this->trData->clickPos);
				break;
			case self::TYPE_USE_ITEM_ON_ENTITY:
				$this->putEntityRuntimeId($this->trData->entityRuntimeId);
				$this->putUnsignedVarInt($this->trData->actionType);
				$this->putVarInt($this->trData->hotbarSlot);
				$this->putSlot($this->trData->itemInHand);
				$this->putVector3($this->trData->playerPos);
				$this->putVector3($this->trData->clickPos);
				break;
			case self::TYPE_RELEASE_ITEM:
				$this->putUnsignedVarInt($this->trData->actionType);
				$this->putVarInt($this->trData->hotbarSlot);
				$this->putSlot($this->trData->itemInHand);
				$this->putVector3($this->trData->headPos);
				break;
			default:
				throw new \InvalidArgumentException("Unknown transaction type $this->transactionType");
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleInventoryTransaction($this);
	}
}
