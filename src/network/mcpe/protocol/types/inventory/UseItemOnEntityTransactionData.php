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

namespace pocketmine\network\mcpe\protocol\types\inventory;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;

class UseItemOnEntityTransactionData extends TransactionData{
	public const ACTION_INTERACT = 0;
	public const ACTION_ATTACK = 1;

	/** @var int */
	private $entityRuntimeId;
	/** @var int */
	private $actionType;
	/** @var int */
	private $hotbarSlot;
	/** @var Item */
	private $itemInHand;
	/** @var Vector3 */
	private $playerPos;
	/** @var Vector3 */
	private $clickPos;

	/**
	 * @return int
	 */
	public function getEntityRuntimeId() : int{
		return $this->entityRuntimeId;
	}

	/**
	 * @return int
	 */
	public function getActionType() : int{
		return $this->actionType;
	}

	/**
	 * @return int
	 */
	public function getHotbarSlot() : int{
		return $this->hotbarSlot;
	}

	/**
	 * @return Item
	 */
	public function getItemInHand() : Item{
		return $this->itemInHand;
	}

	/**
	 * @return Vector3
	 */
	public function getPlayerPos() : Vector3{
		return $this->playerPos;
	}

	/**
	 * @return Vector3
	 */
	public function getClickPos() : Vector3{
		return $this->clickPos;
	}

	public function getTypeId() : int{
		return InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY;
	}

	protected function decodeData(NetworkBinaryStream $stream) : void{
		$this->entityRuntimeId = $stream->getEntityRuntimeId();
		$this->actionType = $stream->getUnsignedVarInt();
		$this->hotbarSlot = $stream->getVarInt();
		$this->itemInHand = $stream->getSlot();
		$this->playerPos = $stream->getVector3();
		$this->clickPos = $stream->getVector3();
	}

	protected function encodeData(NetworkBinaryStream $stream) : void{
		$stream->putEntityRuntimeId($this->entityRuntimeId);
		$stream->putUnsignedVarInt($this->actionType);
		$stream->putVarInt($this->hotbarSlot);
		$stream->putSlot($this->itemInHand);
		$stream->putVector3($this->playerPos);
		$stream->putVector3($this->clickPos);
	}

	public static function new(array $actions, int $entityRuntimeId, int $actionType, int $hotbarSlot, Item $itemInHand, Vector3 $playerPos, Vector3 $clickPos) : self{
		$result = new self;
		$result->actions = $actions;
		$result->entityRuntimeId = $entityRuntimeId;
		$result->actionType = $actionType;
		$result->hotbarSlot = $hotbarSlot;
		$result->itemInHand = $itemInHand;
		$result->playerPos = $playerPos;
		$result->clickPos = $clickPos;
		return $result;
	}
}
