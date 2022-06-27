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

use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\inventory\CraftingTableInventory;
use pocketmine\block\inventory\EnchantInventory;
use pocketmine\block\inventory\LoomInventory;
use pocketmine\block\inventory\StonecutterInventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\crafting\MetaWildcardRecipeIngredient;
use pocketmine\crafting\RecipeIngredient;
use pocketmine\inventory\transaction\action\CreateItemAction;
use pocketmine\inventory\transaction\action\DestroyItemAction;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
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

class TypeConverter{
	use SingletonTrait;

	private const DAMAGE_TAG = "Damage"; //TAG_Int
	private const DAMAGE_TAG_CONFLICT_RESOLUTION = "___Damage_ProtocolCollisionResolution___";
	private const PM_ID_TAG = "___Id___";
	private const PM_META_TAG = "___Meta___";

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
			[$id, $meta] = ItemTranslator::getInstance()->toNetworkId($item);
			if($id < 256){
				//TODO: this is needed for block crafting recipes to work - we need to replace this with some kind of
				//blockstate <-> meta mapping table so that we can remove the legacy code from the core
				$meta = $item->getMeta();
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

		if($ingredient->getMeta() === self::RECIPE_INPUT_WILDCARD_META){
			$itemId = GlobalItemTypeDictionary::getInstance()->getDictionary()->fromIntId($ingredient->getId());
			return new MetaWildcardRecipeIngredient($itemId);
		}

		//TODO: this won't be handled properly for blockitems because a block runtimeID is expected rather than a meta value
		$result = ItemTranslator::getInstance()->fromNetworkId($ingredient->getId(), $ingredient->getMeta(), 0);
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
			$nbt->setInt(self::PM_ID_TAG, $itemStack->getId());
			$nbt->setInt(self::PM_META_TAG, $itemStack->getMeta());
		}else{
			[$id, $meta, $blockRuntimeId] = $idMeta;

			if($itemStack instanceof Durable && $itemStack->getDamage() > 0){
				if($nbt !== null){
					if(($existing = $nbt->getTag(self::DAMAGE_TAG)) !== null){
						$nbt->removeTag(self::DAMAGE_TAG);
						$nbt->setTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION, $existing);
					}
				}else{
					$nbt = new CompoundTag();
				}
				$nbt->setInt(self::DAMAGE_TAG, $itemStack->getDamage());
			}
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

			$id = $meta = null;
			if($itemResult->getId() === ItemIds::INFO_UPDATE && $itemResult->getMeta() === 0){
				if(($idTag = $compound->getTag(self::PM_ID_TAG)) instanceof IntTag){
					$id = $idTag->getValue();
					$compound->removeTag(self::PM_ID_TAG);
				}
				if(($metaTag = $compound->getTag(self::PM_META_TAG)) instanceof IntTag){
					$meta = $metaTag->getValue();
					$compound->removeTag(self::PM_META_TAG);
				}
			}
			if(($damageTag = $compound->getTag(self::DAMAGE_TAG)) instanceof IntTag){
				$meta = $damageTag->getValue();
				$compound->removeTag(self::DAMAGE_TAG);
				if(($conflicted = $compound->getTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION)) !== null){
					$compound->removeTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION);
					$compound->setTag(self::DAMAGE_TAG, $conflicted);
				}
			}
			if($compound->count() === 0){
				$compound = null;
			}
			if($meta !== null){
				if($id !== null && ($id < -0x8000 || $id >= 0x7fff)){
					throw new TypeConversionException("Item ID must be in range " . -0x8000 . " ... " . 0x7fff . " (received $id)");
				}
				if($meta < 0 || $meta >= 0x7ffe){ //this meta value may have been restored from the NBT
					throw new TypeConversionException("Item meta must be in range 0 ... " . 0x7ffe . " (received $meta)");
				}
				$itemResult = ItemFactory::getInstance()->get($id ?? $itemResult->getId(), $meta);
			}
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
				$window = null;
				if($action->windowId === ContainerIds::UI && $action->inventorySlot > 0){
					if($action->inventorySlot === UIInventorySlotOffset::CREATED_ITEM_OUTPUT){
						return null; //useless noise
					}
					$pSlot = $action->inventorySlot;

					$slot = UIInventorySlotOffset::CRAFTING2X2_INPUT[$pSlot] ?? null;
					if($slot !== null){
						$window = $player->getCraftingGrid();
					}elseif(($current = $player->getCurrentWindow()) !== null){
						$slotMap = match(true){
							$current instanceof AnvilInventory => UIInventorySlotOffset::ANVIL,
							$current instanceof EnchantInventory => UIInventorySlotOffset::ENCHANTING_TABLE,
							$current instanceof LoomInventory => UIInventorySlotOffset::LOOM,
							$current instanceof StonecutterInventory => [UIInventorySlotOffset::STONE_CUTTER_INPUT => StonecutterInventory::SLOT_INPUT],
							$current instanceof CraftingTableInventory => UIInventorySlotOffset::CRAFTING3X3_INPUT,
							default => null
						};
						if($slotMap !== null){
							$window = $current;
							$slot = $slotMap[$pSlot] ?? null;
						}
					}
					if($slot === null){
						throw new TypeConversionException("Unmatched UI inventory slot offset $pSlot");
					}
				}else{
					$window = $inventoryManager->getWindow($action->windowId);
					$slot = $action->inventorySlot;
				}
				if($window !== null){
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
				//These types need special handling.
				switch($action->windowId){
					case NetworkInventoryAction::SOURCE_TYPE_CRAFTING_RESULT:
					case NetworkInventoryAction::SOURCE_TYPE_CRAFTING_USE_INGREDIENT:
						return null;
				}

				//TODO: more stuff
				throw new TypeConversionException("No open container with window ID $action->windowId");
			default:
				throw new TypeConversionException("Unknown inventory source type $action->sourceType");
		}
	}
}
