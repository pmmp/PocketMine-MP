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
use pocketmine\crafting\TagWildcardRecipeIngredient;
use pocketmine\data\bedrock\BedrockDataFiles;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\GameMode as ProtocolGameMode;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackExtraData;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackExtraDataShield;
use pocketmine\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient as ProtocolRecipeIngredient;
use pocketmine\network\mcpe\protocol\types\recipe\StringIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\TagItemDescriptor;
use pocketmine\player\GameMode;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use function get_class;

class TypeConverter{
	use SingletonTrait;

	private const PM_ID_TAG = "___Id___";

	private const RECIPE_INPUT_WILDCARD_META = 0x7fff;

	private BlockItemIdMap $blockItemIdMap;
	private BlockTranslator $blockTranslator;
	private ItemTranslator $itemTranslator;
	private ItemTypeDictionary $itemTypeDictionary;
	private int $shieldRuntimeId;

	private SkinAdapter $skinAdapter;

	public function __construct(){
		//TODO: inject stuff via constructor
		$this->blockItemIdMap = BlockItemIdMap::getInstance();

		$canonicalBlockStatesRaw = Filesystem::fileGetContents(BedrockDataFiles::CANONICAL_BLOCK_STATES_NBT);
		$metaMappingRaw = Filesystem::fileGetContents(BedrockDataFiles::BLOCK_STATE_META_MAP_JSON);
		$this->blockTranslator = new BlockTranslator(
			BlockStateDictionary::loadFromString($canonicalBlockStatesRaw, $metaMappingRaw),
			GlobalBlockStateHandlers::getSerializer()
		);

		$this->itemTypeDictionary = ItemTypeDictionaryFromDataHelper::loadFromString(Filesystem::fileGetContents(BedrockDataFiles::REQUIRED_ITEM_LIST_JSON));
		$this->shieldRuntimeId = $this->itemTypeDictionary->fromStringId(ItemTypeNames::SHIELD);

		$this->itemTranslator = new ItemTranslator(
			$this->itemTypeDictionary,
			$this->blockTranslator->getBlockStateDictionary(),
			GlobalItemDataHandlers::getSerializer(),
			GlobalItemDataHandlers::getDeserializer(),
			$this->blockItemIdMap
		);

		$this->skinAdapter = new LegacySkinAdapter();
	}

	public function getBlockTranslator() : BlockTranslator{ return $this->blockTranslator; }

	public function getItemTypeDictionary() : ItemTypeDictionary{ return $this->itemTypeDictionary; }

	public function getItemTranslator() : ItemTranslator{ return $this->itemTranslator; }

	public function getSkinAdapter() : SkinAdapter{ return $this->skinAdapter; }

	public function setSkinAdapter(SkinAdapter $skinAdapter) : void{
		$this->skinAdapter = $skinAdapter;
	}

	/**
	 * Returns a client-friendly gamemode of the specified real gamemode
	 * This function takes care of handling gamemodes known to MCPE (as of 1.1.0.3, that includes Survival, Creative and Adventure)
	 *
	 * @internal
	 */
	public function coreGameModeToProtocol(GameMode $gamemode) : int{
		return match($gamemode){
			GameMode::SURVIVAL => ProtocolGameMode::SURVIVAL,
			//TODO: native spectator support
			GameMode::CREATIVE, GameMode::SPECTATOR => ProtocolGameMode::CREATIVE,
			GameMode::ADVENTURE => ProtocolGameMode::ADVENTURE,
		};
	}

	public function protocolGameModeToCore(int $gameMode) : ?GameMode{
		return match($gameMode){
			ProtocolGameMode::SURVIVAL => GameMode::SURVIVAL,
			ProtocolGameMode::CREATIVE => GameMode::CREATIVE,
			ProtocolGameMode::ADVENTURE => GameMode::ADVENTURE,
			ProtocolGameMode::SURVIVAL_VIEWER, ProtocolGameMode::CREATIVE_VIEWER => GameMode::SPECTATOR,
			//TODO: native spectator support
			default => null,
		};
	}

	public function coreRecipeIngredientToNet(?RecipeIngredient $ingredient) : ProtocolRecipeIngredient{
		if($ingredient === null){
			return new ProtocolRecipeIngredient(null, 0);
		}
		if($ingredient instanceof MetaWildcardRecipeIngredient){
			$id = $this->itemTypeDictionary->fromStringId($ingredient->getItemId());
			$meta = self::RECIPE_INPUT_WILDCARD_META;
			$descriptor = new IntIdMetaItemDescriptor($id, $meta);
		}elseif($ingredient instanceof ExactRecipeIngredient){
			$item = $ingredient->getItem();
			[$id, $meta, $blockRuntimeId] = $this->itemTranslator->toNetworkId($item);
			if($blockRuntimeId !== null){
				$meta = $this->blockTranslator->getBlockStateDictionary()->getMetaFromStateId($blockRuntimeId);
				if($meta === null){
					throw new AssumptionFailedError("Every block state should have an associated meta value");
				}
			}
			$descriptor = new IntIdMetaItemDescriptor($id, $meta);
		}elseif($ingredient instanceof TagWildcardRecipeIngredient){
			$descriptor = new TagItemDescriptor($ingredient->getTagName());
		}else{
			throw new \LogicException("Unsupported recipe ingredient type " . get_class($ingredient) . ", only " . ExactRecipeIngredient::class . " and " . MetaWildcardRecipeIngredient::class . " are supported");
		}

		return new ProtocolRecipeIngredient($descriptor, 1);
	}

	public function netRecipeIngredientToCore(ProtocolRecipeIngredient $ingredient) : ?RecipeIngredient{
		$descriptor = $ingredient->getDescriptor();
		if($descriptor === null){
			return null;
		}

		if($descriptor instanceof TagItemDescriptor){
			return new TagWildcardRecipeIngredient($descriptor->getTag());
		}

		if($descriptor instanceof IntIdMetaItemDescriptor){
			$stringId = $this->itemTypeDictionary->fromIntId($descriptor->getId());
			$meta = $descriptor->getMeta();
		}elseif($descriptor instanceof StringIdMetaItemDescriptor){
			$stringId = $descriptor->getId();
			$meta = $descriptor->getMeta();
		}else{
			throw new \LogicException("Unsupported conversion of recipe ingredient to core item stack");
		}

		if($meta === self::RECIPE_INPUT_WILDCARD_META){
			return new MetaWildcardRecipeIngredient($stringId);
		}

		$blockRuntimeId = null;
		if(($blockId = $this->blockItemIdMap->lookupBlockId($stringId)) !== null){
			$blockRuntimeId = $this->blockTranslator->getBlockStateDictionary()->lookupStateIdFromIdMeta($blockId, $meta);
			if($blockRuntimeId !== null){
				$meta = 0;
			}
		}
		$result = $this->itemTranslator->fromNetworkId(
			$this->itemTypeDictionary->fromStringId($stringId),
			$meta,
			$blockRuntimeId ?? ItemTranslator::NO_BLOCK_RUNTIME_ID
		);
		return new ExactRecipeIngredient($result);
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

		$idMeta = $this->itemTranslator->toNetworkIdQuiet($itemStack);
		if($idMeta === null){
			//Display unmapped items as INFO_UPDATE, but stick something in their NBT to make sure they don't stack with
			//other unmapped items.
			[$id, $meta, $blockRuntimeId] = $this->itemTranslator->toNetworkId(VanillaBlocks::INFO_UPDATE()->asItem());
			if($nbt === null){
				$nbt = new CompoundTag();
			}
			$nbt->setLong(self::PM_ID_TAG, $itemStack->getStateId());
		}else{
			[$id, $meta, $blockRuntimeId] = $idMeta;
		}

		$extraData = $id === $this->shieldRuntimeId ?
			new ItemStackExtraDataShield($nbt, canPlaceOn: [], canDestroy: [], blockingTick: 0) :
			new ItemStackExtraData($nbt, canPlaceOn: [], canDestroy: []);
		$extraDataSerializer = PacketSerializer::encoder();
		$extraData->write($extraDataSerializer);

		return new ItemStack(
			$id,
			$meta,
			$itemStack->getCount(),
			$blockRuntimeId ?? ItemTranslator::NO_BLOCK_RUNTIME_ID,
			$extraDataSerializer->getBuffer(),
		);
	}

	/**
	 * WARNING: Avoid this in server-side code. If you need to compare ItemStacks provided by the client to the
	 * server, prefer encoding the server's itemstack and comparing the serialized ItemStack, instead of converting
	 * the client's ItemStack to a core Item.
	 * This method will fully decode the item's extra data, which can be very costly if the item has a lot of NBT data.
	 *
	 * @throws TypeConversionException
	 */
	public function netItemStackToCore(ItemStack $itemStack) : Item{
		if($itemStack->getId() === 0){
			return VanillaItems::AIR();
		}
		$extraData = $this->deserializeItemStackExtraData($itemStack->getRawExtraData(), $itemStack->getId());

		$compound = $extraData->getNbt();

		$itemResult = $this->itemTranslator->fromNetworkId($itemStack->getId(), $itemStack->getMeta(), $itemStack->getBlockRuntimeId());

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

	public function deserializeItemStackExtraData(string $extraData, int $id) : ItemStackExtraData{
		$extraDataDeserializer = PacketSerializer::decoder($extraData, 0);
		return $id === $this->shieldRuntimeId ?
			ItemStackExtraDataShield::read($extraDataDeserializer) :
			ItemStackExtraData::read($extraDataDeserializer);
	}
}
