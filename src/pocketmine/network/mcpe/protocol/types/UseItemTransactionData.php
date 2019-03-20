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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

class UseItemTransactionData extends TransactionData{
	public const ACTION_CLICK_BLOCK = 0;
	public const ACTION_CLICK_AIR = 1;
	public const ACTION_BREAK_BLOCK = 2;

	/** @var int */
	private $actionType;
	/** @var Vector3 */
	private $blockPos;
	/** @var int */
	private $face;
	/** @var int */
	private $hotbarSlot;
	/** @var Item */
	private $itemInHand;
	/** @var Vector3 */
	private $playerPos;
	/** @var Vector3 */
	private $clickPos;
	/** @var int */
	private $blockRuntimeId;

	/**
	 * @return int
	 */
	public function getActionType() : int{
		return $this->actionType;
	}

	/**
	 * @return Vector3
	 */
	public function getBlockPos() : Vector3{
		return $this->blockPos;
	}

	/**
	 * @return int
	 */
	public function getFace() : int{
		return $this->face;
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

	/**
	 * @return int
	 */
	public function getBlockRuntimeId() : int{
		return $this->blockRuntimeId;
	}

	public function getTypeId() : int{
		return InventoryTransactionPacket::TYPE_USE_ITEM;
	}

	protected function decodeData(NetworkBinaryStream $stream) : void{
		$this->actionType = $stream->getUnsignedVarInt();
		$this->blockPos = new Vector3();
		$stream->getBlockPosition($this->blockPos->x, $this->blockPos->y, $this->blockPos->z);
		$this->face = $stream->getVarInt();
		$this->hotbarSlot = $stream->getVarInt();
		$this->itemInHand = $stream->getSlot();
		$this->playerPos = $stream->getVector3();
		$this->clickPos = $stream->getVector3();
		$this->blockRuntimeId = $stream->getUnsignedVarInt();
	}

	protected function encodeData(NetworkBinaryStream $stream) : void{
		$stream->putUnsignedVarInt($this->actionType);
		$stream->putBlockPosition($this->blockPos->x, $this->blockPos->y, $this->blockPos->z);
		$stream->putVarInt($this->face);
		$stream->putVarInt($this->hotbarSlot);
		$stream->putSlot($this->itemInHand);
		$stream->putVector3($this->playerPos);
		$stream->putVector3($this->clickPos);
		$stream->putUnsignedVarInt($this->blockRuntimeId);
	}

	public static function new(array $actions, int $actionType, Vector3 $blockPos, int $face, int $hotbarSlot, Item $itemInHand, Vector3 $playerPos, Vector3 $clickPos, int $blockRuntimeId) : self{
		$result = new self;
		$result->actions = $actions;
		$result->actionType = $actionType;
		$result->blockPos = $blockPos;
		$result->face = $face;
		$result->hotbarSlot = $hotbarSlot;
		$result->itemInHand = $itemInHand;
		$result->playerPos = $playerPos;
		$result->clickPos = $clickPos;
		$result->blockRuntimeId = $blockRuntimeId;
		return $result;
	}
}
