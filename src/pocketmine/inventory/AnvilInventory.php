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

use pocketmine\block\Air;
use pocketmine\block\Anvil;
use pocketmine\block\BlockIds;
use pocketmine\item\Durable;
use pocketmine\item\EnchantedBook;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\TieredTool;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;

class AnvilInventory extends ContainerInventory implements FakeInventory, FakeResultInventory{

	public const SLOT_INPUT = 0;
	public const SLOT_SACRIFICE = 1;
	public const SLOT_OUTPUT = 2;

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

	public function getUIOffsets() : array{
		return UIInventorySlotOffset::ANVIL;
	}

	public function getDefaultSize() : int{
		return 3; //1 input, 1 sacrifice, 1 output
	}

	public function getResultSlot() : int{
		return self::SLOT_OUTPUT;
	}

	/**
	 * @param Player $player
	 * @param Item   $result
	 *
	 * @return bool
	 */
	public function onResult(Player $player, Item $result) : bool{
		$input = $this->getItem(self::SLOT_INPUT);
		$sacrifice = $this->getItem(self::SLOT_SACRIFICE);
		$output = clone $input;

		$totalRepairCost = $input->getRepairCost() + $sacrifice->getRepairCost();
		$levelCostBonus = 0;
		$materialCost = 1;
		$renamed = false;

		static $tierIds = [
			TieredTool::TIER_WOODEN => BlockIds::WOODEN_PLANKS,
			TieredTool::TIER_STONE => BlockIds::COBBLESTONE,
			TieredTool::TIER_IRON => ItemIds::IRON_INGOT,
			TieredTool::TIER_GOLD => ItemIds::GOLD_INGOT,
			TieredTool::TIER_DIAMOND => ItemIds::DIAMOND
		];

		if(!$output->isNull()){
			if($result->hasCustomName()){
				if($output->getCustomName() !== $result->getCustomName()){ // renaming
					$renamed = true;
					$levelCostBonus++;
				}
			}

			if(!$sacrifice->isNull()){
				$enchantedBook = $sacrifice instanceof EnchantedBook and count($sacrifice->getEnchantments()) > 0;

				if($output instanceof TieredTool and isset($tierIds[$output->getTier()])){
					$targetMaterial = ItemFactory::get($tierIds[$output->getTier()]);
					if($sacrifice->equals($targetMaterial)){
						$d = min($input->getDamage(), (int) $output->getMaxDurability() / 4);

						for($m2 = 0; $d > 0 and $m2 < $sacrifice->getCount(); $m2++){
							$output->setDamage($output->getDamage() - $d);
							$levelCostBonus++;
							$d = min($output->getDamage(), (int) $output->getMaxDurability() / 4);
						}

						$materialCost = $m2;
					}else{
						goto sacrifice_is_tool;
					}
				}else{
					sacrifice_is_tool:

					if(!$enchantedBook and (!$output->equals($sacrifice, false, false) or !($output instanceof Durable))){
						$this->clear(self::SLOT_OUTPUT);

						return false;
					}

					if($output instanceof Durable and !$enchantedBook and $sacrifice instanceof Durable){
						$f = ($output->getMaxDurability() - $output->getDamage()) + ($sacrifice->getMaxDurability() - $sacrifice->getDamage()) + intval(($output->getMaxDurability() * 12) / 100);
						$f2 = max(0, $output->getMaxDurability() - $f);

						if($f2 < $output->getDamage()){
							$output->setDamage($f2);
							$levelCostBonus += 2;
						}
					}

					foreach($sacrifice->getEnchantments() as $enchantmentInstance){
						$enchantment = $enchantmentInstance->getType();

						$l1 = $enchantmentInstance->getLevel();
						$cel = $output->getEnchantmentLevel($enchantmentInstance->getId());

						if($l1 === $cel){
							$cel++;
						}else{
							$cel = max($cel, $l1);
						}

						$canApply = ($enchantment->canApply($output) or $player->isCreative() or $output instanceof EnchantedBook);

						foreach($output->getEnchantments() as $enchantmentInstance2){
							if($enchantment->getId() !== $enchantmentInstance2->getId() and !$enchantment->canApplyTogether($enchantmentInstance2->getType())){
								$canApply = false;
								$levelCostBonus++;
							}
						}

						if($canApply){
							$cel = min($cel, $enchantment->getMaxLevel());

							$output->addEnchantment(new EnchantmentInstance($enchantment, $cel));
							$rarityBonus = 0;

							switch($enchantment->getRarity()){
								case Enchantment::RARITY_MYTHIC:
									$rarityBonus = 8;
									break;
								case Enchantment::RARITY_RARE:
									$rarityBonus = 4;
									break;
								case Enchantment::RARITY_UNCOMMON:
									$rarityBonus = 2;
									break;
								case Enchantment::RARITY_COMMON:
									$rarityBonus = 1;
									break;
							}

							if($enchantedBook){
								$rarityBonus = max(1, intval($rarityBonus / 2));
							}

							$levelCostBonus += $rarityBonus * $cel;
						}
					}
				}
			}

			$onlyRenamed = $renamed and $levelCostBonus === 1;
			$levelCost = $totalRepairCost + $levelCostBonus;

			if($onlyRenamed and $levelCost > 39){
				$levelCost = 39;
			}

			if($levelCost > 39 and !$player->isCreative()){
				$this->clear(self::SLOT_OUTPUT);

				return false;
			}

			if((!$onlyRenamed and ($player->isSurvival() and $input->getRepairCost() >= 63)) or $input->getRepairCost() >= 2147483647){
				$this->clear(self::SLOT_OUTPUT);

				return false;
			}

			if(!$onlyRenamed){
				$repairCost = $output->getRepairCost();

				if(!$sacrifice->isNull() and $repairCost < $sacrifice->getRepairCost()){
					$repairCost = $sacrifice->getRepairCost();
				}

				$output->setRepairCost($repairCost * 2 + 1);
			}else{
				$output->setRepairCost($output->getRepairCost());
			}

			$this->checkEnchantments($result, $output);

			if($renamed){
				$output->setCustomName($result->getCustomName());
			}

			if($output->equalsExact($result)){
				if(!$sacrifice->isNull()){
					$sacrifice->setCount(max(0, $sacrifice->getCount() - $materialCost));

					$this->setItem(self::SLOT_SACRIFICE, $sacrifice);
				}

				if(!$player->isCreative()){
					$player->addXpLevels(max(-$player->getXpLevel(), -$levelCost));
				}

				$block = $player->level->getBlock($this->getHolder());
				if(!$player->isCreative() and $block instanceof Anvil and $player->random->nextFloat() < 0.12){
					$direction = $block->getDamage() & 3;
					$type = $block->getDamage() - $direction;

					if($type === Anvil::TYPE_NORMAL){
						$type = Anvil::TYPE_SLIGHTLY_DAMAGED;
					}elseif($type === Anvil::TYPE_SLIGHTLY_DAMAGED){
						$type = Anvil::TYPE_VERY_DAMAGED;
					}else{
						$type = -1;
					}

					if($type !== -1){
						$player->level->setBlock($this->getHolder(), new Anvil($direction | $type));
					}else{
						$player->level->setBlock($this->getHolder(), new Air());

						$player->level->broadcastLevelEvent($this->getHolder(), LevelEventPacket::EVENT_SOUND_ANVIL_BREAK);
						return true;
					}
				}

				$player->level->broadcastLevelEvent($this->getHolder(), LevelEventPacket::EVENT_SOUND_ANVIL_USE);
				return true;
			}
		}

		return false;
	}

	private function checkEnchantments(Item $result, Item $output) : void{
		$map1 = [];
		$map2 = [];

		foreach($result->getEnchantments() as $e){
			$map1[$e->getId()] = $e->getLevel();
		}

		foreach($output->getEnchantments() as $e){
			$map2[$e->getId()] = $e->getLevel();
		}

		$same = true;
		foreach($map1 as $id => $level){
			if(isset($map2[$id])){
				if($map2[$id] !== $level){
					$same = false;
					break;
				}
			}else{
				break;
			}
		}

		if($same and !empty($map1) and !empty($map2)){
			$output->setNamedTagEntry($result->getNamedTagEntry(Item::TAG_ENCH) ?? new ListTag(Item::TAG_ENCH, []));
		}
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Position
	 */
	public function getHolder(){
		return $this->holder;
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);

		foreach($this->getContents() as $item){
			$who->dropItem($item);
		}
		$this->clearAll();
	}
}
