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

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBinaryStream as PacketSerializer;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;

class UseItemOnEntityTransactionData extends TransactionData{
	public const ACTION_INTERACT = 0;
	public const ACTION_ATTACK = 1;
	public const ACTION_ITEM_INTERACT = 2;

	/** @var int */
	private $entityRuntimeId;
	/** @var int */
	private $actionType;
	/** @var int */
	private $hotbarSlot;
	/** @var ItemStackWrapper */
	private $itemInHand;
	/** @var Vector3 */
	private $playerPos;
	/** @var Vector3 */
	private $clickPos;

	public function getEntityRuntimeId() : int{
		return $this->entityRuntimeId;
	}

	public function getActionType() : int{
		return $this->actionType;
	}

	public function getHotbarSlot() : int{
		return $this->hotbarSlot;
	}

	public function getItemInHand() : ItemStackWrapper{
		return $this->itemInHand;
	}

	public function getPlayerPos() : Vector3{
		return $this->playerPos;
	}

	public function getClickPos() : Vector3{
		return $this->clickPos;
	}

	public function getTypeId() : int{
		return InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY;
	}

	protected function decodeData(PacketSerializer $stream) : void{
		$this->entityRuntimeId = $stream->getEntityRuntimeId();
		$this->actionType = $stream->getUnsignedVarInt();
		$this->hotbarSlot = $stream->getVarInt();
		$this->itemInHand = ItemStackWrapper::read($stream);
		$this->playerPos = $stream->getVector3();
		$this->clickPos = $stream->getVector3();
	}

	protected function encodeData(PacketSerializer $stream) : void{
		$stream->putEntityRuntimeId($this->entityRuntimeId);
		$stream->putUnsignedVarInt($this->actionType);
		$stream->putVarInt($this->hotbarSlot);
		$this->itemInHand->write($stream);
		$stream->putVector3($this->playerPos);
		$stream->putVector3($this->clickPos);
	}

	/**
	 * @param NetworkInventoryAction[] $actions
	 */
	public static function new(array $actions, int $entityRuntimeId, int $actionType, int $hotbarSlot, ItemStackWrapper $itemInHand, Vector3 $playerPos, Vector3 $clickPos) : self{
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
