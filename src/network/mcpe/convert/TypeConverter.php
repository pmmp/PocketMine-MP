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

namespace pocketmine\network\mcpe\convert;

use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\crafting\MetaWildcardRecipeIngredient;
use pocketmine\crafting\RecipeIngredient;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\inventory\transaction\action\CreateItemAction;
use pocketmine\inventory\transaction\action\DestroyItemAction;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\protocol\types\GameMode as ProtocolGameMode;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient as ProtocolRecipeIngredient;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use function get_class;
use function morton2d_encode;

class TypeConverter{
	use SingletonTrait;

	private const PM_ID_TAG = "___Id___";

	private const RECIPE_INPUT_WILDCARD_META = 0x7fff;

	private int $shieldRuntimeId;

	public function __construct(){
		//TODO: inject stuff via constructor
		$this->shieldRuntimeId = GlobalItemTypeDictionary::getInstance()->getDictionary()->fromStringId("minecraft:shield");
	}

	/**
	 * Returns a client-friendly gamemode of the specified real gamemode
	 * This function takes care of handling gamemodes known to MCPE (as of 1.1.0.3, that includes Survival, Creative and Adventure)
	 *
	 * @internal
	 */
	public function coreGameModeToProtocol(GameMode $gamemode) : int{
		switch($gamemode->id()){
			case GameMode::SURVIVAL()->id():
				return ProtocolGameMode::SURVIVAL;
			case GameMode::CREATIVE()->id():
			case GameMode::SPECTATOR()->id():
				return ProtocolGameMode::CREATIVE;
			case GameMode::ADVENTURE()->id():
				return ProtocolGameMode::ADVENTURE;
			default:
				throw new AssumptionFailedError("Unknown game mode");
		}
	}

	public function protocolGameModeName(GameMode $gameMode) : string{
		switch($gameMode->id()){
			case GameMode::SURVIVAL()->id(): return "Survival";
			case GameMode::ADVENTURE()->id(): return "Adventure";
			default: return "Creative";
		}
	}

	public function protocolGameModeToCore(int $gameMode) : ?GameMode{
		switch($gameMode){
			case ProtocolGameMode::SURVIVAL:
				return GameMode::SURVIVAL();
			case ProtocolGameMode::CREATIVE:
				return GameMode::CREATIVE();
			case ProtocolGameMode::ADVENTURE:
				return GameMode::ADVENTURE();
			case ProtocolGameMode::CREATIVE_VIEWER:
			case ProtocolGameMode::SURVIVAL_VIEWER:
				return GameMode::SPECTATOR();
			default:
				return null;
		}
	}

	public function coreRecipeIngredientToNet(?RecipeIngredient $ingredient) : ProtocolRecipeIngredient{
		if($ingredient === null){
			return new ProtocolRecipeIngredient(0, 0, 0);
		}
		if($ingredient instanceof MetaWildcardRecipeIngredient){
			$id = GlobalItemTypeDictionary::getInstance()->getDictionary()->fromStringId($ingredient->getItemId());
			$meta = self::RECIPE_INPUT_WILDCARD_META;
		}elseif($ingredient instanceof ExactRecipeIngredient){
			$item = $ingredient->getItem();
			[$id, $meta, $blockRuntimeId] = ItemTranslator::getInstance()->toNetworkId($item);
			if($blockRuntimeId !== ItemTranslator::NO_BLOCK_RUNTIME_ID){
				$meta = RuntimeBlockMapping::getInstance()->getBlockStateDictionary()->getMetaFromStateId($blockRuntimeId);
				if($meta === null){
					throw new AssumptionFailedError("Every block state should have an associated meta value");
				}
			}
		}else{
			throw new \LogicException("Unsupported recipe ingredient type " . get_class($ingredient) . ", only " . ExactRecipeIngredient::class . " and " . MetaWildcardRecipeIngredient::class . " are supported");
		}
		return new ProtocolRecipeIngredient($id, $meta, 1);
	}

	public function netRecipeIngredientToCore(ProtocolRecipeIngredient $ingredient) : ?RecipeIngredient{
		if($ingredient->getId() === 0){
			return null;
		}

		$itemId = GlobalItemTypeDictionary::getInstance()->getDictionary()->fromIntId($ingredient->getId());

		if($ingredient->getMeta() === self::RECIPE_INPUT_WILDCARD_META){
			return new MetaWildcardRecipeIngredient($itemId);
		}

		$meta = $ingredient->getMeta();
		$blockRuntimeId = null;
		if(($blockId = BlockItemIdMap::getInstance()->lookupBlockId($itemId)) !== null){
			$blockRuntimeId = RuntimeBlockMapping::getInstance()->getBlockStateDictionary()->lookupStateIdFromIdMeta($blockId, $meta);
			if($blockRuntimeId !== null){
				$meta = 0;
			}
		}
		$result = ItemTranslator::getInstance()->fromNetworkId($ingredient->getId(), $meta, $blockRuntimeId ?? ItemTranslator::NO_BLOCK_RUNTIME_ID);
		return new ExactRecipeIngredient($result);
	}

	public function coreItemStackToNet(Item $itemStack) : ItemStack{
		if($itemStack->isNull()){
			return ItemStack::null();
		}
		$nbt = null;
		if($itemStack->hasNamedTag()){
			$nbt = clone $itemStack->getNamedTag();
		}

		$idMeta = ItemTranslator::getInstance()->toNetworkIdQuiet($itemStack);
		if($idMeta === null){
			//Display unmapped items as INFO_UPDATE, but stick something in their NBT to make sure they don't stack with
			//other unmapped items.
			[$id, $meta, $blockRuntimeId] = ItemTranslator::getInstance()->toNetworkId(VanillaBlocks::INFO_UPDATE()->asItem());
			if($nbt === null){
				$nbt = new CompoundTag();
			}
			$nbt->setInt(self::PM_ID_TAG, morton2d_encode($itemStack->getTypeId(), $itemStack->computeTypeData()));
		}else{
			[$id, $meta, $blockRuntimeId] = $idMeta;
		}

		return new ItemStack(
			$id,
			$meta,
			$itemStack->getCount(),
			$blockRuntimeId,
			$nbt,
			[],
			[],
			$id === $this->shieldRuntimeId ? 0 : null
		);
	}

	/**
	 * @throws TypeConversionException
	 */
	public function netItemStackToCore(ItemStack $itemStack) : Item{
		if($itemStack->getId() === 0){
			return VanillaItems::AIR();
		}
		$compound = $itemStack->getNbt();

		$itemResult = ItemTranslator::getInstance()->fromNetworkId($itemStack->getId(), $itemStack->getMeta(), $itemStack->getBlockRuntimeId());

		if($compound !== null){
			$compound = clone $compound;
		}

		$itemResult->setCount($itemStack->getCount());
		if($compound !== null){
			try{
				$itemResult->setNamedTag($compound);
			}catch(NbtException $e){
				throw TypeConversionException::wrap($e, "Bad itemstack NBT data");
			}
		}

		return $itemResult;
	}

	/**
	 * @throws TypeConversionException
	 */
	public function createInventoryAction(NetworkInventoryAction $action, Player $player, InventoryManager $inventoryManager) : ?InventoryAction{
		if($action->oldItem->getItemStack()->equals($action->newItem->getItemStack())){
			//filter out useless noise in 1.13
			return null;
		}
		try{
			$old = $this->netItemStackToCore($action->oldItem->getItemStack());
		}catch(TypeConversionException $e){
			throw TypeConversionException::wrap($e, "Inventory action: oldItem");
		}
		try{
			$new = $this->netItemStackToCore($action->newItem->getItemStack());
		}catch(TypeConversionException $e){
			throw TypeConversionException::wrap($e, "Inventory action: newItem");
		}
		switch($action->sourceType){
			case NetworkInventoryAction::SOURCE_CONTAINER:
				if($action->windowId === ContainerIds::UI && $action->inventorySlot === UIInventorySlotOffset::CREATED_ITEM_OUTPUT){
					return null; //useless noise
				}
				$located = $inventoryManager->locateWindowAndSlot($action->windowId, $action->inventorySlot);
				if($located !== null){
					[$window, $slot] = $located;
					return new SlotChangeAction($window, $slot, $old, $new);
				}

				throw new TypeConversionException("No open container with window ID $action->windowId");
			case NetworkInventoryAction::SOURCE_WORLD:
				if($action->inventorySlot !== NetworkInventoryAction::ACTION_MAGIC_SLOT_DROP_ITEM){
					throw new TypeConversionException("Only expecting drop-item world actions from the client!");
				}

				return new DropItemAction($new);
			case NetworkInventoryAction::SOURCE_CREATIVE:
				switch($action->inventorySlot){
					case NetworkInventoryAction::ACTION_MAGIC_SLOT_CREATIVE_DELETE_ITEM:
						return new DestroyItemAction($new);
					case NetworkInventoryAction::ACTION_MAGIC_SLOT_CREATIVE_CREATE_ITEM:
						return new CreateItemAction($old);
					default:
						throw new TypeConversionException("Unexpected creative action type $action->inventorySlot");

				}
			case NetworkInventoryAction::SOURCE_TODO:
				//These are used to balance a transaction that involves special actions, like crafting, enchanting, etc.
				//The vanilla server just accepted these without verifying them. We don't need to care about them since
				//we verify crafting by checking for imbalances anyway.
				return null;
			default:
				throw new TypeConversionException("Unknown inventory source type $action->sourceType");
		}
	}
}
