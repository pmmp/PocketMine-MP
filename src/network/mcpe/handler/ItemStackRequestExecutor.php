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

use pocketmine\block\inventory\EnchantInventory;
use pocketmine\block\inventory\LoomInventory;
use pocketmine\inventory\Inventory;
use pocketmine\block\inventory\SmithingTableInventory;
use pocketmine\inventory\transaction\SmithingTransaction;
use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\utils\AnvilHelper;
use pocketmine\block\utils\BannerPatternLayer;
use pocketmine\block\utils\BannerPatternType;
use pocketmine\data\bedrock\BannerPatternTypeIdMap;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\inventory\transaction\AnvilTransaction;
use pocketmine\inventory\transaction\LoomTransaction;
use pocketmine\item\Banner as BannerItem;
use pocketmine\item\Dye;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftRecipeOptionalStackRequestAction;

use pocketmine\inventory\transaction\action\CreateItemAction;
use pocketmine\inventory\transaction\action\DestroyItemAction;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\inventory\transaction\EnchantingTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionBuilder;
use pocketmine\inventory\transaction\TransactionBuilderInventory;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\network\mcpe\cache\CraftingDataCache;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerUIIds;
use pocketmine\network\mcpe\protocol\types\inventory\FullContainerName;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftingConsumeInputStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftingCreateSpecificResultStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftRecipeAutoStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftRecipeStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CreativeCreateStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\DeprecatedCraftingResultsStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\DestroyStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\DropStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequest;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequestSlotInfo;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\LoomStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\MineBlockStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\PlaceStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\SwapStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\TakeStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponse;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use function array_key_first;
use function count;
use function spl_object_id;

class ItemStackRequestExecutor
{
	private const LOOM_MAX_PATTERNS = 6;

	/**
	 * @var array<string, BannerPatternType>
	 */
	private const LOOM_PATTERN_ITEM_MAP = [
		ItemTypeNames::BORDURE_INDENTED_BANNER_PATTERN => BannerPatternType::CURLY_BORDER,
		ItemTypeNames::CREEPER_BANNER_PATTERN => BannerPatternType::CREEPER,
		ItemTypeNames::FIELD_MASONED_BANNER_PATTERN => BannerPatternType::BRICKS,
		ItemTypeNames::FLOW_BANNER_PATTERN => BannerPatternType::FLOW,
		ItemTypeNames::FLOWER_BANNER_PATTERN => BannerPatternType::FLOWER,
		ItemTypeNames::GLOBE_BANNER_PATTERN => BannerPatternType::GLOBE,
		ItemTypeNames::GUSTER_BANNER_PATTERN => BannerPatternType::GUSTER,
		ItemTypeNames::MOJANG_BANNER_PATTERN => BannerPatternType::MOJANG,
		ItemTypeNames::PIGLIN_BANNER_PATTERN => BannerPatternType::PIGLIN,
		ItemTypeNames::SKULL_BANNER_PATTERN => BannerPatternType::SKULL,
	];

	private TransactionBuilder $builder;

	/** @var ItemStackRequestSlotInfo[] */
	private array $requestSlotInfos = [];

	private ?InventoryTransaction $specialTransaction = null;

	/** @var Item[] */
	private array $craftingResults = [];

	private ?Item $nextCreatedItem = null;
	private bool $createdItemFromCreativeInventory = false;
	private int $createdItemsTakenCount = 0;

	public function __construct(
		private Player $player,
		private InventoryManager $inventoryManager,
		private ItemStackRequest $request
	) {
		$this->builder = new TransactionBuilder();
	}

	protected function prettyInventoryAndSlot(Inventory $inventory, int $slot): string
	{
		if ($inventory instanceof TransactionBuilderInventory) {
			$inventory = $inventory->getActualInventory();
		}
		return (new \ReflectionClass($inventory))->getShortName() . "#" . spl_object_id($inventory) . ", slot: $slot";
	}

	/**
	 * @throws ItemStackRequestProcessException
	 */
	private function matchItemStack(Inventory $inventory, int $slotId, int $clientItemStackId): void
	{
		$info = $this->inventoryManager->getItemStackInfo($inventory, $slotId);
		if ($info === null) {
			throw new AssumptionFailedError("The inventory is tracked and the slot is valid, so this should not be null");
		}

		if (!($clientItemStackId < 0 ? $info->getRequestId() === $clientItemStackId : $info->getStackId() === $clientItemStackId)) {
			throw new ItemStackRequestProcessException(
				$this->prettyInventoryAndSlot($inventory, $slotId) . ": " .
					"Mismatched expected itemstack, " .
					"client expected: $clientItemStackId, server actual: " . $info->getStackId() . ", last modified by request: " . ($info->getRequestId() ?? "none")
			);
		}
	}

	/**
	 * @phpstan-return array{TransactionBuilderInventory, int}
	 *
	 * @throws ItemStackRequestProcessException
	 */
	protected function getBuilderInventoryAndSlot(ItemStackRequestSlotInfo $info): array
	{
		[$windowId, $slotId] = ItemStackContainerIdTranslator::translate($info->getContainerName()->getContainerId(), $this->inventoryManager->getCurrentWindowId(), $info->getSlotId());
		$windowAndSlot = $this->inventoryManager->locateWindowAndSlot($windowId, $slotId);
		if ($windowAndSlot === null) {
			throw new ItemStackRequestProcessException("No open inventory matches container UI ID: " . $info->getContainerName()->getContainerId() . ", slot ID: " . $info->getSlotId());
		}
		[$inventory, $slot] = $windowAndSlot;
		if (!$inventory->slotExists($slot)) {
			throw new ItemStackRequestProcessException("No such inventory slot :" . $this->prettyInventoryAndSlot($inventory, $slot));
		}

		if ($info->getStackId() !== $this->request->getRequestId()) { //the itemstack may have been modified by the current request
			$this->matchItemStack($inventory, $slot, $info->getStackId());
		}

		return [$this->builder->getInventory($inventory), $slot];
	}

	/**
	 * @throws ItemStackRequestProcessException
	 */
	protected function transferItems(ItemStackRequestSlotInfo $source, ItemStackRequestSlotInfo $destination, int $count): void
	{
		$removed = $this->removeItemFromSlot($source, $count);
		$this->addItemToSlot($destination, $removed, $count);
	}

	/**
	 * Deducts items from an inventory slot, returning a stack containing the removed items.
	 * @throws ItemStackRequestProcessException
	 */
	protected function removeItemFromSlot(ItemStackRequestSlotInfo $slotInfo, int $count): Item
	{
		if ($slotInfo->getContainerName()->getContainerId() === ContainerUIIds::CREATED_OUTPUT && $slotInfo->getSlotId() === UIInventorySlotOffset::CREATED_ITEM_OUTPUT) {
			//special case for the "created item" output slot
			// If a created item was prepared by the executor (craft/enchant/creative), take it.
			// Otherwise, the client may be targeting a UI's output slot (e.g. anvil result) without
			// having created a "created item" prediction; map the CREATED_OUTPUT to the current
			// UI window slot and remove from the actual inventory slot to support that behavior.
			if ($this->nextCreatedItem !== null) {
				return $this->takeCreatedItem($count);
			}

			// Try to map the CREATED_OUTPUT container to the current window and remove from the real slot
			try {
				[$windowId, $netSlot] = ItemStackContainerIdTranslator::translate($slotInfo->getContainerName()->getContainerId(), $this->inventoryManager->getCurrentWindowId(), $slotInfo->getSlotId());
			} catch (\Throwable $e) {
				return $this->takeCreatedItem($count);
			}

			$windowAndSlot = $this->inventoryManager->locateWindowAndSlot($windowId, $netSlot);
			if ($windowAndSlot !== null) {
				$this->requestSlotInfos[] = $slotInfo;
				[$inventory, $slot] = $windowAndSlot;
				if ($count < 1) {
					throw new ItemStackRequestProcessException($this->prettyInventoryAndSlot($inventory, $slot) . ": Cannot take less than 1 items from a stack");
				}

				$existingItem = $inventory->getItem($slot);
				if ($existingItem->getCount() < $count) {
					throw new ItemStackRequestProcessException($this->prettyInventoryAndSlot($inventory, $slot) . ": Cannot take $count items from a stack of " . $existingItem->getCount());
				}

				$removed = $existingItem->pop($count);
				$this->builder->getInventory($inventory)->setItem($slot, $existingItem);

				return $removed;
			}

			return $this->takeCreatedItem($count);
		}
		$this->requestSlotInfos[] = $slotInfo;
		[$inventory, $slot] = $this->getBuilderInventoryAndSlot($slotInfo);
		if ($count < 1) {
			//this should be impossible at the protocol level, but in case of buggy core code this will prevent exploits
			throw new ItemStackRequestProcessException($this->prettyInventoryAndSlot($inventory, $slot) . ": Cannot take less than 1 items from a stack");
		}

		$existingItem = $inventory->getItem($slot);
		if ($existingItem->getCount() < $count) {
			throw new ItemStackRequestProcessException($this->prettyInventoryAndSlot($inventory, $slot) . ": Cannot take $count items from a stack of " . $existingItem->getCount());
		}

		$removed = $existingItem->pop($count);
		$inventory->setItem($slot, $existingItem);

		return $removed;
	}

	/**
	 * Adds items to the target slot, if they are stackable.
	 * @throws ItemStackRequestProcessException
	 */
	protected function addItemToSlot(ItemStackRequestSlotInfo $slotInfo, Item $item, int $count): void
	{
		$this->requestSlotInfos[] = $slotInfo;
		[$inventory, $slot] = $this->getBuilderInventoryAndSlot($slotInfo);
		if ($count < 1) {
			//this should be impossible at the protocol level, but in case of buggy core code this will prevent exploits
			throw new ItemStackRequestProcessException($this->prettyInventoryAndSlot($inventory, $slot) . ": Cannot take less than 1 items from a stack");
		}

		$existingItem = $inventory->getItem($slot);
		if (!$existingItem->isNull() && !$existingItem->canStackWith($item)) {
			throw new ItemStackRequestProcessException($this->prettyInventoryAndSlot($inventory, $slot) . ": Can only add items to an empty slot, or a slot containing the same item");
		}

		//we can't use the existing item here; it may be an empty stack
		$newItem = clone $item;
		$newItem->setCount($existingItem->getCount() + $count);
		$inventory->setItem($slot, $newItem);
	}

	protected function dropItem(Item $item, int $count): void
	{
		if ($count < 1) {
			throw new ItemStackRequestProcessException("Cannot drop less than 1 of an item");
		}
		$this->builder->addAction(new DropItemAction((clone $item)->setCount($count)));
	}

	/**
	 * @throws ItemStackRequestProcessException
	 */
	protected function setNextCreatedItem(?Item $item, bool $creative = false): void
	{
		if ($item !== null && $item->isNull()) {
			$item = null;
		}
		if ($this->nextCreatedItem !== null) {
			//while this is more complicated than simply adding the action when the item is taken, this ensures that
			//plugins can tell the difference between 1 item that got split into 2 slots, vs 2 separate items.
			if ($this->createdItemFromCreativeInventory && $this->createdItemsTakenCount > 0) {
				$this->nextCreatedItem->setCount($this->createdItemsTakenCount);
				$this->builder->addAction(new CreateItemAction($this->nextCreatedItem));
			} elseif ($this->createdItemsTakenCount < $this->nextCreatedItem->getCount()) {
				throw new ItemStackRequestProcessException("Not all of the previous created item was taken");
			}
		}
		$this->nextCreatedItem = $item;
		$this->createdItemFromCreativeInventory = $creative;
		$this->createdItemsTakenCount = 0;
	}

	/**
	 * @throws ItemStackRequestProcessException
	 */
	protected function beginCrafting(int $recipeId, int $repetitions): void
	{
		if ($this->specialTransaction !== null) {
			throw new ItemStackRequestProcessException("Another special transaction is already in progress");
		}
		if ($repetitions < 1) {
			throw new ItemStackRequestProcessException("Cannot craft a recipe less than 1 time");
		}
		if ($repetitions > 256) {
			//TODO: we can probably lower this limit to 64, but I'm unsure if there are cases where the client may
			//request more than 64 repetitions of a recipe.
			//It's already hard-limited to 256 repetitions in the protocol, so this is just a sanity check.
			throw new ItemStackRequestProcessException("Cannot craft a recipe more than 256 times");
		}
		$craftingManager = $this->player->getServer()->getCraftingManager();
		$recipeIndex = $recipeId - CraftingDataCache::RECIPE_ID_OFFSET;
		$recipe = $craftingManager->getCraftingRecipeFromIndex($recipeIndex);
		if ($recipe === null) {
			throw new ItemStackRequestProcessException("No such crafting recipe index: $recipeIndex");
		}

		$this->specialTransaction = new CraftingTransaction($this->player, $craftingManager, [], $recipe, $repetitions);

		//TODO: Since the system assumes that crafting can only be done in the crafting grid, we have to give it a
		//crafting grid to make the API happy. No implementation of getResultsFor() actually uses the crafting grid
		//right now, so this will work, but this will become a problem in the future for things like shulker boxes and
		//custom crafting recipes.
		$craftingResults = $recipe->getResultsFor($this->player->getCraftingGrid());
		foreach ($craftingResults as $k => $craftingResult) {
			$craftingResult->setCount($craftingResult->getCount() * $repetitions);
			$this->craftingResults[$k] = $craftingResult;
		}
		if (count($this->craftingResults) === 1) {
			//for multi-output recipes, later actions will tell us which result to create and when
			$this->setNextCreatedItem($this->craftingResults[array_key_first($this->craftingResults)]);
		}
	}

	/**
	 * @throws ItemStackRequestProcessException
	 */
	protected function takeCreatedItem(int $count): Item
	{
		if ($count < 1) {
			//this should be impossible at the protocol level, but in case of buggy core code this will prevent exploits
			throw new ItemStackRequestProcessException("Cannot take less than 1 created item");
		}
		$createdItem = $this->nextCreatedItem;
		if ($createdItem === null) {
			throw new ItemStackRequestProcessException("No created item is waiting to be taken");
		}

		if (!$this->createdItemFromCreativeInventory) {
			$availableCount = $createdItem->getCount() - $this->createdItemsTakenCount;
			if ($count > $availableCount) {
				throw new ItemStackRequestProcessException("Not enough created items available to be taken (have $availableCount, tried to take $count)");
			}
		}

		$this->createdItemsTakenCount += $count;
		$takenItem = clone $createdItem;
		$takenItem->setCount($count);
		if (!$this->createdItemFromCreativeInventory && $this->createdItemsTakenCount >= $createdItem->getCount()) {
			$this->setNextCreatedItem(null);
		}
		return $takenItem;
	}

	/**
	 * @throws ItemStackRequestProcessException
	 */
	private function assertDoingCrafting(): void
	{
		if (
			!$this->specialTransaction instanceof CraftingTransaction &&
			!$this->specialTransaction instanceof EnchantingTransaction &&
			!$this->specialTransaction instanceof AnvilTransaction &&
			!$this->specialTransaction instanceof LoomTransaction
		) {
			if ($this->specialTransaction === null) {
				throw new ItemStackRequestProcessException("Expected CraftRecipe or CraftRecipeAuto action to precede this action");
			} else {
				throw new ItemStackRequestProcessException("A different special transaction is already in progress");
			}
		}
	}

	/**
	 * @throws ItemStackRequestProcessException
	 */
	private function createLoomResult(BannerItem $baseBanner, BannerPatternType $patternType, Dye $dyeItem, int $repetitions): BannerItem
	{
		$existingPatterns = $baseBanner->getPatterns();
		if (count($existingPatterns) >= self::LOOM_MAX_PATTERNS) {
			throw new ItemStackRequestProcessException("Banner already has the maximum number of patterns");
		}

		$newPatterns = $existingPatterns;
		$newPatterns[] = new BannerPatternLayer($patternType, $dyeItem->getColor());

		$result = clone $baseBanner;
		$result->setPatterns($newPatterns);
		$result->setCount($repetitions);

		return $result;
	}

	private function loomPatternRequiresItem(BannerPatternType $patternType): bool
	{
		return in_array($patternType, self::LOOM_PATTERN_ITEM_MAP, true);
	}

	/**
	 * @throws ItemStackRequestProcessException
	 */
	private function validateLoomPatternItem(Item $patternItem, BannerPatternType $patternType): void
	{
		if ($patternItem->isNull()) {
			if ($this->loomPatternRequiresItem($patternType)) {
				throw new ItemStackRequestProcessException("Requested loom pattern requires a banner pattern item");
			}
			return;
		}

		$serializer = GlobalItemDataHandlers::getSerializer();
		$itemName = $serializer->serializeType($patternItem)->getName();
		$mappedType = self::LOOM_PATTERN_ITEM_MAP[$itemName] ?? null;
		if ($mappedType === null || $mappedType !== $patternType) {
			throw new ItemStackRequestProcessException("Banner pattern item does not match the requested pattern");
		}
	}

	/**
	 * @throws ItemStackRequestProcessException
	 */
	protected function processItemStackRequestAction(ItemStackRequestAction $action): void
	{
		if (
			$action instanceof TakeStackRequestAction ||
			$action instanceof PlaceStackRequestAction
		) {
			$this->transferItems($action->getSource(), $action->getDestination(), $action->getCount());
		} elseif ($action instanceof SwapStackRequestAction) {
			$this->requestSlotInfos[] = $action->getSlot1();
			$this->requestSlotInfos[] = $action->getSlot2();

			[$inventory1, $slot1] = $this->getBuilderInventoryAndSlot($action->getSlot1());
			[$inventory2, $slot2] = $this->getBuilderInventoryAndSlot($action->getSlot2());

			$item1 = $inventory1->getItem($slot1);
			$item2 = $inventory2->getItem($slot2);
			$inventory1->setItem($slot1, $item2);
			$inventory2->setItem($slot2, $item1);
		} elseif ($action instanceof DropStackRequestAction) {
			//TODO: this action has a "randomly" field, I have no idea what it's used for
			$dropped = $this->removeItemFromSlot($action->getSource(), $action->getCount());
			$this->builder->addAction(new DropItemAction($dropped));
		} elseif ($action instanceof DestroyStackRequestAction) {
			$destroyed = $this->removeItemFromSlot($action->getSource(), $action->getCount());
			$this->builder->addAction(new DestroyItemAction($destroyed));
		} elseif ($action instanceof CreativeCreateStackRequestAction) {
			$item = $this->player->getCreativeInventory()->getItem($action->getCreativeItemId());
			if ($item === null) {
				throw new ItemStackRequestProcessException("No such creative item index: " . $action->getCreativeItemId());
			}

			$this->setNextCreatedItem($item, true);
		} elseif ($action instanceof CraftRecipeStackRequestAction) {
			$window = $this->player->getCurrentWindow();
			if ($window instanceof EnchantInventory) {
				$optionId = $this->inventoryManager->getEnchantingTableOptionIndex($action->getRecipeId());
				if ($optionId !== null && ($option = $window->getOption($optionId)) !== null) {
					$this->specialTransaction = new EnchantingTransaction($this->player, $option, $optionId + 1);
					$this->setNextCreatedItem($window->getOutput($optionId));
				}
			} else {
				$this->beginCrafting($action->getRecipeId(), $action->getRepetitions());
			}
		} elseif ($action instanceof CraftRecipeAutoStackRequestAction) {
			$this->beginCrafting($action->getRecipeId(), $action->getRepetitions());
		} elseif ($action instanceof CraftRecipeOptionalStackRequestAction) {
			$window = $this->player->getCurrentWindow();
			if ($window instanceof AnvilInventory) {
				// Update the anvil inventory with the new name from filterStrings
				$filterStrings = $this->request->getFilterStrings();
				$this->player->getServer()->getLogger()->debug("[DEBUG-ANVIL] Filter strings count: " . count($filterStrings));
				$newName = $filterStrings[0] ?? null;
				$window->setNewItemName($newName);
				
				// Debug: log attempt to calculate anvil result from current window
				try{
					$inputName = $window->getInput()->isNull() ? 'AIR' : $window->getInput()->getName();
					$matName = $window->getMaterial()->isNull() ? 'AIR' : $window->getMaterial()->getName();
				}catch(\Throwable $e){
					$inputName = 'ERR'; $matName = 'ERR';
				}
				$this->player->getServer()->getLogger()->debug("[DEBUG-ANVIL] CraftRecipeOptional action: window input=" . $inputName . ", material=" . $matName . ", filter=" . ($newName ?? 'null'));
				$result = AnvilHelper::calculateResult($window->getInput(), $window->getMaterial(), $newName, $this->player->isCreative());
				if ($result !== null) {
					$this->player->getServer()->getLogger()->debug("[DEBUG-ANVIL] AnvilHelper returned result with xpCost=" . $result->getXpCost());
					$this->specialTransaction = new AnvilTransaction($this->player, $result, $newName);
					$this->setNextCreatedItem($result->getOutput());
					// Also update the result slot so client can see preview
					$window->setItem(\pocketmine\block\inventory\AnvilInventory::SLOT_RESULT, clone $result->getOutput());
				} else {
					// Clear result slot if no valid result
					$window->clear(\pocketmine\block\inventory\AnvilInventory::SLOT_RESULT);
				}
			}
		} elseif ($action instanceof LoomStackRequestAction) {
			$window = $this->player->getCurrentWindow();
			if (!$window instanceof LoomInventory) {
				throw new ItemStackRequestProcessException("Received loom action while no loom window is open");
			}

			$patternType = BannerPatternTypeIdMap::getInstance()->fromId($action->getPatternId());
			if ($patternType === null) {
				throw new ItemStackRequestProcessException("Unknown loom pattern id: " . $action->getPatternId());
			}

			$bannerItem = $window->getItem(LoomInventory::SLOT_BANNER);
			if (!$bannerItem instanceof BannerItem || $bannerItem->isNull()) {
				throw new ItemStackRequestProcessException("Loom requires at least one banner in the banner slot");
			}

			$dyeItem = $window->getItem(LoomInventory::SLOT_DYE);
			if (!$dyeItem instanceof Dye || $dyeItem->isNull()) {
				throw new ItemStackRequestProcessException("Loom requires a dye in the dye slot");
			}

			$repetitions = $action->getRepetitions();
			if ($repetitions < 1) {
				throw new ItemStackRequestProcessException("Cannot apply a loom pattern less than 1 time");
			}
			if ($repetitions > $bannerItem->getCount()) {
				throw new ItemStackRequestProcessException("Not enough banners in the loom to craft $repetitions pattern(s)");
			}
			if ($repetitions > $dyeItem->getCount()) {
				throw new ItemStackRequestProcessException("Not enough dye in the loom to craft $repetitions pattern(s)");
			}

			$this->validateLoomPatternItem($window->getItem(LoomInventory::SLOT_PATTERN), $patternType);
			$resultItem = $this->createLoomResult($bannerItem, $patternType, $dyeItem, $repetitions);

			$this->specialTransaction = new LoomTransaction(
				$this->player,
				clone $bannerItem,
				clone $dyeItem,
				clone $resultItem,
				$repetitions
			);
			$this->setNextCreatedItem($resultItem);
		} elseif ($action instanceof CraftingConsumeInputStackRequestAction) {
			// Special handling for anvil/loom - they don't use CraftRecipe but CraftRecipeOptional
			// When taking result, client sends CraftingConsumeInput even without a crafting transaction
			$window = $this->player->getCurrentWindow();
			if ($window instanceof AnvilInventory || $window instanceof LoomInventory) {
				// For anvil/loom, just remove the item from the source slot
				// The transaction validation will happen in AnvilTransaction/LoomTransaction
				$this->removeItemFromSlot($action->getSource(), $action->getCount());
			} else {
				$this->assertDoingCrafting();
				$this->removeItemFromSlot($action->getSource(), $action->getCount()); //output discarded - we allow CraftingTransaction to verify the balance
			}
		} elseif ($action instanceof CraftingCreateSpecificResultStackRequestAction) {
			$this->assertDoingCrafting();

			$nextResultItem = $this->craftingResults[$action->getResultIndex()] ?? null;
			if ($nextResultItem === null) {
				throw new ItemStackRequestProcessException("No such crafting result index: " . $action->getResultIndex());
			}
			$this->setNextCreatedItem($nextResultItem);
		} elseif ($action instanceof DeprecatedCraftingResultsStackRequestAction) {
			//no obvious use
		} else {
			throw new ItemStackRequestProcessException("Unhandled item stack request action");
		}
	}


	/**
	 * @throws ItemStackRequestProcessException
	 */
	public function generateInventoryTransaction(): ?InventoryTransaction
	{
		foreach (Utils::promoteKeys($this->request->getActions()) as $k => $action) {
			try {
				$this->processItemStackRequestAction($action);
			} catch (ItemStackRequestProcessException $e) {
				throw new ItemStackRequestProcessException("Error processing action $k (" . (new \ReflectionClass($action))->getShortName() . "): " . $e->getMessage(), 0, $e);
			}
		}
		$this->setNextCreatedItem(null);
		$inventoryActions = $this->builder->generateActions();
		if (count($inventoryActions) === 0) {
			return null;
		}

		$transaction = $this->specialTransaction ?? new InventoryTransaction($this->player);
		foreach ($inventoryActions as $action) {
			$transaction->addAction($action);
		}

		return $transaction;
	}

	public function getItemStackResponseBuilder(): ItemStackResponseBuilder
	{
		$builder = new ItemStackResponseBuilder($this->request->getRequestId(), $this->inventoryManager);
		foreach ($this->requestSlotInfos as $requestInfo) {
			$builder->addSlot($requestInfo->getContainerName()->getContainerId(), $requestInfo->getSlotId());
		}

		return $builder;
	}

	public function buildItemStackResponse(): ItemStackResponse
	{
		return $this->getItemStackResponseBuilder()->build();
	}
}
