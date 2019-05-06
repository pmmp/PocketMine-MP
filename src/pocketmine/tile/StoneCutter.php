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

namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\inventory\StoneCutterInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryEventProcessor;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use function ceil;
use function max;

class StoneCutter extends Spawnable implements InventoryHolder, Container, Nameable{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_RECIPES = [];

	/** @var StoneCutterInventory */
	protected $inventory;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
	}

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->loadName($nbt);

		$this->inventory = new StoneCutterInventory($this);
		$this->loadItems($nbt);

		$this->inventory->setEventProcessor(new class($this) implements InventoryEventProcessor{
			/** @var StoneCutter */
			private $stonecutter;

			public function __construct(StoneCutter $stonecutter){
				$this->stonecutter = $stonecutter;
			}

			public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem, Item $newItem) : ?Item{
				return $newItem;
				}
			});
		}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}
	
	public function getDefaultName() : string{
		return "Stone Cutter";
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers(true);
			$this->inventory = null;

			parent::close();
		}
	}

	public function getInventory(){
		return $this->inventory;
	}

	public function getRealInventory(){
		return $this->getInventory();
	}

}
