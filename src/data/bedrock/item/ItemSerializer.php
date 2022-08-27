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

namespace pocketmine\data\bedrock\item;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\data\bedrock\block\BlockStateSerializeException;
use pocketmine\data\bedrock\block\BlockStateSerializer;
use pocketmine\data\bedrock\item\SavedItemData as Data;
use pocketmine\item\CoralFan;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\VanillaItems as Items;
use pocketmine\utils\AssumptionFailedError;
use function class_parents;
use function get_class;

final class ItemSerializer{
	/**
	 * These callables actually accept Item, but for the sake of type completeness, it has to be never, since we can't
	 * describe the bottom type of a type hierarchy only containing Item.
	 *
	 * @var \Closure[][]
	 * @phpstan-var array<int, array<class-string, \Closure(never) : Data>>
	 */
	private array $itemSerializers = [];

	/**
	 * @var \Closure[][]
	 * @phpstan-var array<int, array<class-string, \Closure(never) : Data>>
	 */
	private array $blockItemSerializers = [];

	public function __construct(
		private BlockStateSerializer $blockStateSerializer
	){
		$this->registerSpecialBlockSerializers();
		new ItemSerializerDeserializerRegistrar(null, $this);
	}

	/**
	 * @phpstan-template TItemType of Item
	 * @phpstan-param TItemType $item
	 * @phpstan-param \Closure(TItemType) : Data $serializer
	 */
	public function map(Item $item, \Closure $serializer) : void{
		$index = $item->getTypeId();
		if(isset($this->itemSerializers[$index])){
			//TODO: REMOVE ME
			throw new AssumptionFailedError("Registering the same item twice!");
		}
		$this->itemSerializers[$index][get_class($item)] = $serializer;
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $block
	 * @phpstan-param \Closure(TBlockType) : Data $serializer
	 */
	public function mapBlock(Block $block, \Closure $serializer) : void{
		$index = $block->getTypeId();
		if(isset($this->blockItemSerializers[$index])){
			throw new AssumptionFailedError("Registering the same blockitem twice!");
		}
		$this->blockItemSerializers[$index][get_class($block)] = $serializer;
	}

	/**
	 * @phpstan-template TItemType of Item
	 * @phpstan-param TItemType $item
	 *
	 * @throws ItemTypeSerializeException
	 */
	public function serializeType(Item $item) : Data{
		if($item->isNull()){
			throw new \InvalidArgumentException("Cannot serialize a null itemstack");
		}
		if($item instanceof ItemBlock){
			$data = $this->serializeBlockItem($item->getBlock());
		}else{
			$index = $item->getTypeId();

			$locatedSerializer = $this->itemSerializers[$index][get_class($item)] ?? null;
			if($locatedSerializer === null){
				$parents = class_parents($item);
				if($parents !== false){
					foreach($parents as $parent){
						if(isset($this->itemSerializers[$index][$parent])){
							$locatedSerializer = $this->itemSerializers[$index][$parent];
							break;
						}
					}
				}
			}

			if($locatedSerializer === null){
				throw new ItemTypeSerializeException("No serializer registered for " . get_class($item) . " ($index) " . $item->getName());
			}

			/**
			 * @var \Closure $serializer
			 * @phpstan-var \Closure(TItemType) : Data $serializer
			 */
			$serializer = $locatedSerializer;

			/** @var Data $data */
			$data = $serializer($item);
		}

		if($item->hasNamedTag()){
			$resultTag = $item->getNamedTag();
			$extraTag = $data->getTag();
			if($extraTag !== null){
				$resultTag = $resultTag->merge($extraTag);
			}
			$data = new Data($data->getName(), $data->getMeta(), $data->getBlock(), $resultTag);
		}

		return $data;
	}

	public function serializeStack(Item $item, ?int $slot = null) : SavedItemStackData{
		return new SavedItemStackData(
			$this->serializeType($item),
			$item->getCount(),
			$slot,
			null,
			[], //we currently represent canDestroy and canPlaceOn via NBT, like PC
			[]
		);
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $block
	 *
	 * @throws ItemTypeSerializeException
	 */
	private function serializeBlockItem(Block $block) : Data{
		$index = $block->getTypeId();

		$locatedSerializer = $this->blockItemSerializers[$index][get_class($block)] ?? null;
		if($locatedSerializer === null){
			$parents = class_parents($block);
			if($parents !== false){
				foreach($parents as $parent){
					if(isset($this->blockItemSerializers[$index][$parent])){
						$locatedSerializer = $this->blockItemSerializers[$index][$parent];
						break;
					}
				}
			}
		}

		if($locatedSerializer !== null){
			/** @phpstan-var \Closure(TBlockType) : Data $serializer */
			$serializer = $locatedSerializer;
			$data = $serializer($block);
		}else{
			$data = $this->standardBlock($block);
		}

		return $data;
	}

	/**
	 * @throws ItemTypeSerializeException
	 */
	private function standardBlock(Block $block) : Data{
		try{
			$blockStateData = $this->blockStateSerializer->serialize($block->getStateId());
		}catch(BlockStateSerializeException $e){
			throw new ItemTypeSerializeException($e->getMessage(), 0, $e);
		}

		$itemNameId = BlockItemIdMap::getInstance()->lookupItemId($blockStateData->getName()) ?? $blockStateData->getName();

		return new Data($itemNameId, 0, $blockStateData);
	}

	private function registerSpecialBlockSerializers() : void{
		//these are encoded as regular blocks, but they have to be accounted for explicitly since they don't use ItemBlock
		//Bamboo->getBlock() returns BambooSapling :(
		$this->map(Items::BAMBOO(), fn() => $this->standardBlock(Blocks::BAMBOO()));
		$this->map(Items::CORAL_FAN(), fn(CoralFan $item) => $this->standardBlock($item->getBlock()));
	}
}
