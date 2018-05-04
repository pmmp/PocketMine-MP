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
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;

class TradeRecipe{
	public const TAG_BUY_A = "buyA";
	public const TAG_BUY_B = "buyB";
	public const TAG_SELL = "sell";
	public const TAG_RECIPES = "Recipes";

	/** @var Item */
	private $output;
	/** @var Item */
	private $buyA;
	/** @var null|Item */
	private $buyB;

	/**
	 * Recipe constructor.
	 * @param Item      $output result of the transaction
	 * @param Item      $buyA   required item #1
	 * @param null|Item $buyB   required item #2 (not necessary)
	 */
	public function __construct(Item $output, Item $buyA, ?Item $buyB = null){
		$this->output = $output;
		$this->buyA = $buyA;
		$this->buyB = $buyB;
	}

	/**
	 * @return CompoundTag
	 */
	public function toNBT() : CompoundTag{
		$nbt = new CompoundTag("", [
			new IntTag("maxUses", 999),
			new ByteTag("rewardExp", 0),
			new IntTag("uses", 0)
		]);
		$this->putItem($nbt, self::TAG_SELL, $this->output);
		$this->putItem($nbt, self::TAG_BUY_A, $this->buyA);
		if($this->buyB !== null){
			$this->putItem($nbt, self::TAG_BUY_B, $this->buyB);
		}
		return $nbt;
	}

	/**
	 * @param CompoundTag $tag
	 * @param string      $tagName
	 * @param Item        $item
	 */
	public function putItem(CompoundTag $tag, string $tagName, Item $item) : void{
		$tag->setTag($item->nbtSerialize(-1, $tagName));
	}

	/**
	 * @param TradeRecipe ...$recipes
	 * @return ListTag
	 */
	public static function createRecipes(TradeRecipe ...$recipes) : ListTag{
		$list = new ListTag(self::TAG_RECIPES);
		foreach($recipes as $recipe){
			$list->push($recipe->toNBT());
		}
		return $list;
	}
}