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

use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient;

class TypeConverter{
	private const DAMAGE_TAG = "Damage"; //TAG_Int
	private const DAMAGE_TAG_CONFLICT_RESOLUTION = "___Damage_ProtocolCollisionResolution___";

	/** @var self|null */
	private static $instance;

	private function __construct(){
		//NOOP
	}

	public static function getInstance() : self{
		if(self::$instance === null){
			self::$instance = new self;
		}
		return self::$instance;
	}

	public static function setInstance(self $instance) : void{
		self::$instance = $instance;
	}

	public function coreItemStackToRecipeIngredient(Item $itemStack) : RecipeIngredient{
		$meta = $itemStack->getMeta();
		return new RecipeIngredient($itemStack->getId(), $meta === -1 ? 0x7fff : $meta, $itemStack->getCount());
	}

	public function recipeIngredientToCoreItemStack(RecipeIngredient $ingredient) : Item{
		$meta = $ingredient->getMeta();
		return ItemFactory::get($ingredient->getId(), $meta === 0x7fff ? -1 : $meta, $ingredient->getCount());
	}

	public function coreItemStackToNet(Item $itemStack) : ItemStack{
		$nbt = null;
		if($itemStack->hasNamedTag()){
			$nbt = clone $itemStack->getNamedTag();
		}
		if($itemStack instanceof Durable and $itemStack->getDamage() > 0){
			if($nbt !== null){
				if(($existing = $nbt->getTag(self::DAMAGE_TAG)) !== null){
					$nbt->removeTag(self::DAMAGE_TAG);
					$nbt->setTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION, $existing);
				}
			}else{
				$nbt = new CompoundTag();
			}
			$nbt->setInt(self::DAMAGE_TAG, $itemStack->getDamage());
		}
		$id = $itemStack->getId();
		$meta = $itemStack->getMeta();

		return new ItemStack(
			$id,
			$meta === -1 ? 0x7fff : $meta,
			$itemStack->getCount(),
			$nbt,
			[],
			[],
			$id === ItemIds::SHIELD ? 0 : null
		);
	}

	public function netItemStackToCore(ItemStack $itemStack) : Item{
		$compound = $itemStack->getNbt();
		$meta = $itemStack->getMeta();

		if($compound !== null){
			$compound = clone $compound;
			if($compound->hasTag(self::DAMAGE_TAG, IntTag::class)){
				$meta = $compound->getInt(self::DAMAGE_TAG);
				$compound->removeTag(self::DAMAGE_TAG);
				if($compound->count() === 0){
					$compound = null;
					goto end;
				}
			}
			if(($conflicted = $compound->getTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION)) !== null){
				$compound->removeTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION);
				$compound->setTag(self::DAMAGE_TAG, $conflicted);
			}
		}

		end:
		return ItemFactory::get(
			$itemStack->getId(),
			$meta !== 0x7fff ? $meta : -1,
			$itemStack->getCount(),
			$compound
		);
	}
}