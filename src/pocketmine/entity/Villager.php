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

namespace pocketmine\entity;

use pocketmine\inventory\TradeRecipe;
use pocketmine\nbt\tag\ListTag;

class Villager extends Creature implements NPC, Ageable{
	public const PROFESSION_FARMER = 0;
	public const PROFESSION_LIBRARIAN = 1;
	public const PROFESSION_PRIEST = 2;
	public const PROFESSION_BLACKSMITH = 3;
	public const PROFESSION_BUTCHER = 4;

	public const NETWORK_ID = self::VILLAGER;

	public $width = 0.6;
	public $height = 1.8;

	/** @var bool */
	protected $canTrade;
	/** @var string */
	protected $traderName;
	/** @var ListTag */
	protected $recipes;

	public function getName() : string{
		return "Villager";
	}

	protected function initEntity() : void{
		parent::initEntity();

		/** @var int $profession */
		$profession = $this->namedtag->getInt("Profession", self::PROFESSION_FARMER);

		if($profession > 4 or $profession < 0){
			$profession = self::PROFESSION_FARMER;
		}

		$this->setProfession($profession);
		$this->setCanTrade((bool) $this->namedtag->getByte("CanTrade", 0));
		$this->setTraderName($this->namedtag->getString("TraderName", $this->getNameTag()));
		$this->recipes = $this->namedtag->getListTag($name = TradeRecipe::TAG_RECIPES) ?? new ListTag($name, []);
	}

	public function saveNBT() : void{
		parent::saveNBT();
		$this->namedtag->setInt("Profession", $this->getProfession());
		$this->namedtag->setByte("CanTrade", (int) $this->isCanTrade());
		$this->namedtag->setString("TraderName", $this->getTraderName());
		$this->namedtag->setTag(new ListTag(TradeRecipe::TAG_RECIPES, $this->getRecipes()));
	}

	/**
	 * Sets the villager profession
	 *
	 * @param int $profession
	 */
	public function setProfession(int $profession) : void{
		$this->propertyManager->setInt(self::DATA_VARIANT, $profession);
	}

	public function getProfession() : int{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function isBaby() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_BABY);
	}

	public function setCanTrade(bool $value = true) : void{
		$this->canTrade = $value;
	}

	public function isCanTrade() : bool{
		return $this->canTrade;
	}

	public function setPlayerEntityRuntimeId(int $entityRuntimeId) : void{
		$this->propertyManager->setLong(self::DATA_TRADING_PLAYER_EID, $entityRuntimeId);
	}

	public function removePlayerEntityRuntimeId() : void{
		$this->propertyManager->removeProperty(self::DATA_TRADING_PLAYER_EID);
	}

	public function setTraderName(string $traderName) : void{
		$this->traderName = $traderName;
	}

	public function getTraderName() : string{
		return $this->traderName;
	}

	public function getRecipes() : ListTag{
		return $this->recipes;
	}

	public function setRecipes(TradeRecipe ...$recipes) : void{
		$list = new ListTag(TradeRecipe::TAG_RECIPES);
		foreach($recipes as $recipe){
			$list->push($recipe->toNBT());
		}
		$this->recipes = $list;
	}
}
