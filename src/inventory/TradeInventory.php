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

use pocketmine\entity\Living;
use pocketmine\entity\trade\TradeRecipe;
use pocketmine\entity\trade\TradeRecipeData;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\player\Player;
use function count;

final class TradeInventory extends EntityInventory{

	private const TAG_RECIPES = "Recipes";
	private const TAG_TIER_EXP_REQUIREMENTS = "TierExpRequirements";

	public function __construct(
		private readonly string $name,
		private readonly Living $entity,
		private readonly TradeRecipeData $recipeData
	){
		parent::__construct(2);
	}

	public function getHolder() : Living{
		return $this->entity;
	}

	public function getRecipeData() : TradeRecipeData{
		return $this->recipeData;
	}

	public function onOpen(Player $who) : void{
		parent::onOpen($who);
		$this->entity->getNetworkProperties()->setLong(EntityMetadataProperties::TRADING_PLAYER_EID, $who->getId());
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		$this->entity->getNetworkProperties()->setLong(EntityMetadataProperties::TRADING_PLAYER_EID, -1);
	}

	public function createInventoryOpenPackets(int $id) : array{
		$holder = $this->getHolder();

		$recipeData = $this->recipeData;
		$recipes = $recipeData->getRecipes();

		$tierExpRequirements = [];

		foreach($recipeData->getTierExpRequirements() as $tier => $expRequirement){
			$tierExpRequirements[] = CompoundTag::create()
				->setInt((string) $tier, $expRequirement);
		}

		$recipesTag = new ListTag();
		for($i = 0; $i < count($recipes); $i++){
			$recipeNBT = $recipes[$i]->nbtSerialize();
			//TODO: net ID behaves like index of the recipe.
			//If we don't set this client sends some random number that we can't track.
			$recipeNBT->setInt(TradeRecipe::TAG_NET_ID, $i + 1);
			$recipesTag->push($recipeNBT);
		}

		$nbt = CompoundTag::create()
			->setTag(self::TAG_RECIPES, $recipesTag)
			->setTag(self::TAG_TIER_EXP_REQUIREMENTS, new ListTag($tierExpRequirements));

		return [
			UpdateTradePacket::create(
				$id,
				WindowTypes::TRADING,
				0,
				$recipeData->getTier(),
				$holder->getId(),
				-1,
				$this->name,
				true,
				true,
				new CacheableNbt($nbt)
			)];
	}
}
