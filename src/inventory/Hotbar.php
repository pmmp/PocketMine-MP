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

namespace pocketmine\inventory;

use pocketmine\utils\ObjectSet;

final class Hotbar{
	protected int $selectedIndex = 0;

	/**
	 * @var \Closure[]|ObjectSet
	 * @phpstan-var ObjectSet<\Closure(int $oldIndex) : void>
	 */
	protected ObjectSet $selectedIndexChangeListeners;

	public function __construct(
		private int $size = 9
	){
		$this->selectedIndexChangeListeners = new ObjectSet();
	}

	public function isHotbarSlot(int $slot) : bool{
		return $slot >= 0 && $slot < $this->getSize();
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private function throwIfNotHotbarSlot(int $slot) : void{
		if(!$this->isHotbarSlot($slot)){
			throw new \InvalidArgumentException("$slot is not a valid hotbar slot index (expected 0 - " . ($this->getSize() - 1) . ")");
		}
	}

	/**
	 * Returns the hotbar slot number the holder is currently holding.
	 */
	public function getSelectedIndex() : int{
		return $this->selectedIndex;
	}

	/**
	 * Sets which hotbar slot the player is currently loading.
	 *
	 * @param int $hotbarSlot 0-8 index of the hotbar slot to hold
	 *
	 * @throws \InvalidArgumentException if the hotbar slot is out of range
	 */
	public function setSelectedIndex(int $hotbarSlot) : void{
		$this->throwIfNotHotbarSlot($hotbarSlot);

		$oldIndex = $this->selectedIndex;
		$this->selectedIndex = $hotbarSlot;

		foreach($this->selectedIndexChangeListeners as $callback){
			$callback($oldIndex);
		}
	}

	/**
	 * @return \Closure[]|ObjectSet
	 * @phpstan-return ObjectSet<\Closure(int $oldIndex) : void>
	 */
	public function getSelectedIndexChangeListeners() : ObjectSet{ return $this->selectedIndexChangeListeners; }

	/**
	 * Returns the number of slots in the hotbar.
	 */
	public function getSize() : int{
		return $this->size;
	}
}
