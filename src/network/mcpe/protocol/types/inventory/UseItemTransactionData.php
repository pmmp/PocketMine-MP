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
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

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
	/** @var ItemStack */
	private $itemInHand;
	/** @var Vector3 */
	private $playerPos;
	/** @var Vector3 */
	private $clickPos;
	/** @var int */
	private $blockRuntimeId;

	public function getActionType() : int{
		return $this->actionType;
	}

	public function getBlockPos() : Vector3{
		return $this->blockPos;
	}

	public function getFace() : int{
		return $this->face;
	}

	public function getHotbarSlot() : int{
		return $this->hotbarSlot;
	}

	public function getItemInHand() : ItemStack{
		return $this->itemInHand;
	}

	public function getPlayerPos() : Vector3{
		return $this->playerPos;
	}

	public function getClickPos() : Vector3{
		return $this->clickPos;
	}

	public function getBlockRuntimeId() : int{
		return $this->blockRuntimeId;
	}

	public function getTypeId() : int{
		return InventoryTransactionPacket::TYPE_USE_ITEM;
	}

	protected function decodeData(PacketSerializer $stream) : void{
		$this->actionType = $stream->getUnsignedVarInt();
		$x = $y = $z = 0;
		$stream->getBlockPosition($x, $y, $z);
		$this->blockPos = new Vector3($x, $y, $z);
		$this->face = $stream->getVarInt();
		$this->hotbarSlot = $stream->getVarInt();
		$this->itemInHand = $stream->getSlot();
		$this->playerPos = $stream->getVector3();
		$this->clickPos = $stream->getVector3();
		$this->blockRuntimeId = $stream->getUnsignedVarInt();
	}

	protected function encodeData(PacketSerializer $stream) : void{
		$stream->putUnsignedVarInt($this->actionType);
		$stream->putBlockPosition($this->blockPos->x, $this->blockPos->y, $this->blockPos->z);
		$stream->putVarInt($this->face);
		$stream->putVarInt($this->hotbarSlot);
		$stream->putSlot($this->itemInHand);
		$stream->putVector3($this->playerPos);
		$stream->putVector3($this->clickPos);
		$stream->putUnsignedVarInt($this->blockRuntimeId);
	}

	/**
	 * @param NetworkInventoryAction[] $actions
	 */
	public static function new(array $actions, int $actionType, Vector3 $blockPos, int $face, int $hotbarSlot, ItemStack $itemInHand, Vector3 $playerPos, Vector3 $clickPos, int $blockRuntimeId) : self{
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
