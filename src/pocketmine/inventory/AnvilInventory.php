<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\block\BlockIds;
use pocketmine\item\Compass;
use pocketmine\item\Durable;
use pocketmine\item\EnchantedBook;
use pocketmine\item\Map;
use pocketmine\item\TieredTool;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;

class AnvilInventory extends ContainerInventory implements FakeInventory{

	/** @var Position */
	protected $holder;

	public function __construct(Position $pos){
		parent::__construct($pos->asPosition());
	}

	public function getNetworkType() : int{
		return WindowTypes::ANVIL;
	}

	public function getName() : string{
		return "Anvil";
	}

	public function getDefaultSize() : int{
		return 3; //1 input, 1 material, 1 result
	}

	public function isResultOutput() : bool{
		return !$this->getItem(2)->isNull();
	}

	/**
	 * @param Player $player
	 * @param Item $result
	 *
	 * @return bool
	 */
	public function onResult(Player $player, Item $result) : bool{
		$input = $this->getItem(0);
		$material = $this->getItem(1);
		$resultE = clone $input;
		$repairCost = $input->getRepairCost();

		if(($player->isSurvival() and $repairCost >= 63) or ($player->isCreative() and $repairCost >= 2147483647)){
			return false;
		}

		// TODO: check xp level cost

		if($material instanceof EnchantedBook){ // enchanting
			foreach($material->getEnchantments() as $enchantment){
				if($resultE->hasEnchantment($enchantment->getId())){
					if($enchantment->getLevel() > $resultE->getEnchantmentLevel($enchantment->getId())){
						$resultE->addEnchantment($enchantment);
					}
				}else{
					$resultE->addEnchantment($enchantment);
				}
			}
		}elseif($input instanceof Durable and $material instanceof Durable and $input->equals($material, false, false)){ // item repair
			if($input->getDamage() > 0){
				/** @var Durable $resultE */
				$f = $material->getDamage() + intval(($input->getMaxDurability() * 12) / 100);
				$resultE->setDamage(max(0, $resultE->getDamage() - $f));
			}
			foreach($material->getEnchantments() as $enchantment){
				if($resultE->hasEnchantment($enchantment->getId())){
					if($enchantment->getLevel() > $resultE->getEnchantmentLevel($enchantment->getId())){
						$resultE->addEnchantment($enchantment);
					}
				}else{
					$resultE->addEnchantment($enchantment);
				}
			}
			$repairCost += $material->getRepairCost();
		}elseif($input instanceof TieredTool){ // repairing tiered tool
			static $tierIds = [
				TieredTool::TIER_WOODEN => BlockIds::WOODEN_PLANKS,
				TieredTool::TIER_STONE => BlockIds::COBBLESTONE,
				TieredTool::TIER_IRON => ItemIds::IRON_INGOT,
				TieredTool::TIER_GOLD => ItemIds::GOLD_INGOT,
				TieredTool::TIER_DIAMOND => ItemIds::DIAMOND
			];

			if(isset($tierIds[$input->getTier()])){
				$targetMaterial = ItemFactory::get($tierIds[$input->getTier()]);
				if($material->equals($targetMaterial)){
					/** @var TieredTool $resultE */
					if($input->getDamage() < $input->getMaxDurability()){
						$f = intval(($input->getMaxDurability() * 25) / 100);
						$resultE->setDamage(max(0, $resultE->getDamage() - $f));
					}
				}
			}
		}elseif($input instanceof Map){
			/** @var Map $resultE */
			if($material instanceof Compass){
				$resultE->setMapDisplayPlayers(true);
			}elseif($material->getId() === ItemIds::PAPER){
				if(($mapData = $resultE->getMapData()) !== null){
					if($mapData->getScale() < 4){
						$mapData->setScale($mapData->getScale() + 1);
					}else{
						return false;
					}
				}
			}
		}

		if($result->hasCustomName()){
			if($input->getCustomName() !== $result->getCustomName()){ // renaming
				$resultE->setCustomName($result->getCustomName());
			}
		}
		$repairCost = $repairCost * 2 + 1;

		$resultE->setRepairCost($repairCost);

		if($result->equalsExact($resultE)){
			$this->clear(0);

			if(!$this->getItem(1)->isNull()){
				$material = $this->getItem(1);
				$material->pop();

				$this->setItem(1, $material);
			}

			return true;
		}
		return false;
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Position
	 */
	public function getHolder(){
		return $this->holder;
	}
}
