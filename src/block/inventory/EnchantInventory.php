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

namespace pocketmine\block\inventory;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\item\enchantment\EnchantmentHelper as Helper;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\PlayerEnchantOptionsPacket;
use pocketmine\network\mcpe\protocol\types\Enchant;
use pocketmine\network\mcpe\protocol\types\EnchantOption;
use pocketmine\player\Player;
use pocketmine\world\Position;
use function array_map;
use function array_merge;
use function count;
use function is_null;

class EnchantInventory extends SimpleInventory implements BlockInventory, TemporaryInventory{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;
	public const SLOT_LAPIS = 1;

	private Player $player;

	/** @var EnchantOption[] $options */
	private array $options = [];

	/** @var Item[] $outputs */
	private array $outputs = [];

	public function __construct(Position $holder, Player $player){
		$this->holder = $holder;
		$this->player = $player;
		parent::__construct(2);
	}

	protected function onSlotChange(int $index, Item $before) : void{
		if($index == self::SLOT_INPUT){
			$this->options = Helper::getEnchantOptions($this->holder, $this->getItem(self::SLOT_INPUT), $this->player->getXpSeed());
			$this->outputs = [];

			if(count($this->options) !== 0){
				$this->player->getNetworkSession()->sendDataPacket(PlayerEnchantOptionsPacket::create($this->options));
			}
		}

		parent::onSlotChange($index, $before);
	}

	public function getInput() : Item{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getLapis() : Item{
		return $this->getItem(self::SLOT_LAPIS);
	}

	public function getOutput(int $optionId) : Item{
		$this->prepareOutput($optionId);
		return $this->outputs[$optionId];
	}

	private function prepareOutput(int $optionId) : void{
		if(isset($this->outputs[$optionId])){
			return;
		}

		$option = $this->options[$optionId] ?? null;
		if(is_null($option)){
			throw new \RuntimeException("Failed to find enchantment option with network id $optionId");
		}

		$enchantments = array_map(
			function(Enchant $e){
				$enchantment = EnchantmentIdMap::getInstance()->fromId($e->getId());
				if(is_null($enchantment)){
					throw new \RuntimeException("Failed to get enchantment with id {$e->getId()}");
				}
				return new EnchantmentInstance($enchantment, $e->getLevel());
			},
			array_merge(
				$option->getEquipActivatedEnchantments(),
				$option->getHeldActivatedEnchantments(),
				$option->getSelfActivatedEnchantments()
			)
		);

		$outputItem = $this->getItem(self::SLOT_INPUT);
		if($outputItem->getTypeId() === ItemTypeIds::BOOK){
			$outputItem = VanillaItems::ENCHANTED_BOOK();
		}
		foreach($enchantments as $enchantment){
			$outputItem->addEnchantment($enchantment);
		}

		$this->outputs[$optionId] = $outputItem;
	}
}
