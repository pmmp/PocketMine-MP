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

use pocketmine\block\Campfire as BlockCampfire;
use pocketmine\block\inventory\CampfireInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\world\World;

class Campfire extends Spawnable implements Container{
	use ContainerTrait;

	private const TAG_FIRST_INPUT_ITEM = "Item1"; //TAG_Compound
	private const TAG_SECOND_INPUT_ITEM = "Item2"; //TAG_Compound
	private const TAG_THIRD_INPUT_ITEM = "Item3"; //TAG_Compound
	private const TAG_FOURTH_INPUT_ITEM = "Item4"; //TAG_Compound

	private const TAG_FIRST_COOKING_TIME = "ItemTime1"; //TAG_Int
	private const TAG_SECOND_COOKING_TIME = "ItemTime2"; //TAG_Int
	private const TAG_THIRD_COOKING_TIME = "ItemTime3"; //TAG_Int
	private const TAG_FOURTH_COOKING_TIME = "ItemTime4"; //TAG_Int

	protected CampfireInventory $inventory;
	/** @var array<int, int> */
	private array $cookingTimes = [];

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new CampfireInventory($this->position);
		$this->inventory->getListeners()->add(CallbackInventoryListener::onAnyChange(
			static function(Inventory $unused) use ($world, $pos) : void{
				$block = $world->getBlock($pos);
				if($block instanceof BlockCampfire){
					$world->setBlock($pos, $block);
				}
			})
		);
	}

	public function getInventory() : CampfireInventory{
		return $this->inventory;
	}

	public function getRealInventory() : CampfireInventory{
		return $this->inventory;
	}

	/**
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function getCookingTimes() : array{
		return $this->cookingTimes;
	}

	/**
	 * @param int[] $cookingTimes
	 * @phpstan-param array<int, int> $cookingTimes
	 */
	public function setCookingTimes(array $cookingTimes) : void{
		$this->cookingTimes = $cookingTimes;
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$items = [];
		$listeners = $this->inventory->getListeners()->toArray();
		$this->inventory->getListeners()->remove(...$listeners); //prevent any events being fired by initialization

		foreach([
			[0, self::TAG_FIRST_INPUT_ITEM, self::TAG_FIRST_COOKING_TIME],
			[1, self::TAG_SECOND_INPUT_ITEM, self::TAG_SECOND_COOKING_TIME],
			[2, self::TAG_THIRD_INPUT_ITEM, self::TAG_THIRD_COOKING_TIME],
			[3, self::TAG_FOURTH_INPUT_ITEM, self::TAG_FOURTH_COOKING_TIME],
		] as [$slot, $itemTag, $cookingTimeTag]){
			if(($tag = $nbt->getTag($itemTag)) instanceof CompoundTag){
				$items[$slot] = Item::nbtDeserialize($tag);
			}
			if(($tag = $nbt->getTag($cookingTimeTag)) instanceof IntTag){
				$this->cookingTimes[$slot] = $tag->getValue();
			}
		}
		$this->inventory->setContents($items);
		$this->inventory->getListeners()->add(...$listeners);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		foreach([
			[0, self::TAG_FIRST_INPUT_ITEM, self::TAG_FIRST_COOKING_TIME],
			[1, self::TAG_SECOND_INPUT_ITEM, self::TAG_SECOND_COOKING_TIME],
			[2, self::TAG_THIRD_INPUT_ITEM, self::TAG_THIRD_COOKING_TIME],
			[3, self::TAG_FOURTH_INPUT_ITEM, self::TAG_FOURTH_COOKING_TIME],
		] as [$slot, $itemTag, $cookingTimeTag]){
			$item = $this->inventory->getItem($slot);
			if(!$item->isNull()){
				$nbt->setTag($itemTag, $item->nbtSerialize());
				if(isset($this->cookingTimes[$slot])){
					$nbt->setInt($cookingTimeTag, $this->cookingTimes[$slot]);
				}
			}
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		foreach([
			0 => self::TAG_FIRST_INPUT_ITEM,
			1 => self::TAG_SECOND_INPUT_ITEM,
			2 => self::TAG_THIRD_INPUT_ITEM,
			3 => self::TAG_FOURTH_INPUT_ITEM
		] as $slot => $tag){
			$item = $this->inventory->getItem($slot);
			if(!$item->isNull()){
				$nbt->setTag($tag, TypeConverter::getInstance()->getItemTranslator()->toNetworkNbt($item));
			}
		}
	}
}
