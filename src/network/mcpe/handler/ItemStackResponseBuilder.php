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
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponse;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponseContainerInfo;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponseSlotInfo;
use pocketmine\network\PacketHandlingException;

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
	 * @phpstan-return array{Inventory, int}
	 */
	private function getInventoryAndSlot(int $containerInterfaceId, int $slotId) : array{
		$windowId = ItemStackContainerIdTranslator::translate($containerInterfaceId, $this->inventoryManager->getCurrentWindowId());
		$windowAndSlot = $this->inventoryManager->locateWindowAndSlot($windowId, $slotId);
		if($windowAndSlot === null){
			throw new PacketHandlingException("Stack request action cannot target an inventory that is not open");
		}
		[$inventory, $slot] = $windowAndSlot;
		if(!$inventory->slotExists($slot)){
			throw new PacketHandlingException("Stack request action cannot target an inventory slot that does not exist");
		}

		return [$inventory, $slot];
	}

	public function build(bool $success) : ItemStackResponse{
		$responseInfosByContainer = [];
		foreach($this->changedSlots as $containerInterfaceId => $slotIds){
			foreach($slotIds as $slotId){
				[$inventory, $slot] = $this->getInventoryAndSlot($containerInterfaceId, $slotId);

				$item = $inventory->getItem($slot);
				$info = $this->inventoryManager->trackItemStack($inventory, $slot, $item, $this->requestId);

				$responseInfosByContainer[$containerInterfaceId][] = new ItemStackResponseSlotInfo(
					$slotId,
					$slotId,
					$info->getItemStack()->getCount(),
					$info->getStackId(),
					$item->hasCustomName() ? $item->getCustomName() : "",
					0
				);
			}
		}

		$responseContainerInfos = [];
		foreach($responseInfosByContainer as $containerInterfaceId => $responseInfos){
			$responseContainerInfos[] = new ItemStackResponseContainerInfo($containerInterfaceId, $responseInfos);
		}

		return new ItemStackResponse($success ? ItemStackResponse::RESULT_OK : ItemStackResponse::RESULT_ERROR, $this->requestId, $responseContainerInfos);
	}
}
