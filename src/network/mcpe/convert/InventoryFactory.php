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

namespace pocketmine\network\mcpe\convert;

use Closure;
use pocketmine\block\CraftingTable;
use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\inventory\BrewingStandInventory;
use pocketmine\block\inventory\EnchantInventory;
use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\inventory\LoomInventory;
use pocketmine\crafting\FurnaceType;
use pocketmine\inventory\Inventory;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use function array_key_exists;

final class InventoryFactory{
	use SingletonTrait;
	
	/**
	 * @var string[] inventory class for a slot offset
	 * @phpstan-var array<int, class-string<Inventory>>
	 */
	private array $slotOffsets = [];
	
	/**
	 * @var int[] core to net converted entries
	 * @phpstan-var array<int, int>
	 */
	private array $netToCoreOffsets = [];
	
	/**
	 * @var Closure[]|int[]
	 * @phpstan-var array<class-string<Inventory>, array<Closure|int>>
	 */
	private array $windowTypes = [];
	
	public function __construct(){
		$this->register(AnvilInventory::class, WindowTypes::ANVIL, UIInventorySlotOffset::ANVIL);
		$this->register(EnchantInventory::class, WindowTypes::ENCHANTMENT, UIInventorySlotOffset::ENCHANTING_TABLE);
		$this->register(LoomInventory::class, WindowTypes::LOOM, UIInventorySlotOffset::LOOM);
		$this->register(FurnaceInventory::class, Closure::fromCallable(function (Inventory $inventory): int{
			return match($inventory->getFurnaceType()->id()){
				FurnaceType::FURNACE()->id() => WindowTypes::FURNACE,
				FurnaceType::BLAST_FURNACE()->id() => WindowTypes::BLAST_FURNACE,
				FurnaceType::SMOKER()->id() => WindowTypes::SMOKER,
				default => throw new AssumptionFailedError("Unreachable")
			};
		}));
		$this->register(BrewingStandInventory::class, WindowTypes::BREWING_STAND);
		$this->register(HopperInventory::class, WindowTypes::HOPPER);
	}
	
	/**
	 * @param Closure|int $windowType the window type of the inventory, Closure for multiple possibilities
	 * @param array<int, int> $slotOffsets net => core
	 * @phpstan-param class-string<Inventory> $className
	 */
	public function register(string $className, Closure|int $windowType, array $slotOffsets = []) : void{
		foreach ($slotOffsets as $netOffset => $coreOffset){
			$this->slotOffsets[$netOffset] = $className;
			$this->netToCoreOffsets[$netOffset] = $coreOffset;
		}
		if ($windowType instanceof Closure){
			Utils::validateCallableSignature(function (Inventory $inventory): int{ return 0; }, $windowType);
		}
		$this->windowTypes[$className] = $windowType;
	}
	
	/**
	 * @param int $offset the (net) slot offset
	 */
	public function getOffsetInventory(int $offset): ?string{
		if (!array_key_exists($offset, $this->slotOffsets)) return null;
		return $this->slotOffsets[$offset];
	}
	
	/**
	 * @param int $netOffset offset to convert
	 */
	public function getCoreOffset(int $netOffset): ?int{
		if (!array_key_exists($netOffset, $this->netToCoreOffsets)) return null;
		return $this->netToCoreOffsets[$netOffset];
	}
	
	public function getWindowType(Inventory $inventory): int {
		if (!array_key_exists($inventory::class, $this->windowTypes)){
			throw new \UnexpectedValueException("Window type of Inventory " . $inventory::class . " was not registered!");
		}
		if (($type = $this->windowTypes[$inventory::class]) instanceof Closure) $type = $type($inventory);
		return $type;
	}
}