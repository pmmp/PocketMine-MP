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

use pocketmine\item\Item;
use pocketmine\utils\Utils;

class CallbackInventoryListener implements InventoryListener{

	//TODO: turn the closure signatures into type aliases when PHPStan supports them

	/**
	 * @phpstan-param (\Closure(Inventory, int, Item) : void)|null $onSlotChange
	 * @phpstan-param (\Closure(Inventory, Item[]) : void)|null $onContentChange
	 */
	public function __construct(
		private ?\Closure $onSlotChange,
		private ?\Closure $onContentChange
	){
		if($onSlotChange !== null){
			Utils::validateCallableSignature(function(Inventory $inventory, int $slot, Item $oldItem) : void{}, $onSlotChange);
		}
		if($onContentChange !== null){
			Utils::validateCallableSignature(function(Inventory $inventory, array $oldContents) : void{}, $onContentChange);
		}
	}

	/**
	 * @phpstan-param \Closure(Inventory) : void $onChange
	 */
	public static function onAnyChange(\Closure $onChange) : self{
		return new self(
			static function(Inventory $inventory, int $unused, Item $unusedB) use ($onChange) : void{
				$onChange($inventory);
			},
			static function(Inventory $inventory, array $unused) use ($onChange) : void{
				$onChange($inventory);
			}
		);
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem) : void{
		if($this->onSlotChange !== null){
			($this->onSlotChange)($inventory, $slot, $oldItem);
		}
	}

	/**
	 * @param Item[] $oldContents
	 */
	public function onContentChange(Inventory $inventory, array $oldContents) : void{
		if($this->onContentChange !== null){
			($this->onContentChange)($inventory, $oldContents);
		}
	}
}
