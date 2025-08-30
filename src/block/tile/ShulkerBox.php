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

namespace pocketmine\block\tile;

use pocketmine\block\BlockTypeIds;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\transaction\action\validator\CallbackSlotValidator;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class ShulkerBox extends Spawnable implements ContainerTile, Nameable{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTileTrait;

	public const TAG_FACING = "facing";

	protected Facing $facing = Facing::NORTH;

	protected Inventory $inventory;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new SimpleInventory(27);

		$this->inventory->getSlotValidators()->add(new CallbackSlotValidator(static function(Inventory $_, Item $item) : ?TransactionValidationException{ //remaining params not needed
			$blockTypeId = ItemTypeIds::toBlockTypeId($item->getTypeId());
			if($blockTypeId === BlockTypeIds::SHULKER_BOX || $blockTypeId === BlockTypeIds::DYED_SHULKER_BOX){
				return new TransactionValidationException("Shulker box inventory cannot contain shulker boxes");
			}

			return null;
		}));
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->loadName($nbt);
		$this->loadItems($nbt);
		//TODO: suspicious use of internal Facing value for storage
		$this->facing = Facing::tryFrom($nbt->getByte(self::TAG_FACING, $this->facing->value)) ?? throw new SavedDataLoadingException("Invalid facing value");
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$this->saveName($nbt);
		$this->saveItems($nbt);
		//TODO: suspicious use of internal Facing value for storage
		$nbt->setByte(self::TAG_FACING, $this->facing->value);
	}

	public function copyDataFromItem(Item $item) : void{
		$this->readSaveData($item->getNamedTag());
		if($item->hasCustomName()){
			$this->setName($item->getCustomName());
		}
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();
			parent::close();
		}
	}

	protected function onBlockDestroyedHook() : void{
		//NOOP override of ContainerTrait - shulker boxes retain their contents when destroyed
	}

	public function getCleanedNBT() : ?CompoundTag{
		$nbt = parent::getCleanedNBT();
		if($nbt !== null){
			$nbt->removeTag(self::TAG_FACING);
		}
		return $nbt;
	}

	public function getFacing() : Facing{
		return $this->facing;
	}

	public function setFacing(Facing $facing) : void{
		$this->facing = $facing;
	}

	public function getInventory() : Inventory{
		return $this->inventory;
	}

	public function getRealInventory() : Inventory{
		return $this->inventory;
	}

	public function getDefaultName() : string{
		return "Shulker Box";
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		//TODO: suspicious use of internal Facing value for network
		$nbt->setByte(self::TAG_FACING, $this->facing->value);
		$this->addNameSpawnData($nbt);
	}
}
