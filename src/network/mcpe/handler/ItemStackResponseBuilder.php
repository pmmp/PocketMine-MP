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

namespace pocketmine\network\mcpe\handler;

use pocketmine\inventory\Inventory;
use pocketmine\item\Durable;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerUIIds;
use pocketmine\network\mcpe\protocol\types\inventory\FullContainerName;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponse;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponseContainerInfo;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponseSlotInfo;
use pocketmine\player\InventoryWindow;
use pocketmine\utils\AssumptionFailedError;

final class ItemStackResponseBuilder{

	/**
	 * @var int[][]
	 * @phpstan-var array<int, array<int, int>>
	 */
	private array $changedSlots = [];

	public function __construct(
		private int $requestId,
		private InventoryManager $inventoryManager
	){}

	public function addSlot(int $containerInterfaceId, int $slotId) : void{
		$this->changedSlots[$containerInterfaceId][$slotId] = $slotId;
	}

	/**
	 * @phpstan-return array{InventoryWindow, int}
	 */
	private function locateWindowAndSlot(int $containerInterfaceId, int $slotId) : ?array{
		[$windowId, $slotId] = ItemStackContainerIdTranslator::translate($containerInterfaceId, $this->inventoryManager->getCurrentWindowId(), $slotId);
		return $this->inventoryManager->locateWindowAndSlot($windowId, $slotId);
	}

	public function build() : ItemStackResponse{
		$responseInfosByContainer = [];
		foreach($this->changedSlots as $containerInterfaceId => $slotIds){
			if($containerInterfaceId === ContainerUIIds::CREATED_OUTPUT){
				continue;
			}
			foreach($slotIds as $slotId){
				$windowAndSlot = $this->locateWindowAndSlot($containerInterfaceId, $slotId);
				if($windowAndSlot === null){
					//a plugin may have closed the inventory during an event, or the slot may have been invalid
					continue;
				}
				[$window, $slot] = $windowAndSlot;

				$itemStackInfo = $this->inventoryManager->getItemStackInfo($window, $slot);
				if($itemStackInfo === null){
					throw new AssumptionFailedError("ItemStackInfo should never be null for an open inventory");
				}
				$item = $window->getInventory()->getItem($slot);

				$responseInfosByContainer[$containerInterfaceId][] = new ItemStackResponseSlotInfo(
					$slotId,
					$slotId,
					$item->getCount(),
					$itemStackInfo->getStackId(),
					$item->getCustomName(),
					$item->getCustomName(),
					$item instanceof Durable ? $item->getDamage() : 0,
				);
			}
		}

		$responseContainerInfos = [];
		foreach($responseInfosByContainer as $containerInterfaceId => $responseInfos){
			$responseContainerInfos[] = new ItemStackResponseContainerInfo(new FullContainerName($containerInterfaceId), $responseInfos);
		}

		return new ItemStackResponse(ItemStackResponse::RESULT_OK, $this->requestId, $responseContainerInfos);
	}
}
