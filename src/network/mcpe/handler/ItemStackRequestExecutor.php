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

use pocketmine\inventory\transaction\action\DestroyItemAction;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionBuilder;
use pocketmine\inventory\transaction\TransactionBuilderInventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftingConsumeInputStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftingMarkSecondaryResultStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftRecipeAutoStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftRecipeStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\DeprecatedCraftingResultsStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\DestroyStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\DropStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequest;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequestSlotInfo;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\PlaceStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\SwapStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\TakeStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponse;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use function get_class;

final class ItemStackRequestExecutor{
	private TransactionBuilder $builder;

	/** @var ItemStackRequestSlotInfo[] */
	private array $requestSlotInfos = [];

	private bool $crafting = false;

	public function __construct(
		private Player $player,
		private InventoryManager $inventoryManager,
		private ItemStackRequest $request
	){
		$this->builder = new TransactionBuilder();
	}

	/**
	 * @phpstan-return array{TransactionBuilderInventory, int}
	 */
	private function getBuilderInventoryAndSlot(ItemStackRequestSlotInfo $info) : array{
		$windowId = ItemStackContainerIdTranslator::translate($info->getContainerId(), $this->inventoryManager->getCurrentWindowId());
		$windowAndSlot = $this->inventoryManager->locateWindowAndSlot($windowId, $info->getSlotId());
		if($windowAndSlot === null){
			throw new PacketHandlingException("Stack request action cannot target an inventory that is not open");
		}
		[$inventory, $slot] = $windowAndSlot;
		if(!$inventory->slotExists($slot)){
			throw new PacketHandlingException("Stack request action cannot target an inventory slot that does not exist");
		}

		if(
			$info->getStackId() !== $this->request->getRequestId() && //using TransactionBuilderInventory enables this to work
			!$this->inventoryManager->matchItemStack($inventory, $slot, $info->getStackId())
		){
			throw new PacketHandlingException("Inventory " . $info->getContainerId() . ", slot " . $slot . ": server-side item does not match expected");
		}

		return [$this->builder->getInventory($inventory), $slot];
	}

	private function transferItems(ItemStackRequestSlotInfo $source, ItemStackRequestSlotInfo $destination, int $count) : void{
		[$sourceInventory, $sourceSlot] = $this->getBuilderInventoryAndSlot($source);
		[$targetInventory, $targetSlot] = $this->getBuilderInventoryAndSlot($destination);

		$oldSourceItem = $sourceInventory->getItem($sourceSlot);
		$oldTargetItem = $targetInventory->getItem($targetSlot);

		if(!$targetInventory->isSlotEmpty($targetSlot) && !$oldTargetItem->canStackWith($oldSourceItem)){
			throw new PacketHandlingException("Can only transfer items into an empty slot, or a slot containing the same item");
		}
		[$newSourceItem, $newTargetItem] = $this->splitStack($oldSourceItem, $count, $oldTargetItem->getCount());

		$sourceInventory->setItem($sourceSlot, $newSourceItem);
		$targetInventory->setItem($targetSlot, $newTargetItem);
	}

	/**
	 * @phpstan-return array{Item, Item}
	 */
	private function splitStack(Item $item, int $transferredCount, int $targetCount) : array{
		if($item->getCount() < $transferredCount){
			throw new PacketHandlingException("Cannot take $transferredCount items from a stack of " . $item->getCount());
		}

		$leftover = clone $item;
		$removed = $leftover->pop($transferredCount);
		$removed->setCount($removed->getCount() + $targetCount);
		if($leftover->isNull()){
			$leftover = VanillaItems::AIR();
		}

		return [$leftover, $removed];
	}

	private function processItemStackRequestAction(ItemStackRequestAction $action) : void{
		if(
			$action instanceof TakeStackRequestAction ||
			$action instanceof PlaceStackRequestAction
		){
			$this->requestSlotInfos[] = $action->getSource();
			$this->requestSlotInfos[] = $action->getDestination();
			$this->transferItems($action->getSource(), $action->getDestination(), $action->getCount());
		}elseif($action instanceof SwapStackRequestAction){
			$this->requestSlotInfos[] = $action->getSlot1();
			$this->requestSlotInfos[] = $action->getSlot2();

			[$inventory1, $slot1] = $this->getBuilderInventoryAndSlot($action->getSlot1());
			[$inventory2, $slot2] = $this->getBuilderInventoryAndSlot($action->getSlot2());

			$item1 = $inventory1->getItem($slot1);
			$item2 = $inventory2->getItem($slot2);
			$inventory1->setItem($slot1, $item2);
			$inventory2->setItem($slot2, $item1);
		}elseif($action instanceof DropStackRequestAction){
			$this->requestSlotInfos[] = $action->getSource();
			[$inventory, $slot] = $this->getBuilderInventoryAndSlot($action->getSource());

			$oldItem = $inventory->getItem($slot);
			[$leftover, $dropped] = $this->splitStack($oldItem, $action->getCount(), 0);

			//TODO: this action has a "randomly" field, I have no idea what it's used for
			$inventory->setItem($slot, $leftover);
			$this->builder->addAction(new DropItemAction($dropped));
		}elseif($action instanceof DestroyStackRequestAction){
			$this->requestSlotInfos[] = $action->getSource();
			[$inventory, $slot] = $this->getBuilderInventoryAndSlot($action->getSource());

			$oldItem = $inventory->getItem($slot);
			[$leftover, $destroyed] = $this->splitStack($oldItem, $action->getCount(), 0);

			$inventory->setItem($slot, $leftover);
			$this->builder->addAction(new DestroyItemAction($destroyed));
		}elseif($action instanceof CraftingConsumeInputStackRequestAction){
			//we don't need this for the PM system
			$this->requestSlotInfos[] = $action->getSource();
			$this->crafting = true;
		}elseif(
			$action instanceof CraftRecipeStackRequestAction || //TODO
			$action instanceof CraftRecipeAutoStackRequestAction || //TODO
			$action instanceof CraftingMarkSecondaryResultStackRequestAction || //no obvious use
			$action instanceof DeprecatedCraftingResultsStackRequestAction //no obvious use
		){
			$this->crafting = true;
		}else{
			throw new PacketHandlingException("Unhandled item stack request action: " . get_class($action));
		}
	}

	public function generateInventoryTransaction() : InventoryTransaction{
		foreach($this->request->getActions() as $action){
			$this->processItemStackRequestAction($action);
		}
		$inventoryActions = $this->builder->generateActions();

		return $this->crafting ?
			new CraftingTransaction($this->player, $this->player->getServer()->getCraftingManager(), $inventoryActions) :
			new InventoryTransaction($this->player, $inventoryActions);
	}

	public function buildItemStackResponse(bool $success) : ItemStackResponse{
		$builder = new ItemStackResponseBuilder($this->request->getRequestId(), $this->inventoryManager);
		foreach($this->requestSlotInfos as $requestInfo){
			$builder->addSlot($requestInfo->getContainerId(), $requestInfo->getSlotId());
		}

		return $builder->build($success);
	}
}
