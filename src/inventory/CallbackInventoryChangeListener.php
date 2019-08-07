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

use pocketmine\utils\Utils;

class CallbackInventoryChangeListener implements InventoryChangeListener{

	/** @var \Closure|null */
	private $onSlotChangeCallback;
	/** @var \Closure|null */
	private $onContentChangeCallback;

	public function __construct(?\Closure $onSlotChange, ?\Closure $onContentChange){
		if($onSlotChange !== null){
			Utils::validateCallableSignature(function(Inventory	$inventory, int $slot){}, $onSlotChange);
		}
		if($onContentChange !== null){
			Utils::validateCallableSignature(function(Inventory $inventory){}, $onContentChange);
		}

		$this->onSlotChangeCallback = $onSlotChange;
		$this->onContentChangeCallback = $onContentChange;
	}

	public static function onAnyChange(\Closure $onChange) : self{
		return new self(
			static function(Inventory $inventory, int $unused) use ($onChange) : void{
				$onChange($inventory);
			},
			static function(Inventory $inventory) use ($onChange) : void{
				$onChange($inventory);
			}
		);
	}

	public function onSlotChange(Inventory $inventory, int $slot) : void{
		($this->onSlotChangeCallback)($inventory, $slot);
	}

	public function onContentChange(Inventory $inventory) : void{
		($this->onContentChangeCallback)($inventory);
	}
}
