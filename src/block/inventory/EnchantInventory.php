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

use pocketmine\event\player\PlayerEnchantingOptionsRequestEvent;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\item\enchantment\EnchantingHelper as Helper;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\Position;
use function array_values;
use function count;

class EnchantInventory extends SimpleInventory implements BlockInventory, TemporaryInventory{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;
	public const SLOT_LAPIS = 1;

	/**
	 * @var EnchantingOption[] $options
	 * @phpstan-var list<EnchantingOption>
	 */
	private array $options = [];

	/**
	 * Options mapped by viewer (spl_object_id(player) => list<EnchantingOption>)
	 * @phpstan-var array<int, list<EnchantingOption>>
	 */
	private array $optionsByViewer = [];

	public function __construct(Position $holder){
		$this->holder = $holder;
		parent::__construct(2);
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		// Clean up any viewer-specific cached options to avoid unbounded growth
		unset($this->optionsByViewer[spl_object_id($who)]);
	}

	protected function onSlotChange(int $index, Item $before) : void{
		if($index === self::SLOT_INPUT){
			foreach($this->viewers as $viewer){
				// Generate options per viewer (depends on their enchantment seed)
				$item = $this->getInput();
				$options = Helper::generateOptions($this->holder, $item, $viewer->getEnchantmentSeed());

				$event = new PlayerEnchantingOptionsRequestEvent($viewer, $this, $options);
				$event->call();
				if(!$event->isCancelled() && count($event->getOptions()) > 0){
					$this->optionsByViewer[spl_object_id($viewer)] = array_values($event->getOptions());
					// Also keep a fallback default (last writer) for compatibility
					$this->options = $this->optionsByViewer[spl_object_id($viewer)];
					$viewer->getNetworkSession()->getInvManager()?->syncEnchantingTableOptions($this->optionsByViewer[spl_object_id($viewer)]);
				}
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

	public function getOutput(int $optionId, ?Player $viewer = null) : ?Item{
		$option = $this->getOption($optionId, $viewer);
		return $option === null ? null : Helper::enchantItem($this->getInput(), $option->getEnchantments());
	}

	public function getOption(int $optionId, ?Player $viewer = null) : ?EnchantingOption{
		if($viewer !== null){
			$opts = $this->optionsByViewer[spl_object_id($viewer)] ?? null;
			return $opts[$optionId] ?? null;
		}
		// Fallback: if only one viewer has options, return that
		if(count($this->optionsByViewer) === 1){
			$only = array_values($this->optionsByViewer)[0];
			return $only[$optionId] ?? null;
		}
		// Last-writer fallback for compatibility
		return $this->options[$optionId] ?? null;
	}
}
