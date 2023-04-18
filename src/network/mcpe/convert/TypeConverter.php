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

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\types\GameMode as ProtocolGameMode;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient;
use pocketmine\network\mcpe\protocol\types\recipe\StringIdMetaItemDescriptor;
use pocketmine\player\GameMode;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;

class TypeConverter{
	use SingletonTrait;

	private const DAMAGE_TAG = "Damage"; //TAG_Int
	private const DAMAGE_TAG_CONFLICT_RESOLUTION = "___Damage_ProtocolCollisionResolution___";
	private const PM_ID_TAG = "___Id___";
	private const PM_META_TAG = "___Meta___";

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

	public function coreItemStackToRecipeIngredient(Item $itemStack) : RecipeIngredient{
		if($itemStack->isNull()){
			return new RecipeIngredient(null, 0);
		}
		if($itemStack->hasAnyDamageValue()){
			[$id, ] = ItemTranslator::getInstance()->toNetworkId($itemStack->getId(), 0);
			$meta = 0x7fff;
		}else{
			[$id, $meta] = ItemTranslator::getInstance()->toNetworkId($itemStack->getId(), $itemStack->getMeta());
		}
		return new RecipeIngredient(new IntIdMetaItemDescriptor($id, $meta), $itemStack->getCount());
	}

	public function recipeIngredientToCoreItemStack(RecipeIngredient $ingredient) : Item{
		$descriptor = $ingredient->getDescriptor();
		if($descriptor === null){
			return VanillaItems::AIR();
		}
		if($descriptor instanceof IntIdMetaItemDescriptor){
			[$id, $meta] = ItemTranslator::getInstance()->fromNetworkIdWithWildcardHandling($descriptor->getId(), $descriptor->getMeta());
			return ItemFactory::getInstance()->get($id, $meta, $ingredient->getCount());
		}
		if($descriptor instanceof StringIdMetaItemDescriptor){
			$intId = GlobalItemTypeDictionary::getInstance()->getDictionary()->fromStringId($descriptor->getId());
			[$id, $meta] = ItemTranslator::getInstance()->fromNetworkIdWithWildcardHandling($intId, $descriptor->getMeta());
			return ItemFactory::getInstance()->get($id, $meta, $ingredient->getCount());
		}

		throw new \LogicException("Unsupported conversion of recipe ingredient to core item stack");
	}

	public function coreItemStackToNet(Item $itemStack) : ItemStack{
		if($itemStack->isNull()){
			return ItemStack::null();
		}
		$nbt = $itemStack->getNamedTag();
		if($nbt->count() === 0){
			$nbt = null;
		}else{
			$nbt = clone $nbt;
		}

		$internalId = $itemStack->getId();
		$internalMeta = $itemStack->getMeta();
		$idMeta = ItemTranslator::getInstance()->toNetworkIdQuiet($internalId, $internalMeta);
		if($idMeta === null){
			//Display unmapped items as INFO_UPDATE, but stick something in their NBT to make sure they don't stack with
			//other unmapped items.
			[$id, $meta] = ItemTranslator::getInstance()->toNetworkId(ItemIds::INFO_UPDATE, 0);
			if($nbt === null){
				$nbt = new CompoundTag();
			}
			$nbt->setInt(self::PM_ID_TAG, $internalId);
			$nbt->setInt(self::PM_META_TAG, $internalMeta);
		}else{
			[$id, $meta] = $idMeta;

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
				$meta = 0;
			}
		}

		$blockRuntimeId = 0;
		if($internalId < 256){
			$block = $itemStack->getBlock();
			if($block->getId() !== BlockLegacyIds::AIR){
				$blockRuntimeId = RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId());
				$meta = 0;
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

		[$id, $meta] = ItemTranslator::getInstance()->fromNetworkId($itemStack->getId(), $itemStack->getMeta());
		if($itemStack->getBlockRuntimeId() !== 0){
			//blockitem meta is zeroed out by the client, so we have to infer it from the block runtime ID
			$blockFullId = RuntimeBlockMapping::getInstance()->fromRuntimeId($itemStack->getBlockRuntimeId());
			$meta = $blockFullId & Block::INTERNAL_METADATA_MASK;
		}

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
			}
			if($compound->count() === 0){
				$compound = null;
			}
		}
		if($id < -0x8000 || $id >= 0x7fff){
			throw new TypeConversionException("Item ID must be in range " . -0x8000 . " ... " . 0x7fff . " (received $id)");
		}
		if($meta < 0 || $meta >= 0x7fff){ //this meta value may have been restored from the NBT
			throw new TypeConversionException("Item meta must be in range 0 ... " . 0x7fff . " (received $meta)");
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
}
