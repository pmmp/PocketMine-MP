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

use pocketmine\crafting\CraftingGrid;
use pocketmine\inventory\transaction\action\DestroyItemAction;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionBuilder;
use pocketmine\inventory\transaction\TransactionBuilderInventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerUIIds;
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
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use function get_class;

final class ItemStackRequestExecutor{
	private TransactionBuilder $builder;

	/** @var ItemStackRequestSlotInfo[] */
	private array $requestSlotInfos = [];

	private ?InventoryTransaction $specialTransaction = null;

	/** @var Item[] */
	private array $craftingResults = [];
	private int $craftingOutputIndex = 0;

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
		$removed = $this->removeItemFromSlot($source, $count);
		$this->addItemToSlot($destination, $removed, $count);
	}

	/**
	 * Deducts items from an inventory slot, returning a stack containing the removed items.
	 */
	private function removeItemFromSlot(ItemStackRequestSlotInfo $slotInfo, int $count) : Item{
		$this->requestSlotInfos[] = $slotInfo;
		[$inventory, $slot] = $this->getBuilderInventoryAndSlot($slotInfo);

		$existingItem = $inventory->getItem($slot);
		if($existingItem->getCount() < $count){
			throw new PacketHandlingException("Cannot take $count items from a stack of " . $existingItem->getCount());
		}

		$removed = $existingItem->pop($count);
		$inventory->setItem($slot, $existingItem);

		return $removed;
	}

	/**
	 * Adds items to the target slot, if they are stackable.
	 */
	private function addItemToSlot(ItemStackRequestSlotInfo $slotInfo, Item $item, int $count) : Item{
		$this->requestSlotInfos[] = $slotInfo;
		[$inventory, $slot] = $this->getBuilderInventoryAndSlot($slotInfo);

		$existingItem = $inventory->getItem($slot);
		if(!$existingItem->isNull() && !$existingItem->canStackWith($item)){
			throw new PacketHandlingException("Can only add items to an empty slot, or a slot containing the same item");
		}

		//we can't use the existing item here; it may be an empty stack
		$newItem = clone $item;
		$newItem->setCount($existingItem->getCount() + $count);
		$inventory->setItem($slot, $newItem);

		$leftover = clone $item;
		$leftover->pop($count);

		return $leftover;
	}

	private function beginCrafting(int $recipeId, int $repetitions) : void{
		if($this->specialTransaction !== null){
			throw new PacketHandlingException("Cannot perform more than 1 special action per request");
		}
		if($repetitions < 1){ //TODO: upper bound?
			throw new PacketHandlingException("Cannot craft a recipe less than 1 time");
		}
		$craftingManager = $this->player->getServer()->getCraftingManager();
		$recipe = $craftingManager->getCraftingRecipeIndex()[$recipeId] ?? null;
		if($recipe === null){
			throw new PacketHandlingException("Unknown crafting recipe ID $recipeId");
		}

		$this->specialTransaction = new CraftingTransaction($this->player, $craftingManager, [], $recipe, $repetitions);

		$currentWindow = $this->player->getCurrentWindow();
		if($currentWindow !== null && !($currentWindow instanceof CraftingGrid)){
			throw new PacketHandlingException("Cannot complete crafting when the player's current window is not a crafting grid");
		}
		$craftingGrid = $currentWindow ?? $this->player->getCraftingGrid();

		$craftingResults = $recipe->getResultsFor($craftingGrid);
		foreach($craftingResults as $k => $craftingResult){
			$craftingResult->setCount($craftingResult->getCount() * $repetitions);
			$this->craftingResults[$k] = $craftingResult;
		}
	}

	private function takeCraftingResult(ItemStackRequestSlotInfo $destination, int $count) : void{
		$recipeResultItem = $this->craftingResults[$this->craftingOutputIndex] ?? null;
		if($recipeResultItem === null){
			throw new PacketHandlingException("Cannot refer to nonexisting crafting output index " . $this->craftingOutputIndex);
		}

		$availableCount = $recipeResultItem->getCount();
		if($availableCount < $count){
			throw new PacketHandlingException("Tried to take too many results from crafting");
		}

		$this->craftingResults[$this->craftingOutputIndex] = $this->addItemToSlot($destination, $recipeResultItem, $count);
	}

	private function processItemStackRequestAction(ItemStackRequestAction $action) : void{
		if(
			$action instanceof TakeStackRequestAction ||
			$action instanceof PlaceStackRequestAction
		){
			$source = $action->getSource();
			$destination = $action->getDestination();

			if($source->getContainerId() === ContainerUIIds::CREATED_OUTPUT && $source->getSlotId() === UIInventorySlotOffset::CREATED_ITEM_OUTPUT){
				$this->takeCraftingResult($destination, $action->getCount());
			}else{
				$this->transferItems($source, $destination, $action->getCount());
			}
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
			//TODO: this action has a "randomly" field, I have no idea what it's used for
			$dropped = $this->removeItemFromSlot($action->getSource(), $action->getCount());
			$this->builder->addAction(new DropItemAction($dropped));

		}elseif($action instanceof DestroyStackRequestAction){
			$destroyed = $this->removeItemFromSlot($action->getSource(), $action->getCount());
			$this->builder->addAction(new DestroyItemAction($destroyed));

		}elseif($action instanceof CraftRecipeStackRequestAction){
			$this->beginCrafting($action->getRecipeId(), 1);
		}elseif($action instanceof CraftRecipeAutoStackRequestAction){
			$this->beginCrafting($action->getRecipeId(), $action->getRepetitions());
		}elseif($action instanceof CraftingConsumeInputStackRequestAction){
			if(!$this->specialTransaction instanceof CraftingTransaction){
				throw new PacketHandlingException("Cannot consume crafting input when no crafting transaction is in progress");
			}
			$this->removeItemFromSlot($action->getSource(), $action->getCount()); //output discarded - we allow CraftingTransaction to verify the balance

		}elseif($action instanceof CraftingMarkSecondaryResultStackRequestAction){
			if(!$this->specialTransaction instanceof CraftingTransaction){
				throw new AssumptionFailedError("Cannot mark crafting result index when no crafting transaction is in progress");
			}
			$outputIndex = $action->getCraftingGridSlot();
			if($outputIndex < 0){
				throw new PacketHandlingException("Crafting result index cannot be negative");
			}
			$this->craftingOutputIndex = $outputIndex;
		}elseif($action instanceof DeprecatedCraftingResultsStackRequestAction){
			//no obvious use
		}else{
			throw new PacketHandlingException("Unhandled item stack request action: " . get_class($action));
		}
	}

	public function generateInventoryTransaction() : InventoryTransaction{
		foreach($this->request->getActions() as $action){
			$this->processItemStackRequestAction($action);
		}
		$inventoryActions = $this->builder->generateActions();

		$transaction = $this->specialTransaction ?? new InventoryTransaction($this->player);
		foreach($inventoryActions as $action){
			$transaction->addAction($action);
		}

		return $transaction;
	}

	public function buildItemStackResponse(bool $success) : ItemStackResponse{
		$builder = new ItemStackResponseBuilder($this->request->getRequestId(), $this->inventoryManager);
		foreach($this->requestSlotInfos as $requestInfo){
			$builder->addSlot($requestInfo->getContainerId(), $requestInfo->getSlotId());
		}

		return $builder->build($success);
	}
}
