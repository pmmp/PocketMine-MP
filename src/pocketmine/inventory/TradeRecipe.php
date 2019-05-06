<?php

/*
    _____ _                 _        __  __ _____
  / ____| |               | |      |  \/  |  __ \
 | |    | | ___  _   _  __| |______| \  / | |__) |
 | |    | |/ _ \| | | |/ _` |______| |\/| |  ___/
 | |____| | (_) | |_| | (_| |      | |  | | |
  \_____|_|\___/ \__,_|\__,_|      |_|  |_|_|

     Make of Things.
 */

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

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
}
