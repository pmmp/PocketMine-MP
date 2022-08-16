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

namespace pocketmine\data\bedrock\item;

use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\ItemFrame;
use pocketmine\block\Skull;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\data\bedrock\block\BlockStateSerializeException;
use pocketmine\data\bedrock\block\BlockStateSerializer;
use pocketmine\data\bedrock\CompoundTypeIds;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\data\bedrock\item\ItemTypeNames as Ids;
use pocketmine\data\bedrock\item\SavedItemData as Data;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\SuspiciousStewTypeIdMap;
use pocketmine\item\Banner;
use pocketmine\item\CoralFan;
use pocketmine\item\Dye;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\Potion;
use pocketmine\item\SplashPotion;
use pocketmine\item\SuspiciousStew;
use pocketmine\item\VanillaItems as Items;
use pocketmine\utils\AssumptionFailedError;
use function class_parents;
use function get_class;

final class ItemSerializer{
	/**
	 * These callables actually accept Item, but for the sake of type completeness, it has to be never, since we can't
	 * describe the bottom type of a type hierarchy only containing Item.
	 *
	 * @var \Closure[][]
	 * @phpstan-var array<int, array<class-string, \Closure(never) : Data>>
	 */
	private array $itemSerializers = [];

	/**
	 * @var \Closure[][]
	 * @phpstan-var array<int, array<class-string, \Closure(never) : Data>>
	 */
	private array $blockItemSerializers = [];

	public function __construct(
		private BlockStateSerializer $blockStateSerializer
	){
		$this->registerSerializers();
	}

	/**
	 * @phpstan-template TItemType of Item
	 * @phpstan-param TItemType $item
	 * @phpstan-param \Closure(TItemType) : Data $serializer
	 */
	public function map(Item $item, \Closure $serializer) : void{
		$index = $item->getTypeId();
		if(isset($this->itemSerializers[$index])){
			//TODO: REMOVE ME
			throw new AssumptionFailedError("Registering the same item twice!");
		}
		$this->itemSerializers[$index][get_class($item)] = $serializer;
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $block
	 * @phpstan-param \Closure(TBlockType) : Data $serializer
	 */
	public function mapBlock(Block $block, \Closure $serializer) : void{
		$index = $block->getTypeId();
		if(isset($this->blockItemSerializers[$index])){
			throw new AssumptionFailedError("Registering the same blockitem twice!");
		}
		$this->blockItemSerializers[$index][get_class($block)] = $serializer;
	}

	/**
	 * @phpstan-template TItemType of Item
	 * @phpstan-param TItemType $item
	 *
	 * @throws ItemTypeSerializeException
	 */
	public function serializeType(Item $item) : Data{
		if($item->isNull()){
			throw new \InvalidArgumentException("Cannot serialize a null itemstack");
		}
		if($item instanceof ItemBlock){
			$data = $this->serializeBlockItem($item->getBlock());
		}else{
			$index = $item->getTypeId();

			$locatedSerializer = $this->itemSerializers[$index][get_class($item)] ?? null;
			if($locatedSerializer === null){
				$parents = class_parents($item);
				if($parents !== false){
					foreach($parents as $parent){
						if(isset($this->itemSerializers[$index][$parent])){
							$locatedSerializer = $this->itemSerializers[$index][$parent];
							break;
						}
					}
				}
			}

			if($locatedSerializer === null){
				throw new ItemTypeSerializeException("No serializer registered for " . get_class($item) . " ($index) " . $item->getName());
			}

			/**
			 * @var \Closure $serializer
			 * @phpstan-var \Closure(TItemType) : Data $serializer
			 */
			$serializer = $locatedSerializer;

			/** @var Data $data */
			$data = $serializer($item);
		}

		if($item->hasNamedTag()){
			$resultTag = $item->getNamedTag();
			$extraTag = $data->getTag();
			if($extraTag !== null){
				$resultTag = $resultTag->merge($extraTag);
			}
			$data = new Data($data->getName(), $data->getMeta(), $data->getBlock(), $resultTag);
		}

		return $data;
	}

	public function serializeStack(Item $item, ?int $slot = null) : SavedItemStackData{
		return new SavedItemStackData(
			$this->serializeType($item),
			$item->getCount(),
			$slot,
			null,
			[], //we currently represent canDestroy and canPlaceOn via NBT, like PC
			[]
		);
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $block
	 *
	 * @throws ItemTypeSerializeException
	 */
	private function serializeBlockItem(Block $block) : Data{
		$index = $block->getTypeId();

		$locatedSerializer = $this->blockItemSerializers[$index][get_class($block)] ?? null;
		if($locatedSerializer === null){
			$parents = class_parents($block);
			if($parents !== false){
				foreach($parents as $parent){
					if(isset($this->blockItemSerializers[$index][$parent])){
						$locatedSerializer = $this->blockItemSerializers[$index][$parent];
						break;
					}
				}
			}
		}

		if($locatedSerializer !== null){
			/** @phpstan-var \Closure(TBlockType) : Data $serializer */
			$serializer = $locatedSerializer;
			$data = $serializer($block);
		}else{
			$data = $this->standardBlock($block);
		}

		return $data;
	}

	/**
	 * @throws ItemTypeSerializeException
	 */
	private function standardBlock(Block $block) : Data{
		try{
			$blockStateData = $this->blockStateSerializer->serialize($block->getStateId());
		}catch(BlockStateSerializeException $e){
			throw new ItemTypeSerializeException($e->getMessage(), 0, $e);
		}

		$itemNameId = BlockItemIdMap::getInstance()->lookupItemId($blockStateData->getName()) ?? $blockStateData->getName();

		return new Data($itemNameId, 0, $blockStateData);
	}

	/**
	 * @phpstan-return \Closure() : Data
	 */
	private static function id(string $id) : \Closure{
		return fn() => new Data($id);
	}

	/**
	 * @phpstan-return \Closure() : Data
	 */
	private static function chemical(int $type) : \Closure{
		return fn() => new Data(Ids::COMPOUND, $type);
	}

	private function registerSpecialBlockSerializers() : void{
		$this->mapBlock(Blocks::ACACIA_DOOR(), self::id(Ids::ACACIA_DOOR));
		$this->mapBlock(Blocks::BIRCH_DOOR(), self::id(Ids::BIRCH_DOOR));
		$this->mapBlock(Blocks::BREWING_STAND(), self::id(Ids::BREWING_STAND));
		$this->mapBlock(Blocks::CAKE(), self::id(Ids::CAKE));
		$this->mapBlock(Blocks::CAULDRON(), self::id(Ids::CAULDRON));
		$this->mapBlock(Blocks::CRIMSON_DOOR(), self::id(Ids::CRIMSON_DOOR));
		$this->mapBlock(Blocks::DARK_OAK_DOOR(), self::id(Ids::DARK_OAK_DOOR));
		$this->mapBlock(Blocks::FLOWER_POT(), self::id(Ids::FLOWER_POT));
		$this->mapBlock(Blocks::HOPPER(), self::id(Ids::HOPPER));
		$this->mapBlock(Blocks::IRON_DOOR(), self::id(Ids::IRON_DOOR));
		$this->mapBlock(Blocks::ITEM_FRAME(), fn(ItemFrame $block) => new Data($block->isGlowing() ? Ids::GLOW_FRAME : Ids::FRAME));
		$this->mapBlock(Blocks::JUNGLE_DOOR(), self::id(Ids::JUNGLE_DOOR));
		$this->mapBlock(Blocks::MANGROVE_DOOR(), self::id(Ids::MANGROVE_DOOR));
		$this->mapBlock(Blocks::NETHER_WART(), self::id(Ids::NETHER_WART));
		$this->mapBlock(Blocks::OAK_DOOR(), self::id(Ids::WOODEN_DOOR));
		$this->mapBlock(Blocks::REDSTONE_COMPARATOR(), self::id(Ids::COMPARATOR));
		$this->mapBlock(Blocks::REDSTONE_REPEATER(), self::id(Ids::REPEATER));
		$this->mapBlock(Blocks::SPRUCE_DOOR(), self::id(Ids::SPRUCE_DOOR));
		$this->mapBlock(Blocks::SUGARCANE(), self::id(Ids::SUGAR_CANE));
		$this->mapBlock(Blocks::WARPED_DOOR(), self::id(Ids::WARPED_DOOR));

		$this->mapBlock(Blocks::BED(), fn(Bed $block) => new Data(Ids::BED, DyeColorIdMap::getInstance()->toId($block->getColor())));
		$this->mapBlock(Blocks::MOB_HEAD(), fn(Skull $block) => new Data(Ids::SKULL, $block->getSkullType()->getMagicNumber()));
	}

	private function registerSerializers() : void{
		$this->registerSpecialBlockSerializers();

		//these are encoded as regular blocks, but they have to be accounted for explicitly since they don't use ItemBlock
		//Bamboo->getBlock() returns BambooSapling :(
		$this->map(Items::BAMBOO(), fn() => $this->standardBlock(Blocks::BAMBOO()));
		$this->map(Items::CORAL_FAN(), fn(CoralFan $item) => $this->standardBlock($item->getBlock()));

		$this->map(Items::ACACIA_BOAT(), self::id(Ids::ACACIA_BOAT));
		$this->map(Items::ACACIA_SIGN(), self::id(Ids::ACACIA_SIGN));
		$this->map(Items::AMETHYST_SHARD(), self::id(Ids::AMETHYST_SHARD));
		$this->map(Items::APPLE(), self::id(Ids::APPLE));
		$this->map(Items::ARROW(), self::id(Ids::ARROW));
		$this->map(Items::BAKED_POTATO(), self::id(Ids::BAKED_POTATO));
		$this->map(Items::BANNER(), fn(Banner $item) => new Data(Ids::BANNER, DyeColorIdMap::getInstance()->toInvertedId($item->getColor())));
		$this->map(Items::BEETROOT(), self::id(Ids::BEETROOT));
		$this->map(Items::BEETROOT_SEEDS(), self::id(Ids::BEETROOT_SEEDS));
		$this->map(Items::BEETROOT_SOUP(), self::id(Ids::BEETROOT_SOUP));
		$this->map(Items::BIRCH_BOAT(), self::id(Ids::BIRCH_BOAT));
		$this->map(Items::BIRCH_SIGN(), self::id(Ids::BIRCH_SIGN));
		$this->map(Items::BLAZE_POWDER(), self::id(Ids::BLAZE_POWDER));
		$this->map(Items::BLAZE_ROD(), self::id(Ids::BLAZE_ROD));
		$this->map(Items::BLEACH(), self::id(Ids::BLEACH));
		$this->map(Items::BONE(), self::id(Ids::BONE));
		$this->map(Items::BONE_MEAL(), self::id(Ids::BONE_MEAL));
		$this->map(Items::BOOK(), self::id(Ids::BOOK));
		$this->map(Items::BOW(), self::id(Ids::BOW));
		$this->map(Items::BOWL(), self::id(Ids::BOWL));
		$this->map(Items::BREAD(), self::id(Ids::BREAD));
		$this->map(Items::BRICK(), self::id(Ids::BRICK));
		$this->map(Items::BUCKET(), self::id(Ids::BUCKET));
		$this->map(Items::CARROT(), self::id(Ids::CARROT));
		$this->map(Items::CHAINMAIL_BOOTS(), self::id(Ids::CHAINMAIL_BOOTS));
		$this->map(Items::CHAINMAIL_CHESTPLATE(), self::id(Ids::CHAINMAIL_CHESTPLATE));
		$this->map(Items::CHAINMAIL_HELMET(), self::id(Ids::CHAINMAIL_HELMET));
		$this->map(Items::CHAINMAIL_LEGGINGS(), self::id(Ids::CHAINMAIL_LEGGINGS));
		$this->map(Items::CHARCOAL(), self::id(Ids::CHARCOAL));
		$this->map(Items::CHEMICAL_ALUMINIUM_OXIDE(), self::chemical(CompoundTypeIds::ALUMINIUM_OXIDE));
		$this->map(Items::CHEMICAL_AMMONIA(), self::chemical(CompoundTypeIds::AMMONIA));
		$this->map(Items::CHEMICAL_BARIUM_SULPHATE(), self::chemical(CompoundTypeIds::BARIUM_SULPHATE));
		$this->map(Items::CHEMICAL_BENZENE(), self::chemical(CompoundTypeIds::BENZENE));
		$this->map(Items::CHEMICAL_BORON_TRIOXIDE(), self::chemical(CompoundTypeIds::BORON_TRIOXIDE));
		$this->map(Items::CHEMICAL_CALCIUM_BROMIDE(), self::chemical(CompoundTypeIds::CALCIUM_BROMIDE));
		$this->map(Items::CHEMICAL_CALCIUM_CHLORIDE(), self::chemical(CompoundTypeIds::CALCIUM_CHLORIDE));
		$this->map(Items::CHEMICAL_CERIUM_CHLORIDE(), self::chemical(CompoundTypeIds::CERIUM_CHLORIDE));
		$this->map(Items::CHEMICAL_CHARCOAL(), self::chemical(CompoundTypeIds::CHARCOAL));
		$this->map(Items::CHEMICAL_CRUDE_OIL(), self::chemical(CompoundTypeIds::CRUDE_OIL));
		$this->map(Items::CHEMICAL_GLUE(), self::chemical(CompoundTypeIds::GLUE));
		$this->map(Items::CHEMICAL_HYDROGEN_PEROXIDE(), self::chemical(CompoundTypeIds::HYDROGEN_PEROXIDE));
		$this->map(Items::CHEMICAL_HYPOCHLORITE(), self::chemical(CompoundTypeIds::HYPOCHLORITE));
		$this->map(Items::CHEMICAL_INK(), self::chemical(CompoundTypeIds::INK));
		$this->map(Items::CHEMICAL_IRON_SULPHIDE(), self::chemical(CompoundTypeIds::IRON_SULPHIDE));
		$this->map(Items::CHEMICAL_LATEX(), self::chemical(CompoundTypeIds::LATEX));
		$this->map(Items::CHEMICAL_LITHIUM_HYDRIDE(), self::chemical(CompoundTypeIds::LITHIUM_HYDRIDE));
		$this->map(Items::CHEMICAL_LUMINOL(), self::chemical(CompoundTypeIds::LUMINOL));
		$this->map(Items::CHEMICAL_MAGNESIUM_NITRATE(), self::chemical(CompoundTypeIds::MAGNESIUM_NITRATE));
		$this->map(Items::CHEMICAL_MAGNESIUM_OXIDE(), self::chemical(CompoundTypeIds::MAGNESIUM_OXIDE));
		$this->map(Items::CHEMICAL_MAGNESIUM_SALTS(), self::chemical(CompoundTypeIds::MAGNESIUM_SALTS));
		$this->map(Items::CHEMICAL_MERCURIC_CHLORIDE(), self::chemical(CompoundTypeIds::MERCURIC_CHLORIDE));
		$this->map(Items::CHEMICAL_POLYETHYLENE(), self::chemical(CompoundTypeIds::POLYETHYLENE));
		$this->map(Items::CHEMICAL_POTASSIUM_CHLORIDE(), self::chemical(CompoundTypeIds::POTASSIUM_CHLORIDE));
		$this->map(Items::CHEMICAL_POTASSIUM_IODIDE(), self::chemical(CompoundTypeIds::POTASSIUM_IODIDE));
		$this->map(Items::CHEMICAL_RUBBISH(), self::chemical(CompoundTypeIds::RUBBISH));
		$this->map(Items::CHEMICAL_SALT(), self::chemical(CompoundTypeIds::SALT));
		$this->map(Items::CHEMICAL_SOAP(), self::chemical(CompoundTypeIds::SOAP));
		$this->map(Items::CHEMICAL_SODIUM_ACETATE(), self::chemical(CompoundTypeIds::SODIUM_ACETATE));
		$this->map(Items::CHEMICAL_SODIUM_FLUORIDE(), self::chemical(CompoundTypeIds::SODIUM_FLUORIDE));
		$this->map(Items::CHEMICAL_SODIUM_HYDRIDE(), self::chemical(CompoundTypeIds::SODIUM_HYDRIDE));
		$this->map(Items::CHEMICAL_SODIUM_HYDROXIDE(), self::chemical(CompoundTypeIds::SODIUM_HYDROXIDE));
		$this->map(Items::CHEMICAL_SODIUM_HYPOCHLORITE(), self::chemical(CompoundTypeIds::SODIUM_HYPOCHLORITE));
		$this->map(Items::CHEMICAL_SODIUM_OXIDE(), self::chemical(CompoundTypeIds::SODIUM_OXIDE));
		$this->map(Items::CHEMICAL_SUGAR(), self::chemical(CompoundTypeIds::SUGAR));
		$this->map(Items::CHEMICAL_SULPHATE(), self::chemical(CompoundTypeIds::SULPHATE));
		$this->map(Items::CHEMICAL_TUNGSTEN_CHLORIDE(), self::chemical(CompoundTypeIds::TUNGSTEN_CHLORIDE));
		$this->map(Items::CHEMICAL_WATER(), self::chemical(CompoundTypeIds::WATER));
		$this->map(Items::CHORUS_FRUIT(), self::id(Ids::CHORUS_FRUIT));
		$this->map(Items::CLAY(), self::id(Ids::CLAY_BALL));
		$this->map(Items::CLOCK(), self::id(Ids::CLOCK));
		$this->map(Items::CLOWNFISH(), self::id(Ids::TROPICAL_FISH));
		$this->map(Items::COAL(), self::id(Ids::COAL));
		$this->map(Items::COCOA_BEANS(), self::id(Ids::COCOA_BEANS));
		$this->map(Items::COMPASS(), self::id(Ids::COMPASS));
		$this->map(Items::COOKED_CHICKEN(), self::id(Ids::COOKED_CHICKEN));
		$this->map(Items::COOKED_FISH(), self::id(Ids::COOKED_COD));
		$this->map(Items::COOKED_MUTTON(), self::id(Ids::COOKED_MUTTON));
		$this->map(Items::COOKED_PORKCHOP(), self::id(Ids::COOKED_PORKCHOP));
		$this->map(Items::COOKED_RABBIT(), self::id(Ids::COOKED_RABBIT));
		$this->map(Items::COOKED_SALMON(), self::id(Ids::COOKED_SALMON));
		$this->map(Items::COOKIE(), self::id(Ids::COOKIE));
		$this->map(Items::COPPER_INGOT(), self::id(Ids::COPPER_INGOT));
		$this->map(Items::CRIMSON_SIGN(), self::id(Ids::CRIMSON_SIGN));
		$this->map(Items::DARK_OAK_BOAT(), self::id(Ids::DARK_OAK_BOAT));
		$this->map(Items::DARK_OAK_SIGN(), self::id(Ids::DARK_OAK_SIGN));
		$this->map(Items::DIAMOND(), self::id(Ids::DIAMOND));
		$this->map(Items::DIAMOND_AXE(), self::id(Ids::DIAMOND_AXE));
		$this->map(Items::DIAMOND_BOOTS(), self::id(Ids::DIAMOND_BOOTS));
		$this->map(Items::DIAMOND_CHESTPLATE(), self::id(Ids::DIAMOND_CHESTPLATE));
		$this->map(Items::DIAMOND_HELMET(), self::id(Ids::DIAMOND_HELMET));
		$this->map(Items::DIAMOND_HOE(), self::id(Ids::DIAMOND_HOE));
		$this->map(Items::DIAMOND_LEGGINGS(), self::id(Ids::DIAMOND_LEGGINGS));
		$this->map(Items::DIAMOND_PICKAXE(), self::id(Ids::DIAMOND_PICKAXE));
		$this->map(Items::DIAMOND_SHOVEL(), self::id(Ids::DIAMOND_SHOVEL));
		$this->map(Items::DIAMOND_SWORD(), self::id(Ids::DIAMOND_SWORD));
		$this->map(Items::DISC_FRAGMENT_5(), self::id(Ids::DISC_FRAGMENT_5));
		$this->map(Items::DRAGON_BREATH(), self::id(Ids::DRAGON_BREATH));
		$this->map(Items::DRIED_KELP(), self::id(Ids::DRIED_KELP));
		$this->map(Items::DYE(), fn(Dye $item) => new Data(match($item->getColor()){
			DyeColor::BLACK() => Ids::BLACK_DYE,
			DyeColor::BLUE() => Ids::BLUE_DYE,
			DyeColor::BROWN() => Ids::BROWN_DYE,
			DyeColor::CYAN() => Ids::CYAN_DYE,
			DyeColor::GRAY() => Ids::GRAY_DYE,
			DyeColor::GREEN() => Ids::GREEN_DYE,
			DyeColor::LIGHT_BLUE() => Ids::LIGHT_BLUE_DYE,
			DyeColor::LIGHT_GRAY() => Ids::LIGHT_GRAY_DYE,
			DyeColor::LIME() => Ids::LIME_DYE,
			DyeColor::MAGENTA() => Ids::MAGENTA_DYE,
			DyeColor::ORANGE() => Ids::ORANGE_DYE,
			DyeColor::PINK() => Ids::PINK_DYE,
			DyeColor::PURPLE() => Ids::PURPLE_DYE,
			DyeColor::RED() => Ids::RED_DYE,
			DyeColor::WHITE() => Ids::WHITE_DYE,
			DyeColor::YELLOW() => Ids::YELLOW_DYE,
			default => throw new AssumptionFailedError("Unhandled dye color " . $item->getColor()->name()),
		}));
		$this->map(Items::ECHO_SHARD(), self::id(Ids::ECHO_SHARD));
		$this->map(Items::EGG(), self::id(Ids::EGG));
		$this->map(Items::EMERALD(), self::id(Ids::EMERALD));
		$this->map(Items::ENCHANTED_GOLDEN_APPLE(), self::id(Ids::ENCHANTED_GOLDEN_APPLE));
		$this->map(Items::ENDER_PEARL(), self::id(Ids::ENDER_PEARL));
		$this->map(Items::EXPERIENCE_BOTTLE(), self::id(Ids::EXPERIENCE_BOTTLE));
		$this->map(Items::FEATHER(), self::id(Ids::FEATHER));
		$this->map(Items::FERMENTED_SPIDER_EYE(), self::id(Ids::FERMENTED_SPIDER_EYE));
		$this->map(Items::FIRE_CHARGE(), self::id(Ids::FIRE_CHARGE));
		$this->map(Items::FISHING_ROD(), self::id(Ids::FISHING_ROD));
		$this->map(Items::FLINT(), self::id(Ids::FLINT));
		$this->map(Items::FLINT_AND_STEEL(), self::id(Ids::FLINT_AND_STEEL));
		$this->map(Items::GHAST_TEAR(), self::id(Ids::GHAST_TEAR));
		$this->map(Items::GLASS_BOTTLE(), self::id(Ids::GLASS_BOTTLE));
		$this->map(Items::GLISTERING_MELON(), self::id(Ids::GLISTERING_MELON_SLICE));
		$this->map(Items::GLOWSTONE_DUST(), self::id(Ids::GLOWSTONE_DUST));
		$this->map(Items::GLOW_INK_SAC(), self::id(Ids::GLOW_INK_SAC));
		$this->map(Items::GOLDEN_APPLE(), self::id(Ids::GOLDEN_APPLE));
		$this->map(Items::GOLDEN_AXE(), self::id(Ids::GOLDEN_AXE));
		$this->map(Items::GOLDEN_BOOTS(), self::id(Ids::GOLDEN_BOOTS));
		$this->map(Items::GOLDEN_CARROT(), self::id(Ids::GOLDEN_CARROT));
		$this->map(Items::GOLDEN_CHESTPLATE(), self::id(Ids::GOLDEN_CHESTPLATE));
		$this->map(Items::GOLDEN_HELMET(), self::id(Ids::GOLDEN_HELMET));
		$this->map(Items::GOLDEN_HOE(), self::id(Ids::GOLDEN_HOE));
		$this->map(Items::GOLDEN_LEGGINGS(), self::id(Ids::GOLDEN_LEGGINGS));
		$this->map(Items::GOLDEN_PICKAXE(), self::id(Ids::GOLDEN_PICKAXE));
		$this->map(Items::GOLDEN_SHOVEL(), self::id(Ids::GOLDEN_SHOVEL));
		$this->map(Items::GOLDEN_SWORD(), self::id(Ids::GOLDEN_SWORD));
		$this->map(Items::GOLD_INGOT(), self::id(Ids::GOLD_INGOT));
		$this->map(Items::GOLD_NUGGET(), self::id(Ids::GOLD_NUGGET));
		$this->map(Items::GUNPOWDER(), self::id(Ids::GUNPOWDER));
		$this->map(Items::HEART_OF_THE_SEA(), self::id(Ids::HEART_OF_THE_SEA));
		$this->map(Items::HONEYCOMB(), self::id(Ids::HONEYCOMB));
		$this->map(Items::HONEY_BOTTLE(), self::id(Ids::HONEY_BOTTLE));
		$this->map(Items::INK_SAC(), self::id(Ids::INK_SAC));
		$this->map(Items::IRON_AXE(), self::id(Ids::IRON_AXE));
		$this->map(Items::IRON_BOOTS(), self::id(Ids::IRON_BOOTS));
		$this->map(Items::IRON_CHESTPLATE(), self::id(Ids::IRON_CHESTPLATE));
		$this->map(Items::IRON_HELMET(), self::id(Ids::IRON_HELMET));
		$this->map(Items::IRON_HOE(), self::id(Ids::IRON_HOE));
		$this->map(Items::IRON_INGOT(), self::id(Ids::IRON_INGOT));
		$this->map(Items::IRON_LEGGINGS(), self::id(Ids::IRON_LEGGINGS));
		$this->map(Items::IRON_NUGGET(), self::id(Ids::IRON_NUGGET));
		$this->map(Items::IRON_PICKAXE(), self::id(Ids::IRON_PICKAXE));
		$this->map(Items::IRON_SHOVEL(), self::id(Ids::IRON_SHOVEL));
		$this->map(Items::IRON_SWORD(), self::id(Ids::IRON_SWORD));
		$this->map(Items::JUNGLE_BOAT(), self::id(Ids::JUNGLE_BOAT));
		$this->map(Items::JUNGLE_SIGN(), self::id(Ids::JUNGLE_SIGN));
		$this->map(Items::LAPIS_LAZULI(), self::id(Ids::LAPIS_LAZULI));
		$this->map(Items::LAVA_BUCKET(), self::id(Ids::LAVA_BUCKET));
		$this->map(Items::LEATHER(), self::id(Ids::LEATHER));
		$this->map(Items::LEATHER_BOOTS(), self::id(Ids::LEATHER_BOOTS));
		$this->map(Items::LEATHER_CAP(), self::id(Ids::LEATHER_HELMET));
		$this->map(Items::LEATHER_PANTS(), self::id(Ids::LEATHER_LEGGINGS));
		$this->map(Items::LEATHER_TUNIC(), self::id(Ids::LEATHER_CHESTPLATE));
		$this->map(Items::MAGMA_CREAM(), self::id(Ids::MAGMA_CREAM));
		$this->map(Items::MANGROVE_SIGN(), self::id(Ids::MANGROVE_SIGN));
		$this->map(Items::MELON(), self::id(Ids::MELON_SLICE));
		$this->map(Items::MELON_SEEDS(), self::id(Ids::MELON_SEEDS));
		$this->map(Items::MILK_BUCKET(), self::id(Ids::MILK_BUCKET));
		$this->map(Items::MINECART(), self::id(Ids::MINECART));
		$this->map(Items::MUSHROOM_STEW(), self::id(Ids::MUSHROOM_STEW));
		$this->map(Items::NAUTILUS_SHELL(), self::id(Ids::NAUTILUS_SHELL));
		$this->map(Items::NETHERITE_AXE(), self::id(Ids::NETHERITE_AXE));
		$this->map(Items::NETHERITE_BOOTS(), self::id(Ids::NETHERITE_BOOTS));
		$this->map(Items::NETHERITE_CHESTPLATE(), self::id(Ids::NETHERITE_CHESTPLATE));
		$this->map(Items::NETHERITE_HELMET(), self::id(Ids::NETHERITE_HELMET));
		$this->map(Items::NETHERITE_HOE(), self::id(Ids::NETHERITE_HOE));
		$this->map(Items::NETHERITE_INGOT(), self::id(Ids::NETHERITE_INGOT));
		$this->map(Items::NETHERITE_LEGGINGS(), self::id(Ids::NETHERITE_LEGGINGS));
		$this->map(Items::NETHERITE_PICKAXE(), self::id(Ids::NETHERITE_PICKAXE));
		$this->map(Items::NETHERITE_SCRAP(), self::id(Ids::NETHERITE_SCRAP));
		$this->map(Items::NETHERITE_SHOVEL(), self::id(Ids::NETHERITE_SHOVEL));
		$this->map(Items::NETHERITE_SWORD(), self::id(Ids::NETHERITE_SWORD));
		$this->map(Items::NETHER_BRICK(), self::id(Ids::NETHERBRICK));
		$this->map(Items::NETHER_QUARTZ(), self::id(Ids::QUARTZ));
		$this->map(Items::NETHER_STAR(), self::id(Ids::NETHER_STAR));
		$this->map(Items::OAK_BOAT(), self::id(Ids::OAK_BOAT));
		$this->map(Items::OAK_SIGN(), self::id(Ids::OAK_SIGN));
		$this->map(Items::PAINTING(), self::id(Ids::PAINTING));
		$this->map(Items::PAPER(), self::id(Ids::PAPER));
		$this->map(Items::PHANTOM_MEMBRANE(), self::id(Ids::PHANTOM_MEMBRANE));
		$this->map(Items::POISONOUS_POTATO(), self::id(Ids::POISONOUS_POTATO));
		$this->map(Items::POPPED_CHORUS_FRUIT(), self::id(Ids::POPPED_CHORUS_FRUIT));
		$this->map(Items::POTATO(), self::id(Ids::POTATO));
		$this->map(Items::POTION(), fn(Potion $item) => new Data(Ids::POTION, PotionTypeIdMap::getInstance()->toId($item->getType())));
		$this->map(Items::PRISMARINE_CRYSTALS(), self::id(Ids::PRISMARINE_CRYSTALS));
		$this->map(Items::PRISMARINE_SHARD(), self::id(Ids::PRISMARINE_SHARD));
		$this->map(Items::PUFFERFISH(), self::id(Ids::PUFFERFISH));
		$this->map(Items::PUMPKIN_PIE(), self::id(Ids::PUMPKIN_PIE));
		$this->map(Items::PUMPKIN_SEEDS(), self::id(Ids::PUMPKIN_SEEDS));
		$this->map(Items::RABBIT_FOOT(), self::id(Ids::RABBIT_FOOT));
		$this->map(Items::RABBIT_HIDE(), self::id(Ids::RABBIT_HIDE));
		$this->map(Items::RABBIT_STEW(), self::id(Ids::RABBIT_STEW));
		$this->map(Items::RAW_BEEF(), self::id(Ids::BEEF));
		$this->map(Items::RAW_CHICKEN(), self::id(Ids::CHICKEN));
		$this->map(Items::RAW_COPPER(), self::id(Ids::RAW_COPPER));
		$this->map(Items::RAW_FISH(), self::id(Ids::COD));
		$this->map(Items::RAW_GOLD(), self::id(Ids::RAW_GOLD));
		$this->map(Items::RAW_IRON(), self::id(Ids::RAW_IRON));
		$this->map(Items::RAW_MUTTON(), self::id(Ids::MUTTON));
		$this->map(Items::RAW_PORKCHOP(), self::id(Ids::PORKCHOP));
		$this->map(Items::RAW_RABBIT(), self::id(Ids::RABBIT));
		$this->map(Items::RAW_SALMON(), self::id(Ids::SALMON));
		$this->map(Items::RECORD_11(), self::id(Ids::MUSIC_DISC_11));
		$this->map(Items::RECORD_13(), self::id(Ids::MUSIC_DISC_13));
		$this->map(Items::RECORD_BLOCKS(), self::id(Ids::MUSIC_DISC_BLOCKS));
		$this->map(Items::RECORD_CAT(), self::id(Ids::MUSIC_DISC_CAT));
		$this->map(Items::RECORD_CHIRP(), self::id(Ids::MUSIC_DISC_CHIRP));
		$this->map(Items::RECORD_FAR(), self::id(Ids::MUSIC_DISC_FAR));
		$this->map(Items::RECORD_MALL(), self::id(Ids::MUSIC_DISC_MALL));
		$this->map(Items::RECORD_MELLOHI(), self::id(Ids::MUSIC_DISC_MELLOHI));
		$this->map(Items::RECORD_STAL(), self::id(Ids::MUSIC_DISC_STAL));
		$this->map(Items::RECORD_STRAD(), self::id(Ids::MUSIC_DISC_STRAD));
		$this->map(Items::RECORD_WAIT(), self::id(Ids::MUSIC_DISC_WAIT));
		$this->map(Items::RECORD_WARD(), self::id(Ids::MUSIC_DISC_WARD));
		$this->map(Items::REDSTONE_DUST(), self::id(Ids::REDSTONE));
		$this->map(Items::ROTTEN_FLESH(), self::id(Ids::ROTTEN_FLESH));
		$this->map(Items::SCUTE(), self::id(Ids::SCUTE));
		$this->map(Items::SHEARS(), self::id(Ids::SHEARS));
		$this->map(Items::SHULKER_SHELL(), self::id(Ids::SHULKER_SHELL));
		$this->map(Items::SLIMEBALL(), self::id(Ids::SLIME_BALL));
		$this->map(Items::SNOWBALL(), self::id(Ids::SNOWBALL));
		$this->map(Items::SPIDER_EYE(), self::id(Ids::SPIDER_EYE));
		$this->map(Items::SPLASH_POTION(), fn(SplashPotion $item) => new Data(Ids::SPLASH_POTION, PotionTypeIdMap::getInstance()->toId($item->getType())));
		$this->map(Items::SPRUCE_BOAT(), self::id(Ids::SPRUCE_BOAT));
		$this->map(Items::SPRUCE_SIGN(), self::id(Ids::SPRUCE_SIGN));
		$this->map(Items::SPYGLASS(), self::id(Ids::SPYGLASS));
		$this->map(Items::SQUID_SPAWN_EGG(), self::id(Ids::SQUID_SPAWN_EGG));
		$this->map(Items::STEAK(), self::id(Ids::COOKED_BEEF));
		$this->map(Items::STICK(), self::id(Ids::STICK));
		$this->map(Items::STONE_AXE(), self::id(Ids::STONE_AXE));
		$this->map(Items::STONE_HOE(), self::id(Ids::STONE_HOE));
		$this->map(Items::STONE_PICKAXE(), self::id(Ids::STONE_PICKAXE));
		$this->map(Items::STONE_SHOVEL(), self::id(Ids::STONE_SHOVEL));
		$this->map(Items::STONE_SWORD(), self::id(Ids::STONE_SWORD));
		$this->map(Items::STRING(), self::id(Ids::STRING));
		$this->map(Items::SUGAR(), self::id(Ids::SUGAR));
		$this->map(Items::SUSPICIOUS_STEW(), fn(SuspiciousStew $item) => new Data(Ids::SUSPICIOUS_STEW, SuspiciousStewTypeIdMap::getInstance()->toId($item->getType())));
		$this->map(Items::SWEET_BERRIES(), self::id(Ids::SWEET_BERRIES));
		$this->map(Items::TOTEM(), self::id(Ids::TOTEM_OF_UNDYING));
		$this->map(Items::VILLAGER_SPAWN_EGG(), self::id(Ids::VILLAGER_SPAWN_EGG));
		$this->map(Items::WARPED_SIGN(), self::id(Ids::WARPED_SIGN));
		$this->map(Items::WATER_BUCKET(), self::id(Ids::WATER_BUCKET));
		$this->map(Items::WHEAT(), self::id(Ids::WHEAT));
		$this->map(Items::WHEAT_SEEDS(), self::id(Ids::WHEAT_SEEDS));
		$this->map(Items::WOODEN_AXE(), self::id(Ids::WOODEN_AXE));
		$this->map(Items::WOODEN_HOE(), self::id(Ids::WOODEN_HOE));
		$this->map(Items::WOODEN_PICKAXE(), self::id(Ids::WOODEN_PICKAXE));
		$this->map(Items::WOODEN_SHOVEL(), self::id(Ids::WOODEN_SHOVEL));
		$this->map(Items::WOODEN_SWORD(), self::id(Ids::WOODEN_SWORD));
		$this->map(Items::WRITABLE_BOOK(), self::id(Ids::WRITABLE_BOOK));
		$this->map(Items::WRITTEN_BOOK(), self::id(Ids::WRITTEN_BOOK));
		$this->map(Items::ZOMBIE_SPAWN_EGG(), self::id(Ids::ZOMBIE_SPAWN_EGG));
	}
}
