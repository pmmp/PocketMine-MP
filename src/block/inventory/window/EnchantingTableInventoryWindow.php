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

namespace pocketmine\block\inventory\window;

use pocketmine\event\player\PlayerEnchantingOptionsRequestEvent;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\enchantment\EnchantingHelper as Helper;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\Position;
use function array_values;
use function count;

final class EnchantingTableInventoryWindow extends BlockInventoryWindow{
	public const SLOT_INPUT = 0;
	public const SLOT_LAPIS = 1;

	/** @var EnchantingOption[] $options */
	private array $options = [];

	private InventoryListener $listener;

	public function __construct(
		Player $viewer,
		Position $holder
	){
		parent::__construct($viewer, new SimpleInventory(2), $holder);

		/** @phpstan-var \WeakReference<$this> $weakThis */
		$weakThis = \WeakReference::create($this);
		$this->listener = new CallbackInventoryListener(
			onSlotChange: static function(Inventory $_, int $slot) use ($weakThis) : void{ //remaining params unneeded
				if($slot === self::SLOT_INPUT && ($strongThis = $weakThis->get()) !== null){
					$strongThis->regenerateOptions();
				}
			},
			onContentChange: static function() use ($weakThis) : void{
				if(($strongThis = $weakThis->get()) !== null){
					$strongThis->regenerateOptions();
				}
			}
		);
		$this->inventory->getListeners()->add($this->listener);
	}

	public function __destruct(){
		$this->inventory->getListeners()->remove($this->listener);
	}

	private function regenerateOptions() : void{
		$this->options = [];
		$item = $this->getInput();
		$options = Helper::generateOptions($this->holder, $item, $this->viewer->getEnchantmentSeed());

		$event = new PlayerEnchantingOptionsRequestEvent($this->viewer, $this, $options);
		$event->call();
		if(!$event->isCancelled() && count($event->getOptions()) > 0){
			$this->options = array_values($event->getOptions());
			$this->viewer->getNetworkSession()->getInvManager()?->syncEnchantingTableOptions($this->options);
		}
	}

	public function getInput() : Item{
		return $this->inventory->getItem(self::SLOT_INPUT);
	}

	public function getLapis() : Item{
		return $this->inventory->getItem(self::SLOT_LAPIS);
	}

	public function getOutput(int $optionId) : ?Item{
		$option = $this->getOption($optionId);
		return $option === null ? null : Helper::enchantItem($this->getInput(), $option->getEnchantments());
	}

	public function getOption(int $optionId) : ?EnchantingOption{
		return $this->options[$optionId] ?? null;
	}
}
