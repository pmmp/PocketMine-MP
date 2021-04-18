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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use function base64_encode;
use function count;

final class ItemStack implements \JsonSerializable{

	/** @var int */
	private $id;
	/** @var int */
	private $meta;
	/** @var int */
	private $count;
	/** @var int */
	private $blockRuntimeId;
	/** @var string[] */
	private $canPlaceOn;
	/** @var string[] */
	private $canDestroy;
	/** @var CompoundTag|null */
	private $nbt;
	/** @var int|null */
	private $shieldBlockingTick;

	/**
	 * @param string[] $canPlaceOn
	 * @param string[] $canDestroy
	 */
	public function __construct(int $id, int $meta, int $count, int $blockRuntimeId, ?CompoundTag $nbt, array $canPlaceOn, array $canDestroy, ?int $shieldBlockingTick = null){
		$this->id = $id;
		$this->meta = $meta;
		$this->count = $count;
		$this->blockRuntimeId = $blockRuntimeId;
		$this->canPlaceOn = $canPlaceOn;
		$this->canDestroy = $canDestroy;
		$this->nbt = $nbt;
		$this->shieldBlockingTick = $shieldBlockingTick;
	}

	public static function null() : self{
		return new self(0, 0, 0, 0, null, [], [], null);
	}

	public function getId() : int{
		return $this->id;
	}

	public function getMeta() : int{
		return $this->meta;
	}

	public function getCount() : int{
		return $this->count;
	}

	public function getBlockRuntimeId() : int{ return $this->blockRuntimeId; }

	/**
	 * @return string[]
	 */
	public function getCanPlaceOn() : array{
		return $this->canPlaceOn;
	}

	/**
	 * @return string[]
	 */
	public function getCanDestroy() : array{
		return $this->canDestroy;
	}

	public function getNbt() : ?CompoundTag{
		return $this->nbt;
	}

	public function getShieldBlockingTick() : ?int{
		return $this->shieldBlockingTick;
	}

	public function equals(ItemStack $itemStack) : bool{
		return
			$this->id === $itemStack->id &&
			$this->meta === $itemStack->meta &&
			$this->count === $itemStack->count &&
			$this->blockRuntimeId === $itemStack->blockRuntimeId &&
			$this->canPlaceOn === $itemStack->canPlaceOn &&
			$this->canDestroy === $itemStack->canDestroy &&
			$this->shieldBlockingTick === $itemStack->shieldBlockingTick && (
				$this->nbt === $itemStack->nbt || //this covers null === null and fast object identity
				($this->nbt !== null && $itemStack->nbt !== null && $this->nbt->equals($itemStack->nbt))
			);
	}

	/** @return mixed[] */
	public function jsonSerialize() : array{
		$result = [
			"id" => $this->id,
			"meta" => $this->meta,
			"count" => $this->count,
			"blockRuntimeId" => $this->blockRuntimeId,
		];
		if(count($this->canPlaceOn) > 0){
			$result["canPlaceOn"] = $this->canPlaceOn;
		}
		if(count($this->canDestroy) > 0){
			$result["canDestroy"] = $this->canDestroy;
		}
		if($this->shieldBlockingTick !== null){
			$result["shieldBlockingTick"] = $this->shieldBlockingTick;
		}
		if($this->nbt !== null){
			$result["nbt"] = base64_encode((new NetworkNbtSerializer())->write(new TreeRoot($this->nbt)));
		}
		return $result;
	}
}
