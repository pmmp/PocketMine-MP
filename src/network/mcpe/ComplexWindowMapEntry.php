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

namespace pocketmine\network\mcpe;

use pocketmine\inventory\Inventory;

final class ComplexWindowMapEntry{

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $reverseSlotMap = [];

	/**
	 * @param int[] $slotMap
	 * @phpstan-param array<int, int> $slotMap
	 */
	public function __construct(
		private Inventory $inventory,
		private array $slotMap
	){
		foreach($slotMap as $slot => $index){
			$this->reverseSlotMap[$index] = $slot;
		}
	}

	public function getInventory() : Inventory{ return $this->inventory; }

	/**
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function getSlotMap() : array{ return $this->slotMap; }

	public function mapNetToCore(int $slot) : ?int{
		return $this->slotMap[$slot] ?? null;
	}

	public function mapCoreToNet(int $slot) : ?int{
		return $this->reverseSlotMap[$slot] ?? null;
	}
}
