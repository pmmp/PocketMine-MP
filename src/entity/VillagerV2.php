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

use pocketmine\data\bedrock\VillagerProfessionIdMap;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\profession\VanillaVillagerProfessions;
use pocketmine\entity\profession\VillagerProfession;
use pocketmine\entity\trade\TradeRecipe;
use pocketmine\entity\trade\TradeRecipeData;
use pocketmine\inventory\TradeInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use function array_map;
use function array_rand;
use function array_values;

final class VillagerV2 extends Living implements Ageable{

	private const TAG_PROFESSION = "Profession"; //TAG_Int
	private const TAG_TRADE_TIER = "TradeTier"; //TAG_Int
	private const TAG_OFFERS = "Offers"; //TAG_Compound
	private const TAG_RECIPES = "Recipes"; //TAG_List<TAG_Compound>
	private const TAG_VARIANT = "Variant"; //TAG_Int
	private const TAG_TRADE_EXPERIENCE = "TradeExperience"; //TAG_Int
	private const TAG_TIER_EXP_REQUIREMENTS = "TierExpRequirements"; //TAG_List<TAG_Compound>

	private TradeRecipeData $recipeData;

	private VillagerProfession $profession;

	private bool $baby = false;

	public static function getNetworkTypeId() : string{
		return EntityIds::VILLAGER_V2;
	}

	public function getName() : string{
		return "Villager";
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.9, 0.6);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$professionId = $nbt->getInt(self::TAG_PROFESSION, -1);

		$allProfessions = array_values(VanillaVillagerProfessions::getAll());
		$profession = $professionId !== -1 ? VillagerProfessionIdMap::getInstance()->fromId($professionId) : $allProfessions[array_rand($allProfessions)];
		if($profession === null){
			throw new SavedDataLoadingException("Invalid profession ID $professionId");
		}
		$this->setProfession($profession);

		$offers = $nbt->getCompoundTag(self::TAG_OFFERS);
		$recipes = [];
		if($offers !== null){
			$recipesTag = $offers->getListTag(self::TAG_RECIPES);
			if($recipesTag !== null){
				foreach($recipesTag->getValue() as $recipeTag){
					if($recipeTag instanceof CompoundTag){
						$recipes[] = TradeRecipe::nbtDeserialize($recipeTag);
					}
				}
			}
		}else{
			$pos = $this->getPosition();
			$recipes = $profession->getRecipes($this->getWorld()->getBiomeId($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()));
		}
		$tradeTier = 0;
		if(($tierTag = $nbt->getTag(self::TAG_TRADE_TIER)) instanceof IntTag){
			$tradeTier = $tierTag->getValue();
		}
		$tierExpRequirements = TradeRecipeData::DEFAULT_TIER_EXP_REQUIREMENTS;
		if(($tierExpRequirementsTag = $nbt->getListTag(self::TAG_TIER_EXP_REQUIREMENTS)) !== null){
			foreach($tierExpRequirementsTag->getValue() as $tierExpRequirementTag){
				if($tierExpRequirementTag instanceof CompoundTag){
					foreach($tierExpRequirementsTag->getValue() as $key => $value){
						if($value instanceof IntTag){
							$tierExpRequirements[(int) $key] = $value->getValue();
						}
					}
				}
			}
		}
		$this->recipeData = new TradeRecipeData($recipes, $tradeTier, $nbt->getInt(self::TAG_TRADE_EXPERIENCE, 0), $tierExpRequirements);
	}

	public function setProfession(VillagerProfession $profession) : void{
		$this->profession = $profession;
		$pos = $this->getPosition();
		$this->recipeData = new TradeRecipeData($profession->getRecipes($this->getWorld()->getBiomeId($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())));
		$this->networkPropertiesDirty = true;
	}

	public function getProfession() : VillagerProfession{
		return $this->profession;
	}

	public function getRecipeData() : TradeRecipeData{
		return $this->recipeData;
	}

	public function setRecipeData(TradeRecipeData $recipeData) : void{
		$this->recipeData = $recipeData;
	}

	public function isBaby() : bool{
		return $this->baby;
	}

	public function setBaby(bool $baby) : void{
		$this->baby = $baby;
		$this->networkPropertiesDirty = true;
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool{
		if($this->isBaby() || !$this->profession->canTrade()){
			return true;
		}
		return $player->setCurrentWindow(new TradeInventory($this->profession->getVillagerName(), $this, $this->recipeData));
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setInt(self::TAG_PROFESSION, $this->profession->getId());
		$nbt->setInt(self::TAG_VARIANT, $this->profession->getId());
		$nbt->setInt(self::TAG_TRADE_EXPERIENCE, $this->recipeData->getTradeExperience());
		$nbt->setInt(self::TAG_TRADE_TIER, $this->recipeData->getTier());

		$offers = CompoundTag::create();

		$recipes = $this->recipeData->getRecipes();
		$recipesTag = array_map(static function(TradeRecipe $recipe) : Tag{
			return $recipe->nbtSerialize();
		}, $recipes);
		$offers->setTag(self::TAG_RECIPES, new ListTag($recipesTag));

		$tierExpRequirements = [];
		foreach($this->recipeData->getTierExpRequirements() as $tier => $expRequirement){
			$tierExpRequirements[] = CompoundTag::create()
				->setInt((string) $tier, $expRequirement);
		}
		$offers->setTag(self::TAG_TIER_EXP_REQUIREMENTS, new ListTag($tierExpRequirements));

		$nbt->setTag(self::TAG_OFFERS, $offers);
		return $nbt;
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);
		$properties->setGenericFlag(EntityMetadataFlags::BABY, $this->baby);
		$properties->setInt(EntityMetadataProperties::VARIANT, $this->profession->getId());
	}
}
