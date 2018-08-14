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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

class Villager extends Creature implements NPC, Ageable{
	public const PROFESSION_FARMER = 0;
	public const PROFESSION_LIBRARIAN = 1;
	public const PROFESSION_PRIEST = 2;
	public const PROFESSION_BLACKSMITH = 3;
	public const PROFESSION_BUTCHER = 4;

	/**
	 * Can be used in @see $traderName
	 * Automatically translated on the client side
	 */
	public const DEFAULT_NAME = "entity.villager.name";
	public const ARMORER = "entity.villager.armor";
	public const BUTCHER = "entity.villager.butcher";
	public const CARTOGRAPHER = "entity.villager.cartographer";
	public const CLERIC = "entity.villager.cleric";
	public const FARMER = "entity.villager.farmer";
	public const FISHERMAN = "entity.villager.fisherman";
	public const FLETCHER = "entity.villager.fletcher";
	public const LEATHERWORKER = "entity.villager.leather";
	public const LIBRARIAN = "entity.villager.librarian";
	public const SHEPHERD = "entity.villager.shepherd";
	public const TOOL_SMITH = "entity.villager.tool";
	public const WEAPON_SMITH = "entity.villager.weapon";

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

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		/** @var int $profession */
		$profession = $nbt->getInt("Profession", self::PROFESSION_FARMER);

		if($profession > 4 or $profession < 0){
			$profession = self::PROFESSION_FARMER;
		}

		$this->setProfession($profession);
		$this->setCanTrade((bool) $this->namedtag->getByte("CanTrade", 0));
		$this->setTraderName($this->namedtag->getString("TraderName", $this->getNameTag()));
		$this->recipes = $this->namedtag->getListTag($name = TradeRecipe::TAG_RECIPES) ?? new ListTag($name, []);
	}
  
	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setInt("Profession", $this->getProfession());
		$nbt->setByte("CanTrade", (int) $this->canTrade());
		$nbt->setString("TraderName", $this->getTraderName());
		$nbt->setTag($this->getRecipes());

		return $nbt;
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

	public function canTrade() : bool{
		return $this->canTrade;
	}

	public function setTradingPlayer(int $entityRuntimeId) : void{
		$this->propertyManager->setLong(self::DATA_TRADING_PLAYER_EID, $entityRuntimeId);
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
