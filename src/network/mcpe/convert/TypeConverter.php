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

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\inventory\EnchantInventory;
use pocketmine\block\inventory\LoomInventory;
use pocketmine\crafting\CraftingGrid;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\CreateItemAction;
use pocketmine\inventory\transaction\action\DestroyItemAction;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\protocol\types\GameMode as ProtocolGameMode;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;

class TypeConverter{
	use SingletonTrait;

	private const DAMAGE_TAG = "Damage"; //TAG_Int
	private const DAMAGE_TAG_CONFLICT_RESOLUTION = "___Damage_ProtocolCollisionResolution___";
	private const PM_ID_TAG = "___Id___";
	private const PM_META_TAG = "___Meta___";

	/** @var int */
	private $shieldRuntimeId;

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

	public function coreItemStackToRecipeIngredient(Item $itemStack) : RecipeIngredient{
		if($itemStack->isNull()){
			return new RecipeIngredient(0, 0, 0);
		}
		if($itemStack->hasAnyDamageValue()){
			[$id, ] = ItemTranslator::getInstance()->toNetworkId($itemStack->getId(), 0);
			$meta = 0x7fff;
		}else{
			[$id, $meta] = ItemTranslator::getInstance()->toNetworkId($itemStack->getId(), $itemStack->getMeta());
		}
		return new RecipeIngredient($id, $meta, $itemStack->getCount());
	}

	public function recipeIngredientToCoreItemStack(RecipeIngredient $ingredient) : Item{
		if($ingredient->getId() === 0){
			return ItemFactory::getInstance()->get(ItemIds::AIR, 0, 0);
		}
		[$id, $meta] = ItemTranslator::getInstance()->fromNetworkIdWithWildcardHandling($ingredient->getId(), $ingredient->getMeta());
		return ItemFactory::getInstance()->get($id, $meta, $ingredient->getCount());
	}

	public function coreItemStackToNet(Item $itemStack) : ItemStack{
		if($itemStack->isNull()){
			return ItemStack::null();
		}
		$nbt = null;
		if($itemStack->hasNamedTag()){
			$nbt = clone $itemStack->getNamedTag();
		}

		$isBlockItem = $itemStack->getId() < 256;

		$idMeta = ItemTranslator::getInstance()->toNetworkIdQuiet($itemStack->getId(), $itemStack->getMeta());
		if($idMeta === null){
			//Display unmapped items as INFO_UPDATE, but stick something in their NBT to make sure they don't stack with
			//other unmapped items.
			[$id, $meta] = ItemTranslator::getInstance()->toNetworkId(ItemIds::INFO_UPDATE, 0);
			if($nbt === null){
				$nbt = new CompoundTag();
			}
			$nbt->setInt(self::PM_ID_TAG, $itemStack->getId());
			$nbt->setInt(self::PM_META_TAG, $itemStack->getMeta());
		}else{
			[$id, $meta] = $idMeta;

			if($itemStack instanceof Durable and $itemStack->getDamage() > 0){
				if($nbt !== null){
					if(($existing = $nbt->getTag(self::DAMAGE_TAG)) !== null){
						$nbt->removeTag(self::DAMAGE_TAG);
						$nbt->setTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION, $existing);
					}
				}else{
					$nbt = new CompoundTag();
				}
				$nbt->setInt(self::DAMAGE_TAG, $itemStack->getDamage());
			}elseif($isBlockItem && $itemStack->getMeta() !== 0){
				//TODO HACK: This foul-smelling code ensures that we can correctly deserialize an item when the
				//client sends it back to us, because as of 1.16.220, blockitems quietly discard their metadata
				//client-side. Aside from being very annoying, this also breaks various server-side behaviours.
				if($nbt === null){
					$nbt = new CompoundTag();
				}
				$nbt->setInt(self::PM_META_TAG, $itemStack->getMeta());
			}
		}

		$blockRuntimeId = 0;
		if($isBlockItem){
			$block = $itemStack->getBlock();
			if($block->getId() !== BlockLegacyIds::AIR){
				$blockRuntimeId = RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId());
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
			return ItemFactory::getInstance()->get(ItemIds::AIR, 0, 0);
		}
		$compound = $itemStack->getNbt();

		[$id, $meta] = ItemTranslator::getInstance()->fromNetworkId($itemStack->getId(), $itemStack->getMeta());

		if($compound !== null){
			$compound = clone $compound;
			if(($idTag = $compound->getTag(self::PM_ID_TAG)) instanceof IntTag){
				$id = $idTag->getValue();
				$compound->removeTag(self::PM_ID_TAG);
			}
			if(($damageTag = $compound->getTag(self::DAMAGE_TAG)) instanceof IntTag){
				$meta = $damageTag->getValue();
				$compound->removeTag(self::DAMAGE_TAG);
				if(($conflicted = $compound->getTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION)) !== null){
					$compound->removeTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION);
					$compound->setTag(self::DAMAGE_TAG, $conflicted);
				}
			}elseif(($metaTag = $compound->getTag(self::PM_META_TAG)) instanceof IntTag){
				//TODO HACK: This foul-smelling code ensures that we can correctly deserialize an item when the
				//client sends it back to us, because as of 1.16.220, blockitems quietly discard their metadata
				//client-side. Aside from being very annoying, this also breaks various server-side behaviours.
				$meta = $metaTag->getValue();
				$compound->removeTag(self::PM_META_TAG);
			}
			if($compound->count() === 0){
				$compound = null;
			}
		}

		try{
			return ItemFactory::getInstance()->get(
				$id,
				$meta,
				$itemStack->getCount(),
				$compound
			);
		}catch(NbtException $e){
			throw TypeConversionException::wrap($e, "Bad itemstack NBT data");
		}
	}

	/**
	 * @param int[] $test
	 * @phpstan-param array<int, int> $test
	 * @phpstan-param \Closure(Inventory) : bool $c
	 * @phpstan-return array{int, Inventory}
	 */
	protected function mapUIInventory(int $slot, array $test, ?Inventory $inventory, \Closure $c) : ?array{
		if($inventory === null){
			return null;
		}
		if(array_key_exists($slot, $test) && $c($inventory)){
			return [$test[$slot], $inventory];
		}
		return null;
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
				if($action->windowId === ContainerIds::UI and $action->inventorySlot > 0){
					if($action->inventorySlot === UIInventorySlotOffset::CREATED_ITEM_OUTPUT){
						return null; //useless noise
					}
					$pSlot = $action->inventorySlot;

					$craftingGrid = $player->getCraftingGrid();
					$mapped =
						$this->mapUIInventory($pSlot, UIInventorySlotOffset::CRAFTING2X2_INPUT, $craftingGrid,
							function(Inventory $i) : bool{ return $i instanceof CraftingGrid && $i->getGridWidth() === CraftingGrid::SIZE_SMALL; }) ??
						$this->mapUIInventory($pSlot, UIInventorySlotOffset::CRAFTING3X3_INPUT, $craftingGrid,
							function(Inventory $i) : bool{ return $i instanceof CraftingGrid && $i->getGridWidth() === CraftingGrid::SIZE_BIG; });
					if($mapped === null){
						$current = $player->getCurrentWindow();
						$mapped =
							$this->mapUIInventory($pSlot, UIInventorySlotOffset::ANVIL, $current,
								function(Inventory $i) : bool{ return $i instanceof AnvilInventory; }) ??
							$this->mapUIInventory($pSlot, UIInventorySlotOffset::ENCHANTING_TABLE, $current,
								function(Inventory $i) : bool{ return $i instanceof EnchantInventory; }) ??
							$this->mapUIInventory($pSlot, UIInventorySlotOffset::LOOM, $current,
								fn(Inventory $i) => $i instanceof LoomInventory);
					}
					if($mapped === null){
						throw new TypeConversionException("Unmatched UI inventory slot offset $pSlot");
					}
					[$slot, $window] = $mapped;
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
