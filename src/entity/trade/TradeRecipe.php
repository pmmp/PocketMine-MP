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

namespace pocketmine\entity\trade;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;

final class TradeRecipe{

	private const TAG_BUY_A = "buyA";
	private const TAG_BUY_B = "buyB";
	private const TAG_SELL = "sell";
	private const TAG_REWARD_EXP = "rewardExp";
	private const TAG_TIER = "tier";
	private const TAG_TRADER_EXP = "traderExp";
	private const TAG_MAX_USES = "maxUses";
	private const TAG_PRICE_MULTIPLIER_A = "priceMultiplierA";
	private const TAG_PRICE_MULTIPLIER_B = "priceMultiplierB";
	private const TAG_USES = "uses";

	public const TAG_NET_ID = "netId";

	private const DEFAULT_MAX_USES = 16;
	private const DEFAULT_PRICE_MULTIPLIER = 0.05;
	private const DEFAULT_REWARD_EXP = 0;
	private const DEFAULT_TIER = 0;
	private const DEFAULT_TRADER_EXP = 0;
	private const DEFAULT_USES = 0;

	public function __construct(
		private readonly Item $buyA,
		private readonly Item $sell,
		private readonly ?Item $buyB = null,
		private readonly int $maxUses = self::DEFAULT_MAX_USES,
		private readonly float $priceMultiplier = self::DEFAULT_PRICE_MULTIPLIER,
		private readonly int $rewardExp = self::DEFAULT_REWARD_EXP,
		private int $tier = self::DEFAULT_TIER,
		private int $traderExp = self::DEFAULT_TRADER_EXP,
		private int $uses = self::DEFAULT_USES
	){
		if($buyA->isNull() || $sell->isNull() || ($buyB !== null && $buyB->isNull())){
			throw new \InvalidArgumentException("Recipe items cannot be null");
		}
		if($tier < 0){
			throw new \InvalidArgumentException("Tier must be a positive integer");
		}
	}

	public function getBuyA() : Item{
		return $this->buyA;
	}

	public function getBuyB() : ?Item{
		return $this->buyB;
	}

	public function getSell() : Item{
		return $this->sell;
	}

	public function getMaxUses() : int{
		return $this->maxUses;
	}

	public function getPriceMultiplier() : float{
		return $this->priceMultiplier;
	}

	public function getRewardExp() : int{
		return $this->rewardExp;
	}

	public function getTier() : int{
		return $this->tier;
	}

	public function getTraderExp() : int{
		return $this->traderExp;
	}

	public function getUses() : int{
		return $this->uses;
	}

	public function setUses(int $uses) : void{
		$this->uses = $uses;
	}

	public function setTier(int $tier) : void{
		$this->tier = $tier;
	}

	public function setTraderExp(int $traderExp) : void{
		$this->traderExp = $traderExp;
	}

	public function isDisabled() : bool{
		return $this->uses >= $this->maxUses;
	}

	public function nbtSerialize() : CompoundTag{
		$nbt = CompoundTag::create()
			->setTag(self::TAG_BUY_A, $this->buyA->nbtSerialize())
			->setTag(self::TAG_SELL, $this->sell->nbtSerialize())
			->setInt(self::TAG_MAX_USES, $this->maxUses)
			->setFloat(self::TAG_PRICE_MULTIPLIER_A, $this->priceMultiplier)
			->setFloat(self::TAG_PRICE_MULTIPLIER_B, 0.0)
			->setInt(self::TAG_REWARD_EXP, $this->rewardExp)
			->setInt(self::TAG_TIER, $this->tier)
			->setInt(self::TAG_TRADER_EXP, $this->traderExp)
			->setInt(self::TAG_USES, $this->uses);
		if($this->buyB !== null){
			$nbt->setTag(self::TAG_BUY_B, $this->buyB->nbtSerialize());
		}
		return $nbt;
	}

	public static function nbtDeserialize(CompoundTag $nbt) : self{
		$buyATag = $nbt->getCompoundTag(self::TAG_BUY_A);
		$sellTag = $nbt->getCompoundTag(self::TAG_SELL);
		if($buyATag === null || $sellTag === null){
			throw new \InvalidArgumentException("Missing buyA or sell tag in trade recipe");
		}
		$buyA = Item::nbtDeserialize($buyATag);
		$sell = Item::nbtDeserialize($sellTag);
		$buyB = null;
		if(($buyBTag = $nbt->getCompoundTag(self::TAG_BUY_B)) !== null){
			$buyB = Item::nbtDeserialize($buyBTag);
		}
		return new self(
			$buyA,
			$sell,
			$buyB,
			$nbt->getInt(self::TAG_MAX_USES, self::DEFAULT_MAX_USES),
			$nbt->getFloat(self::TAG_PRICE_MULTIPLIER_A, self::DEFAULT_PRICE_MULTIPLIER),
			$nbt->getInt(self::TAG_REWARD_EXP, self::DEFAULT_REWARD_EXP),
			$nbt->getInt(self::TAG_TIER, self::DEFAULT_TIER),
			$nbt->getInt(self::TAG_TRADER_EXP, self::DEFAULT_TRADER_EXP),
			$nbt->getInt(self::TAG_USES, self::DEFAULT_USES)
		);
	}
}
