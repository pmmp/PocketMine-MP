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

namespace pocketmine\data\bedrock\block\convert;

use pocketmine\block\ActivatorRail;
use pocketmine\block\BambooSapling;
use pocketmine\block\Bedrock;
use pocketmine\block\Block;
use pocketmine\block\Cactus;
use pocketmine\block\Froglight;
use pocketmine\block\MobHead;
use pocketmine\block\NetherVines;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\Colored;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\block\WeightedPressurePlate;
use pocketmine\block\Wood;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateReader as Reader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use pocketmine\data\bedrock\block\convert\property\BlockDataModel;

/**
 * Registers serializers and deserializers for block data in a unified style, to avoid code duplication.
 * Not all blocks can be registered this way, but we can avoid a lot of repetition for the ones that can.
 */
final class BlockSerializerDeserializerRegistrar{

	public function __construct(
		private ?BlockStateToObjectDeserializer $deserializer,
		private ?BlockObjectToStateSerializer $serializer
	){
		$this->registerSimpleIdOnlyMappings();
		$this->registerColoredIdOnlyMappings();
		$this->registerLeavesMappings();
		$this->registerSaplingMappings();
		$this->registerFlattenedEnumMappings();
		$this->registerStoneLikeSlabMappings();
		$this->registerStoneLikeStairMappings();
		$this->registerStoneLikeWallMappings();
		$this->registerWoodMappings();
		$this->register1to1CustomMappings();
	}

	private function mapSimple(Block $block, string $id) : void{
		$this->deserializer?->mapSimple($id, fn() => clone $block);
		$this->serializer?->mapSimple($block, $id);
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-template TEnum of \UnitEnum
	 *
	 * @phpstan-param TBlock                            $block
	 * @phpstan-param class-string<TEnum>               $enumClass
	 * @phpstan-param \Closure(TBlock) : TEnum          $getProperty
	 * @phpstan-param \Closure(TBlock, TEnum) : TBlock  $setProperty
	 * @phpstan-param \Closure(TBlock, Reader) : TBlock $readExtra
	 * @phpstan-param \Closure(TBlock, Writer) : Writer $writeExtra
	 */
	private function mapFlattenedIdEnumWithExtra(
		Block $block,
		string $enumClass,
		string $prefix,
		string $suffix,
		\Closure $getProperty,
		\Closure $setProperty,
		\Closure $readExtra,
		\Closure $writeExtra
	) : void{
		$mapProperty = ValueMappings::getInstance()->getEnumMap($enumClass);
		$this->deserializer?->mapFlattenedEnum(
			$mapProperty,
			$prefix,
			$suffix,
			fn(\UnitEnum $value) => $setProperty(clone $block, $value),
			$readExtra
		);
		$this->serializer?->mapFlattenedEnum(
			$block,
			$mapProperty,
			$prefix,
			$suffix,
			$getProperty,
			$writeExtra
		);
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-template TEnum of \UnitEnum
	 *
	 * @phpstan-param TBlock                            $block
	 * @phpstan-param class-string<TEnum>               $enumClass
	 * @phpstan-param \Closure(TBlock) : TEnum          $getProperty
	 * @phpstan-param \Closure(TBlock, TEnum) : TBlock  $setProperty
	 * @phpstan-param \Closure(TBlock, Reader) : TBlock $readExtra
	 * @phpstan-param \Closure(TBlock, Writer) : Writer $writeExtra
	 */
	private function mapSwitchedIdEnumWithExtra(
		Block $block,
		string $enumClass,
		\Closure $getProperty,
		\Closure $setProperty,
		\Closure $readExtra,
		\Closure $writeExtra
	) : void{
		$this->mapFlattenedIdEnumWithExtra($block, $enumClass, "", "", $getProperty, $setProperty, $readExtra, $writeExtra);
	}

	/**
	 * @phpstan-template TBlock of Block&Colored
	 * @phpstan-param TBlock $block
	 */
	private function mapColored(Block $block, string $idPrefix, string $idSuffix) : void{
		$this->deserializer?->mapColored($idPrefix, $idSuffix, fn() => clone $block);
		$this->serializer?->mapColored($block, $idPrefix, $idSuffix);
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-param TBlock                            $block
	 * @phpstan-param \Closure(TBlock, Reader) : TBlock $readHelper
	 * @phpstan-param \Closure(TBlock, Writer) : Writer $writeHelper
	 */
	private function mapStdHelper(Block $block, string $id, \Closure $readHelper, \Closure $writeHelper) : void{
		$this->deserializer?->map($id, fn(Reader $in) => $readHelper(clone $block, $in));
		$this->serializer?->map($block, fn(Block $block) => $writeHelper($block, new Writer($id)));
	}

	private function mapSlab(Slab $block, string $singleId, string $doubleId) : void{
		$this->deserializer?->mapSlab($singleId, $doubleId, fn() => clone $block);
		$this->serializer?->mapSlab($block, $singleId, $doubleId);
	}

	private function mapStairs(Stair $block, string $id) : void{
		$this->deserializer?->mapStairs($id, fn() => clone $block);
		$this->serializer?->mapStairs($block, $id);
	}

	private function mapLog(Wood $block, string $unstrippedId, string $strippedId) : void{
		$this->deserializer?->mapLog($unstrippedId, $strippedId, fn() => clone $block);
		$this->serializer?->mapLog($block, $unstrippedId, $strippedId);
	}

	private function registerSimpleIdOnlyMappings() : void{
		$this->mapSimple(Blocks::AIR(), Ids::AIR);
		$this->mapSimple(Blocks::AMETHYST(), Ids::AMETHYST_BLOCK);
		$this->mapSimple(Blocks::ANCIENT_DEBRIS(), Ids::ANCIENT_DEBRIS);
		$this->mapSimple(Blocks::ANDESITE(), Ids::ANDESITE);
		$this->mapSimple(Blocks::BARRIER(), Ids::BARRIER);
		$this->mapSimple(Blocks::BEACON(), Ids::BEACON);
		$this->mapSimple(Blocks::BLACKSTONE(), Ids::BLACKSTONE);
		$this->mapSimple(Blocks::BLUE_ICE(), Ids::BLUE_ICE);
		$this->mapSimple(Blocks::BOOKSHELF(), Ids::BOOKSHELF);
		$this->mapSimple(Blocks::BRICKS(), Ids::BRICK_BLOCK);
		$this->mapSimple(Blocks::BROWN_MUSHROOM(), Ids::BROWN_MUSHROOM);
		$this->mapSimple(Blocks::BUDDING_AMETHYST(), Ids::BUDDING_AMETHYST);
		$this->mapSimple(Blocks::CALCITE(), Ids::CALCITE);
		$this->mapSimple(Blocks::CARTOGRAPHY_TABLE(), Ids::CARTOGRAPHY_TABLE);
		$this->mapSimple(Blocks::CHEMICAL_HEAT(), Ids::CHEMICAL_HEAT);
		$this->mapSimple(Blocks::CHISELED_DEEPSLATE(), Ids::CHISELED_DEEPSLATE);
		$this->mapSimple(Blocks::CHISELED_NETHER_BRICKS(), Ids::CHISELED_NETHER_BRICKS);
		$this->mapSimple(Blocks::CHISELED_POLISHED_BLACKSTONE(), Ids::CHISELED_POLISHED_BLACKSTONE);
		$this->mapSimple(Blocks::CHISELED_RED_SANDSTONE(), Ids::CHISELED_RED_SANDSTONE);
		$this->mapSimple(Blocks::CHISELED_RESIN_BRICKS(), Ids::CHISELED_RESIN_BRICKS);
		$this->mapSimple(Blocks::CHISELED_SANDSTONE(), Ids::CHISELED_SANDSTONE);
		$this->mapSimple(Blocks::CHISELED_STONE_BRICKS(), Ids::CHISELED_STONE_BRICKS);
		$this->mapSimple(Blocks::CHISELED_TUFF(), Ids::CHISELED_TUFF);
		$this->mapSimple(Blocks::CHISELED_TUFF_BRICKS(), Ids::CHISELED_TUFF_BRICKS);
		$this->mapSimple(Blocks::CHORUS_PLANT(), Ids::CHORUS_PLANT);
		$this->mapSimple(Blocks::CLAY(), Ids::CLAY);
		$this->mapSimple(Blocks::COAL(), Ids::COAL_BLOCK);
		$this->mapSimple(Blocks::COAL_ORE(), Ids::COAL_ORE);
		$this->mapSimple(Blocks::COBBLED_DEEPSLATE(), Ids::COBBLED_DEEPSLATE);
		$this->mapSimple(Blocks::COBBLESTONE(), Ids::COBBLESTONE);
		$this->mapSimple(Blocks::COBWEB(), Ids::WEB);
		$this->mapSimple(Blocks::COPPER_ORE(), Ids::COPPER_ORE);
		$this->mapSimple(Blocks::CRACKED_DEEPSLATE_BRICKS(), Ids::CRACKED_DEEPSLATE_BRICKS);
		$this->mapSimple(Blocks::CRACKED_DEEPSLATE_TILES(), Ids::CRACKED_DEEPSLATE_TILES);
		$this->mapSimple(Blocks::CRACKED_NETHER_BRICKS(), Ids::CRACKED_NETHER_BRICKS);
		$this->mapSimple(Blocks::CRACKED_POLISHED_BLACKSTONE_BRICKS(), Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS);
		$this->mapSimple(Blocks::CRACKED_STONE_BRICKS(), Ids::CRACKED_STONE_BRICKS);
		$this->mapSimple(Blocks::CRAFTING_TABLE(), Ids::CRAFTING_TABLE);
		$this->mapSimple(Blocks::CRIMSON_ROOTS(), Ids::CRIMSON_ROOTS);
		$this->mapSimple(Blocks::CRYING_OBSIDIAN(), Ids::CRYING_OBSIDIAN);
		$this->mapSimple(Blocks::DANDELION(), Ids::DANDELION);
		$this->mapSimple(Blocks::CUT_RED_SANDSTONE(), Ids::CUT_RED_SANDSTONE);
		$this->mapSimple(Blocks::CUT_SANDSTONE(), Ids::CUT_SANDSTONE);
		$this->mapSimple(Blocks::DARK_PRISMARINE(), Ids::DARK_PRISMARINE);
		$this->mapSimple(Blocks::DEAD_BUSH(), Ids::DEADBUSH);
		$this->mapSimple(Blocks::DEEPSLATE_BRICKS(), Ids::DEEPSLATE_BRICKS);
		$this->mapSimple(Blocks::DEEPSLATE_COAL_ORE(), Ids::DEEPSLATE_COAL_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_COPPER_ORE(), Ids::DEEPSLATE_COPPER_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_DIAMOND_ORE(), Ids::DEEPSLATE_DIAMOND_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_EMERALD_ORE(), Ids::DEEPSLATE_EMERALD_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_GOLD_ORE(), Ids::DEEPSLATE_GOLD_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_IRON_ORE(), Ids::DEEPSLATE_IRON_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_LAPIS_LAZULI_ORE(), Ids::DEEPSLATE_LAPIS_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_TILES(), Ids::DEEPSLATE_TILES);
		$this->mapSimple(Blocks::DIAMOND(), Ids::DIAMOND_BLOCK);
		$this->mapSimple(Blocks::DIAMOND_ORE(), Ids::DIAMOND_ORE);
		$this->mapSimple(Blocks::DIORITE(), Ids::DIORITE);
		$this->mapSimple(Blocks::DRAGON_EGG(), Ids::DRAGON_EGG);
		$this->mapSimple(Blocks::DRIED_KELP(), Ids::DRIED_KELP_BLOCK);
		$this->mapSimple(Blocks::ELEMENT_ACTINIUM(), Ids::ELEMENT_89);
		$this->mapSimple(Blocks::ELEMENT_ALUMINUM(), Ids::ELEMENT_13);
		$this->mapSimple(Blocks::ELEMENT_AMERICIUM(), Ids::ELEMENT_95);
		$this->mapSimple(Blocks::ELEMENT_ANTIMONY(), Ids::ELEMENT_51);
		$this->mapSimple(Blocks::ELEMENT_ARGON(), Ids::ELEMENT_18);
		$this->mapSimple(Blocks::ELEMENT_ARSENIC(), Ids::ELEMENT_33);
		$this->mapSimple(Blocks::ELEMENT_ASTATINE(), Ids::ELEMENT_85);
		$this->mapSimple(Blocks::ELEMENT_BARIUM(), Ids::ELEMENT_56);
		$this->mapSimple(Blocks::ELEMENT_BERKELIUM(), Ids::ELEMENT_97);
		$this->mapSimple(Blocks::ELEMENT_BERYLLIUM(), Ids::ELEMENT_4);
		$this->mapSimple(Blocks::ELEMENT_BISMUTH(), Ids::ELEMENT_83);
		$this->mapSimple(Blocks::ELEMENT_BOHRIUM(), Ids::ELEMENT_107);
		$this->mapSimple(Blocks::ELEMENT_BORON(), Ids::ELEMENT_5);
		$this->mapSimple(Blocks::ELEMENT_BROMINE(), Ids::ELEMENT_35);
		$this->mapSimple(Blocks::ELEMENT_CADMIUM(), Ids::ELEMENT_48);
		$this->mapSimple(Blocks::ELEMENT_CALCIUM(), Ids::ELEMENT_20);
		$this->mapSimple(Blocks::ELEMENT_CALIFORNIUM(), Ids::ELEMENT_98);
		$this->mapSimple(Blocks::ELEMENT_CARBON(), Ids::ELEMENT_6);
		$this->mapSimple(Blocks::ELEMENT_CERIUM(), Ids::ELEMENT_58);
		$this->mapSimple(Blocks::ELEMENT_CESIUM(), Ids::ELEMENT_55);
		$this->mapSimple(Blocks::ELEMENT_CHLORINE(), Ids::ELEMENT_17);
		$this->mapSimple(Blocks::ELEMENT_CHROMIUM(), Ids::ELEMENT_24);
		$this->mapSimple(Blocks::ELEMENT_COBALT(), Ids::ELEMENT_27);
		$this->mapSimple(Blocks::ELEMENT_COPERNICIUM(), Ids::ELEMENT_112);
		$this->mapSimple(Blocks::ELEMENT_COPPER(), Ids::ELEMENT_29);
		$this->mapSimple(Blocks::ELEMENT_CURIUM(), Ids::ELEMENT_96);
		$this->mapSimple(Blocks::ELEMENT_DARMSTADTIUM(), Ids::ELEMENT_110);
		$this->mapSimple(Blocks::ELEMENT_DUBNIUM(), Ids::ELEMENT_105);
		$this->mapSimple(Blocks::ELEMENT_DYSPROSIUM(), Ids::ELEMENT_66);
		$this->mapSimple(Blocks::ELEMENT_EINSTEINIUM(), Ids::ELEMENT_99);
		$this->mapSimple(Blocks::ELEMENT_ERBIUM(), Ids::ELEMENT_68);
		$this->mapSimple(Blocks::ELEMENT_EUROPIUM(), Ids::ELEMENT_63);
		$this->mapSimple(Blocks::ELEMENT_FERMIUM(), Ids::ELEMENT_100);
		$this->mapSimple(Blocks::ELEMENT_FLEROVIUM(), Ids::ELEMENT_114);
		$this->mapSimple(Blocks::ELEMENT_FLUORINE(), Ids::ELEMENT_9);
		$this->mapSimple(Blocks::ELEMENT_FRANCIUM(), Ids::ELEMENT_87);
		$this->mapSimple(Blocks::ELEMENT_GADOLINIUM(), Ids::ELEMENT_64);
		$this->mapSimple(Blocks::ELEMENT_GALLIUM(), Ids::ELEMENT_31);
		$this->mapSimple(Blocks::ELEMENT_GERMANIUM(), Ids::ELEMENT_32);
		$this->mapSimple(Blocks::ELEMENT_GOLD(), Ids::ELEMENT_79);
		$this->mapSimple(Blocks::ELEMENT_HAFNIUM(), Ids::ELEMENT_72);
		$this->mapSimple(Blocks::ELEMENT_HASSIUM(), Ids::ELEMENT_108);
		$this->mapSimple(Blocks::ELEMENT_HELIUM(), Ids::ELEMENT_2);
		$this->mapSimple(Blocks::ELEMENT_HOLMIUM(), Ids::ELEMENT_67);
		$this->mapSimple(Blocks::ELEMENT_HYDROGEN(), Ids::ELEMENT_1);
		$this->mapSimple(Blocks::ELEMENT_INDIUM(), Ids::ELEMENT_49);
		$this->mapSimple(Blocks::ELEMENT_IODINE(), Ids::ELEMENT_53);
		$this->mapSimple(Blocks::ELEMENT_IRIDIUM(), Ids::ELEMENT_77);
		$this->mapSimple(Blocks::ELEMENT_IRON(), Ids::ELEMENT_26);
		$this->mapSimple(Blocks::ELEMENT_KRYPTON(), Ids::ELEMENT_36);
		$this->mapSimple(Blocks::ELEMENT_LANTHANUM(), Ids::ELEMENT_57);
		$this->mapSimple(Blocks::ELEMENT_LAWRENCIUM(), Ids::ELEMENT_103);
		$this->mapSimple(Blocks::ELEMENT_LEAD(), Ids::ELEMENT_82);
		$this->mapSimple(Blocks::ELEMENT_LITHIUM(), Ids::ELEMENT_3);
		$this->mapSimple(Blocks::ELEMENT_LIVERMORIUM(), Ids::ELEMENT_116);
		$this->mapSimple(Blocks::ELEMENT_LUTETIUM(), Ids::ELEMENT_71);
		$this->mapSimple(Blocks::ELEMENT_MAGNESIUM(), Ids::ELEMENT_12);
		$this->mapSimple(Blocks::ELEMENT_MANGANESE(), Ids::ELEMENT_25);
		$this->mapSimple(Blocks::ELEMENT_MEITNERIUM(), Ids::ELEMENT_109);
		$this->mapSimple(Blocks::ELEMENT_MENDELEVIUM(), Ids::ELEMENT_101);
		$this->mapSimple(Blocks::ELEMENT_MERCURY(), Ids::ELEMENT_80);
		$this->mapSimple(Blocks::ELEMENT_MOLYBDENUM(), Ids::ELEMENT_42);
		$this->mapSimple(Blocks::ELEMENT_MOSCOVIUM(), Ids::ELEMENT_115);
		$this->mapSimple(Blocks::ELEMENT_NEODYMIUM(), Ids::ELEMENT_60);
		$this->mapSimple(Blocks::ELEMENT_NEON(), Ids::ELEMENT_10);
		$this->mapSimple(Blocks::ELEMENT_NEPTUNIUM(), Ids::ELEMENT_93);
		$this->mapSimple(Blocks::ELEMENT_NICKEL(), Ids::ELEMENT_28);
		$this->mapSimple(Blocks::ELEMENT_NIHONIUM(), Ids::ELEMENT_113);
		$this->mapSimple(Blocks::ELEMENT_NIOBIUM(), Ids::ELEMENT_41);
		$this->mapSimple(Blocks::ELEMENT_NITROGEN(), Ids::ELEMENT_7);
		$this->mapSimple(Blocks::ELEMENT_NOBELIUM(), Ids::ELEMENT_102);
		$this->mapSimple(Blocks::ELEMENT_OGANESSON(), Ids::ELEMENT_118);
		$this->mapSimple(Blocks::ELEMENT_OSMIUM(), Ids::ELEMENT_76);
		$this->mapSimple(Blocks::ELEMENT_OXYGEN(), Ids::ELEMENT_8);
		$this->mapSimple(Blocks::ELEMENT_PALLADIUM(), Ids::ELEMENT_46);
		$this->mapSimple(Blocks::ELEMENT_PHOSPHORUS(), Ids::ELEMENT_15);
		$this->mapSimple(Blocks::ELEMENT_PLATINUM(), Ids::ELEMENT_78);
		$this->mapSimple(Blocks::ELEMENT_PLUTONIUM(), Ids::ELEMENT_94);
		$this->mapSimple(Blocks::ELEMENT_POLONIUM(), Ids::ELEMENT_84);
		$this->mapSimple(Blocks::ELEMENT_POTASSIUM(), Ids::ELEMENT_19);
		$this->mapSimple(Blocks::ELEMENT_PRASEODYMIUM(), Ids::ELEMENT_59);
		$this->mapSimple(Blocks::ELEMENT_PROMETHIUM(), Ids::ELEMENT_61);
		$this->mapSimple(Blocks::ELEMENT_PROTACTINIUM(), Ids::ELEMENT_91);
		$this->mapSimple(Blocks::ELEMENT_RADIUM(), Ids::ELEMENT_88);
		$this->mapSimple(Blocks::ELEMENT_RADON(), Ids::ELEMENT_86);
		$this->mapSimple(Blocks::ELEMENT_RHENIUM(), Ids::ELEMENT_75);
		$this->mapSimple(Blocks::ELEMENT_RHODIUM(), Ids::ELEMENT_45);
		$this->mapSimple(Blocks::ELEMENT_ROENTGENIUM(), Ids::ELEMENT_111);
		$this->mapSimple(Blocks::ELEMENT_RUBIDIUM(), Ids::ELEMENT_37);
		$this->mapSimple(Blocks::ELEMENT_RUTHENIUM(), Ids::ELEMENT_44);
		$this->mapSimple(Blocks::ELEMENT_RUTHERFORDIUM(), Ids::ELEMENT_104);
		$this->mapSimple(Blocks::ELEMENT_SAMARIUM(), Ids::ELEMENT_62);
		$this->mapSimple(Blocks::ELEMENT_SCANDIUM(), Ids::ELEMENT_21);
		$this->mapSimple(Blocks::ELEMENT_SEABORGIUM(), Ids::ELEMENT_106);
		$this->mapSimple(Blocks::ELEMENT_SELENIUM(), Ids::ELEMENT_34);
		$this->mapSimple(Blocks::ELEMENT_SILICON(), Ids::ELEMENT_14);
		$this->mapSimple(Blocks::ELEMENT_SILVER(), Ids::ELEMENT_47);
		$this->mapSimple(Blocks::ELEMENT_SODIUM(), Ids::ELEMENT_11);
		$this->mapSimple(Blocks::ELEMENT_STRONTIUM(), Ids::ELEMENT_38);
		$this->mapSimple(Blocks::ELEMENT_SULFUR(), Ids::ELEMENT_16);
		$this->mapSimple(Blocks::ELEMENT_TANTALUM(), Ids::ELEMENT_73);
		$this->mapSimple(Blocks::ELEMENT_TECHNETIUM(), Ids::ELEMENT_43);
		$this->mapSimple(Blocks::ELEMENT_TELLURIUM(), Ids::ELEMENT_52);
		$this->mapSimple(Blocks::ELEMENT_TENNESSINE(), Ids::ELEMENT_117);
		$this->mapSimple(Blocks::ELEMENT_TERBIUM(), Ids::ELEMENT_65);
		$this->mapSimple(Blocks::ELEMENT_THALLIUM(), Ids::ELEMENT_81);
		$this->mapSimple(Blocks::ELEMENT_THORIUM(), Ids::ELEMENT_90);
		$this->mapSimple(Blocks::ELEMENT_THULIUM(), Ids::ELEMENT_69);
		$this->mapSimple(Blocks::ELEMENT_TIN(), Ids::ELEMENT_50);
		$this->mapSimple(Blocks::ELEMENT_TITANIUM(), Ids::ELEMENT_22);
		$this->mapSimple(Blocks::ELEMENT_TUNGSTEN(), Ids::ELEMENT_74);
		$this->mapSimple(Blocks::ELEMENT_URANIUM(), Ids::ELEMENT_92);
		$this->mapSimple(Blocks::ELEMENT_VANADIUM(), Ids::ELEMENT_23);
		$this->mapSimple(Blocks::ELEMENT_XENON(), Ids::ELEMENT_54);
		$this->mapSimple(Blocks::ELEMENT_YTTERBIUM(), Ids::ELEMENT_70);
		$this->mapSimple(Blocks::ELEMENT_YTTRIUM(), Ids::ELEMENT_39);
		$this->mapSimple(Blocks::ELEMENT_ZERO(), Ids::ELEMENT_0);
		$this->mapSimple(Blocks::ELEMENT_ZINC(), Ids::ELEMENT_30);
		$this->mapSimple(Blocks::ELEMENT_ZIRCONIUM(), Ids::ELEMENT_40);
		$this->mapSimple(Blocks::EMERALD(), Ids::EMERALD_BLOCK);
		$this->mapSimple(Blocks::EMERALD_ORE(), Ids::EMERALD_ORE);
		$this->mapSimple(Blocks::ENCHANTING_TABLE(), Ids::ENCHANTING_TABLE);
		$this->mapSimple(Blocks::END_STONE(), Ids::END_STONE);
		$this->mapSimple(Blocks::END_STONE_BRICKS(), Ids::END_BRICKS);
		$this->mapSimple(Blocks::FERN(), Ids::FERN);
		$this->mapSimple(Blocks::FLETCHING_TABLE(), Ids::FLETCHING_TABLE);
		$this->mapSimple(Blocks::GILDED_BLACKSTONE(), Ids::GILDED_BLACKSTONE);
		$this->mapSimple(Blocks::GLASS(), Ids::GLASS);
		$this->mapSimple(Blocks::GLASS_PANE(), Ids::GLASS_PANE);
		$this->mapSimple(Blocks::GLOWING_OBSIDIAN(), Ids::GLOWINGOBSIDIAN);
		$this->mapSimple(Blocks::GLOWSTONE(), Ids::GLOWSTONE);
		$this->mapSimple(Blocks::GOLD(), Ids::GOLD_BLOCK);
		$this->mapSimple(Blocks::GOLD_ORE(), Ids::GOLD_ORE);
		$this->mapSimple(Blocks::GRANITE(), Ids::GRANITE);
		$this->mapSimple(Blocks::GRASS(), Ids::GRASS_BLOCK);
		$this->mapSimple(Blocks::GRASS_PATH(), Ids::GRASS_PATH);
		$this->mapSimple(Blocks::GRAVEL(), Ids::GRAVEL);
		$this->mapSimple(Blocks::HANGING_ROOTS(), Ids::HANGING_ROOTS);
		$this->mapSimple(Blocks::HARDENED_CLAY(), Ids::HARDENED_CLAY);
		$this->mapSimple(Blocks::HARDENED_GLASS(), Ids::HARD_GLASS);
		$this->mapSimple(Blocks::HARDENED_GLASS_PANE(), Ids::HARD_GLASS_PANE);
		$this->mapSimple(Blocks::HONEYCOMB(), Ids::HONEYCOMB_BLOCK);
		$this->mapSimple(Blocks::ICE(), Ids::ICE);
		$this->mapSimple(Blocks::INFESTED_CHISELED_STONE_BRICK(), Ids::INFESTED_CHISELED_STONE_BRICKS);
		$this->mapSimple(Blocks::INFESTED_COBBLESTONE(), Ids::INFESTED_COBBLESTONE);
		$this->mapSimple(Blocks::INFESTED_CRACKED_STONE_BRICK(), Ids::INFESTED_CRACKED_STONE_BRICKS);
		$this->mapSimple(Blocks::INFESTED_MOSSY_STONE_BRICK(), Ids::INFESTED_MOSSY_STONE_BRICKS);
		$this->mapSimple(Blocks::INFESTED_STONE(), Ids::INFESTED_STONE);
		$this->mapSimple(Blocks::INFESTED_STONE_BRICK(), Ids::INFESTED_STONE_BRICKS);
		$this->mapSimple(Blocks::INFO_UPDATE(), Ids::INFO_UPDATE);
		$this->mapSimple(Blocks::INFO_UPDATE2(), Ids::INFO_UPDATE2);
		$this->mapSimple(Blocks::INVISIBLE_BEDROCK(), Ids::INVISIBLE_BEDROCK);
		$this->mapSimple(Blocks::IRON(), Ids::IRON_BLOCK);
		$this->mapSimple(Blocks::IRON_BARS(), Ids::IRON_BARS);
		$this->mapSimple(Blocks::IRON_ORE(), Ids::IRON_ORE);
		$this->mapSimple(Blocks::JUKEBOX(), Ids::JUKEBOX);
		$this->mapSimple(Blocks::LAPIS_LAZULI(), Ids::LAPIS_BLOCK);
		$this->mapSimple(Blocks::LAPIS_LAZULI_ORE(), Ids::LAPIS_ORE);
		$this->mapSimple(Blocks::LEGACY_STONECUTTER(), Ids::STONECUTTER);
		$this->mapSimple(Blocks::LILY_PAD(), Ids::WATERLILY);
		$this->mapSimple(Blocks::MAGMA(), Ids::MAGMA);
		$this->mapSimple(Blocks::MANGROVE_ROOTS(), Ids::MANGROVE_ROOTS);
		$this->mapSimple(Blocks::MELON(), Ids::MELON_BLOCK);
		$this->mapSimple(Blocks::MONSTER_SPAWNER(), Ids::MOB_SPAWNER);
		$this->mapSimple(Blocks::MOSSY_COBBLESTONE(), Ids::MOSSY_COBBLESTONE);
		$this->mapSimple(Blocks::MOSSY_STONE_BRICKS(), Ids::MOSSY_STONE_BRICKS);
		$this->mapSimple(Blocks::MUD(), Ids::MUD);
		$this->mapSimple(Blocks::MUD_BRICKS(), Ids::MUD_BRICKS);
		$this->mapSimple(Blocks::MYCELIUM(), Ids::MYCELIUM);
		$this->mapSimple(Blocks::NETHERITE(), Ids::NETHERITE_BLOCK);
		$this->mapSimple(Blocks::NETHERRACK(), Ids::NETHERRACK);
		$this->mapSimple(Blocks::NETHER_BRICKS(), Ids::NETHER_BRICK);
		$this->mapSimple(Blocks::NETHER_BRICK_FENCE(), Ids::NETHER_BRICK_FENCE);
		$this->mapSimple(Blocks::NETHER_GOLD_ORE(), Ids::NETHER_GOLD_ORE);
		$this->mapSimple(Blocks::NETHER_QUARTZ_ORE(), Ids::QUARTZ_ORE);
		$this->mapSimple(Blocks::NETHER_REACTOR_CORE(), Ids::NETHERREACTOR);
		$this->mapSimple(Blocks::NETHER_WART_BLOCK(), Ids::NETHER_WART_BLOCK);
		$this->mapSimple(Blocks::NOTE_BLOCK(), Ids::NOTEBLOCK);
		$this->mapSimple(Blocks::OBSIDIAN(), Ids::OBSIDIAN);
		$this->mapSimple(Blocks::PACKED_ICE(), Ids::PACKED_ICE);
		$this->mapSimple(Blocks::PACKED_MUD(), Ids::PACKED_MUD);
		$this->mapSimple(Blocks::PODZOL(), Ids::PODZOL);
		$this->mapSimple(Blocks::POLISHED_ANDESITE(), Ids::POLISHED_ANDESITE);
		$this->mapSimple(Blocks::POLISHED_BLACKSTONE(), Ids::POLISHED_BLACKSTONE);
		$this->mapSimple(Blocks::POLISHED_BLACKSTONE_BRICKS(), Ids::POLISHED_BLACKSTONE_BRICKS);
		$this->mapSimple(Blocks::POLISHED_DEEPSLATE(), Ids::POLISHED_DEEPSLATE);
		$this->mapSimple(Blocks::POLISHED_DIORITE(), Ids::POLISHED_DIORITE);
		$this->mapSimple(Blocks::POLISHED_GRANITE(), Ids::POLISHED_GRANITE);
		$this->mapSimple(Blocks::POLISHED_TUFF(), Ids::POLISHED_TUFF);
		$this->mapSimple(Blocks::PRISMARINE(), Ids::PRISMARINE);
		$this->mapSimple(Blocks::PRISMARINE_BRICKS(), Ids::PRISMARINE_BRICKS);
		$this->mapSimple(Blocks::QUARTZ_BRICKS(), Ids::QUARTZ_BRICKS);
		$this->mapSimple(Blocks::RAW_COPPER(), Ids::RAW_COPPER_BLOCK);
		$this->mapSimple(Blocks::RAW_GOLD(), Ids::RAW_GOLD_BLOCK);
		$this->mapSimple(Blocks::RAW_IRON(), Ids::RAW_IRON_BLOCK);
		$this->mapSimple(Blocks::REDSTONE(), Ids::REDSTONE_BLOCK);
		$this->mapSimple(Blocks::RED_MUSHROOM(), Ids::RED_MUSHROOM);
		$this->mapSimple(Blocks::RED_NETHER_BRICKS(), Ids::RED_NETHER_BRICK);
		$this->mapSimple(Blocks::RED_SAND(), Ids::RED_SAND);
		$this->mapSimple(Blocks::RED_SANDSTONE(), Ids::RED_SANDSTONE);
		$this->mapSimple(Blocks::REINFORCED_DEEPSLATE(), Ids::REINFORCED_DEEPSLATE);
		$this->mapSimple(Blocks::RESERVED6(), Ids::RESERVED6);
		$this->mapSimple(Blocks::RESIN(), Ids::RESIN_BLOCK);
		$this->mapSimple(Blocks::RESIN_BRICKS(), Ids::RESIN_BRICKS);
		$this->mapSimple(Blocks::SAND(), Ids::SAND);
		$this->mapSimple(Blocks::SANDSTONE(), Ids::SANDSTONE);
		$this->mapSimple(Blocks::SCULK(), Ids::SCULK);
		$this->mapSimple(Blocks::SEA_LANTERN(), Ids::SEA_LANTERN);
		$this->mapSimple(Blocks::SHROOMLIGHT(), Ids::SHROOMLIGHT);
		$this->mapSimple(Blocks::SHULKER_BOX(), Ids::UNDYED_SHULKER_BOX);
		$this->mapSimple(Blocks::SLIME(), Ids::SLIME);
		$this->mapSimple(Blocks::SMITHING_TABLE(), Ids::SMITHING_TABLE);
		$this->mapSimple(Blocks::SMOOTH_BASALT(), Ids::SMOOTH_BASALT);
		$this->mapSimple(Blocks::SMOOTH_RED_SANDSTONE(), Ids::SMOOTH_RED_SANDSTONE);
		$this->mapSimple(Blocks::SMOOTH_SANDSTONE(), Ids::SMOOTH_SANDSTONE);
		$this->mapSimple(Blocks::SMOOTH_STONE(), Ids::SMOOTH_STONE);
		$this->mapSimple(Blocks::SNOW(), Ids::SNOW);
		$this->mapSimple(Blocks::SOUL_SAND(), Ids::SOUL_SAND);
		$this->mapSimple(Blocks::SOUL_SOIL(), Ids::SOUL_SOIL);
		$this->mapSimple(Blocks::SPORE_BLOSSOM(), Ids::SPORE_BLOSSOM);
		$this->mapSimple(Blocks::STONE(), Ids::STONE);
		$this->mapSimple(Blocks::STONE_BRICKS(), Ids::STONE_BRICKS);
		$this->mapSimple(Blocks::TALL_GRASS(), Ids::SHORT_GRASS);  //no, this is not a typo - tall_grass is now the double block, just to be confusing :(
		$this->mapSimple(Blocks::TINTED_GLASS(), Ids::TINTED_GLASS);
		$this->mapSimple(Blocks::TORCHFLOWER(), Ids::TORCHFLOWER);
		$this->mapSimple(Blocks::TUFF(), Ids::TUFF);
		$this->mapSimple(Blocks::TUFF_BRICKS(), Ids::TUFF_BRICKS);
		$this->mapSimple(Blocks::WARPED_WART_BLOCK(), Ids::WARPED_WART_BLOCK);
		$this->mapSimple(Blocks::WARPED_ROOTS(), Ids::WARPED_ROOTS);
		$this->mapSimple(Blocks::WITHER_ROSE(), Ids::WITHER_ROSE);

		$this->mapSimple(Blocks::ALLIUM(), Ids::ALLIUM);
		$this->mapSimple(Blocks::CORNFLOWER(), Ids::CORNFLOWER);
		$this->mapSimple(Blocks::AZURE_BLUET(), Ids::AZURE_BLUET);
		$this->mapSimple(Blocks::LILY_OF_THE_VALLEY(), Ids::LILY_OF_THE_VALLEY);
		$this->mapSimple(Blocks::BLUE_ORCHID(), Ids::BLUE_ORCHID);
		$this->mapSimple(Blocks::OXEYE_DAISY(), Ids::OXEYE_DAISY);
		$this->mapSimple(Blocks::POPPY(), Ids::POPPY);
		$this->mapSimple(Blocks::ORANGE_TULIP(), Ids::ORANGE_TULIP);
		$this->mapSimple(Blocks::PINK_TULIP(), Ids::PINK_TULIP);
		$this->mapSimple(Blocks::RED_TULIP(), Ids::RED_TULIP);
		$this->mapSimple(Blocks::WHITE_TULIP(), Ids::WHITE_TULIP);
	}

	private function registerColoredIdOnlyMappings() : void{
		$this->mapColored(Blocks::STAINED_HARDENED_GLASS(), "minecraft:hard_", "_stained_glass");
		$this->mapColored(Blocks::STAINED_HARDENED_GLASS_PANE(), "minecraft:hard_", "_stained_glass_pane");

		$this->mapColored(Blocks::CARPET(), "minecraft:", "_carpet");
		$this->mapColored(Blocks::CONCRETE(), "minecraft:", "_concrete");
		$this->mapColored(Blocks::CONCRETE_POWDER(), "minecraft:", "_concrete_powder");
		$this->mapColored(Blocks::DYED_SHULKER_BOX(), "minecraft:", "_shulker_box");
		$this->mapColored(Blocks::STAINED_CLAY(), "minecraft:", "_terracotta");
		$this->mapColored(Blocks::STAINED_GLASS(), "minecraft:", "_stained_glass");
		$this->mapColored(Blocks::STAINED_GLASS_PANE(), "minecraft:", "_stained_glass_pane");
		$this->mapColored(Blocks::WOOL(), "minecraft:", "_wool");
	}

	private function registerLeavesMappings() : void{
		foreach([
			Ids::ACACIA_LEAVES => Blocks::ACACIA_LEAVES(),
			Ids::AZALEA_LEAVES => Blocks::AZALEA_LEAVES(),
			Ids::AZALEA_LEAVES_FLOWERED => Blocks::FLOWERING_AZALEA_LEAVES(),
			Ids::BIRCH_LEAVES => Blocks::BIRCH_LEAVES(),
			Ids::CHERRY_LEAVES => Blocks::CHERRY_LEAVES(),
			Ids::DARK_OAK_LEAVES => Blocks::DARK_OAK_LEAVES(),
			Ids::JUNGLE_LEAVES => Blocks::JUNGLE_LEAVES(),
			Ids::MANGROVE_LEAVES => Blocks::MANGROVE_LEAVES(),
			Ids::OAK_LEAVES => Blocks::OAK_LEAVES(),
			Ids::PALE_OAK_LEAVES => Blocks::PALE_OAK_LEAVES(),
			Ids::SPRUCE_LEAVES => Blocks::SPRUCE_LEAVES()
		] as $id => $block){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeLeaves(...), BlockStateSerializerHelper::encodeLeaves(...));
		}
	}

	private function registerSaplingMappings() : void{
		foreach([
			Ids::ACACIA_SAPLING => Blocks::ACACIA_SAPLING(),
			Ids::BIRCH_SAPLING => Blocks::BIRCH_SAPLING(),
			Ids::DARK_OAK_SAPLING => Blocks::DARK_OAK_SAPLING(),
			Ids::JUNGLE_SAPLING => Blocks::JUNGLE_SAPLING(),
			Ids::OAK_SAPLING => Blocks::OAK_SAPLING(),
			Ids::SPRUCE_SAPLING => Blocks::SPRUCE_SAPLING(),
		] as $id => $block){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeSapling(...), BlockStateSerializerHelper::encodeSapling(...));
		}
	}

	private function registerFlattenedEnumMappings() : void{
		//the suffixes are different for different variants, so we have to map the whole ID to use the flattened mode
		//therefore the prefix and suffix are empty
		$this->mapSwitchedIdEnumWithExtra(
			Blocks::MOB_HEAD(),
			MobHeadType::class,
			fn(MobHead $block) => $block->getMobHeadType(),
			fn(MobHead $block, MobHeadType $value) => $block->setMobHeadType($value),
			fn(MobHead $block, Reader $in) => $block->setFacing($in->readFacingWithoutDown()),
			fn(MobHead $block, Writer $out) => $out->writeFacingWithoutDown($block->getFacing())
		);
		$this->mapSwitchedIdEnumWithExtra(
			Blocks::FROGLIGHT(),
			FroglightType::class,
			fn(Froglight $block) => $block->getFroglightType(),
			fn(Froglight $block, FroglightType $value) => $block->setFroglightType($value),
			fn(Froglight $block, Reader $in) => $block->setAxis($in->readPillarAxis()),
			fn(Froglight $block, Writer $out) => $out->writePillarAxis($block->getAxis())
		);
	}

	private function registerStoneLikeSlabMappings() : void{
		$this->mapSlab(Blocks::ANDESITE_SLAB(), Ids::ANDESITE_SLAB, Ids::ANDESITE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::BLACKSTONE_SLAB(), Ids::BLACKSTONE_SLAB, Ids::BLACKSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::BRICK_SLAB(), Ids::BRICK_SLAB, Ids::BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::COBBLED_DEEPSLATE_SLAB(), Ids::COBBLED_DEEPSLATE_SLAB, Ids::COBBLED_DEEPSLATE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::COBBLESTONE_SLAB(), Ids::COBBLESTONE_SLAB, Ids::COBBLESTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::CUT_RED_SANDSTONE_SLAB(), Ids::CUT_RED_SANDSTONE_SLAB, Ids::CUT_RED_SANDSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::CUT_SANDSTONE_SLAB(), Ids::CUT_SANDSTONE_SLAB, Ids::CUT_SANDSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::DARK_PRISMARINE_SLAB(), Ids::DARK_PRISMARINE_SLAB, Ids::DARK_PRISMARINE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::DEEPSLATE_BRICK_SLAB(), Ids::DEEPSLATE_BRICK_SLAB, Ids::DEEPSLATE_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::DEEPSLATE_TILE_SLAB(), Ids::DEEPSLATE_TILE_SLAB, Ids::DEEPSLATE_TILE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::DIORITE_SLAB(), Ids::DIORITE_SLAB, Ids::DIORITE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::END_STONE_BRICK_SLAB(), Ids::END_STONE_BRICK_SLAB, Ids::END_STONE_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::FAKE_WOODEN_SLAB(), Ids::PETRIFIED_OAK_SLAB, Ids::PETRIFIED_OAK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::GRANITE_SLAB(), Ids::GRANITE_SLAB, Ids::GRANITE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::MOSSY_COBBLESTONE_SLAB(), Ids::MOSSY_COBBLESTONE_SLAB, Ids::MOSSY_COBBLESTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::MOSSY_STONE_BRICK_SLAB(), Ids::MOSSY_STONE_BRICK_SLAB, Ids::MOSSY_STONE_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::MUD_BRICK_SLAB(), Ids::MUD_BRICK_SLAB, Ids::MUD_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::NETHER_BRICK_SLAB(), Ids::NETHER_BRICK_SLAB, Ids::NETHER_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::POLISHED_ANDESITE_SLAB(), Ids::POLISHED_ANDESITE_SLAB, Ids::POLISHED_ANDESITE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::POLISHED_BLACKSTONE_BRICK_SLAB(), Ids::POLISHED_BLACKSTONE_BRICK_SLAB, Ids::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::POLISHED_BLACKSTONE_SLAB(), Ids::POLISHED_BLACKSTONE_SLAB, Ids::POLISHED_BLACKSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::POLISHED_DEEPSLATE_SLAB(), Ids::POLISHED_DEEPSLATE_SLAB, Ids::POLISHED_DEEPSLATE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::POLISHED_DIORITE_SLAB(), Ids::POLISHED_DIORITE_SLAB, Ids::POLISHED_DIORITE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::POLISHED_GRANITE_SLAB(), Ids::POLISHED_GRANITE_SLAB, Ids::POLISHED_GRANITE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::POLISHED_TUFF_SLAB(), Ids::POLISHED_TUFF_SLAB, Ids::POLISHED_TUFF_DOUBLE_SLAB);
		$this->mapSlab(Blocks::PRISMARINE_BRICKS_SLAB(), Ids::PRISMARINE_BRICK_SLAB, Ids::PRISMARINE_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::PRISMARINE_SLAB(), Ids::PRISMARINE_SLAB, Ids::PRISMARINE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::PURPUR_SLAB(), Ids::PURPUR_SLAB, Ids::PURPUR_DOUBLE_SLAB);
		$this->mapSlab(Blocks::QUARTZ_SLAB(), Ids::QUARTZ_SLAB, Ids::QUARTZ_DOUBLE_SLAB);
		$this->mapSlab(Blocks::RED_NETHER_BRICK_SLAB(), Ids::RED_NETHER_BRICK_SLAB, Ids::RED_NETHER_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::RED_SANDSTONE_SLAB(), Ids::RED_SANDSTONE_SLAB, Ids::RED_SANDSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::RESIN_BRICK_SLAB(), Ids::RESIN_BRICK_SLAB, Ids::RESIN_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::SANDSTONE_SLAB(), Ids::SANDSTONE_SLAB, Ids::SANDSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::SMOOTH_QUARTZ_SLAB(), Ids::SMOOTH_QUARTZ_SLAB, Ids::SMOOTH_QUARTZ_DOUBLE_SLAB);
		$this->mapSlab(Blocks::SMOOTH_RED_SANDSTONE_SLAB(), Ids::SMOOTH_RED_SANDSTONE_SLAB, Ids::SMOOTH_RED_SANDSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::SMOOTH_SANDSTONE_SLAB(), Ids::SMOOTH_SANDSTONE_SLAB, Ids::SMOOTH_SANDSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::SMOOTH_STONE_SLAB(), Ids::SMOOTH_STONE_SLAB, Ids::SMOOTH_STONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::STONE_BRICK_SLAB(), Ids::STONE_BRICK_SLAB, Ids::STONE_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::STONE_SLAB(), Ids::NORMAL_STONE_SLAB, Ids::NORMAL_STONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::TUFF_BRICK_SLAB(), Ids::TUFF_BRICK_SLAB, Ids::TUFF_BRICK_DOUBLE_SLAB);
		$this->mapSlab(Blocks::TUFF_SLAB(), Ids::TUFF_SLAB, Ids::TUFF_DOUBLE_SLAB);
	}

	private function registerStoneLikeStairMappings() : void{
		$this->mapStairs(Blocks::ANDESITE_STAIRS(), Ids::ANDESITE_STAIRS);
		$this->mapStairs(Blocks::BLACKSTONE_STAIRS(), Ids::BLACKSTONE_STAIRS);
		$this->mapStairs(Blocks::BRICK_STAIRS(), Ids::BRICK_STAIRS);
		$this->mapStairs(Blocks::COBBLED_DEEPSLATE_STAIRS(), Ids::COBBLED_DEEPSLATE_STAIRS);
		$this->mapStairs(Blocks::COBBLESTONE_STAIRS(), Ids::STONE_STAIRS);
		$this->mapStairs(Blocks::DARK_PRISMARINE_STAIRS(), Ids::DARK_PRISMARINE_STAIRS);
		$this->mapStairs(Blocks::DEEPSLATE_BRICK_STAIRS(), Ids::DEEPSLATE_BRICK_STAIRS);
		$this->mapStairs(Blocks::DEEPSLATE_TILE_STAIRS(), Ids::DEEPSLATE_TILE_STAIRS);
		$this->mapStairs(Blocks::DIORITE_STAIRS(), Ids::DIORITE_STAIRS);
		$this->mapStairs(Blocks::END_STONE_BRICK_STAIRS(), Ids::END_BRICK_STAIRS);
		$this->mapStairs(Blocks::GRANITE_STAIRS(), Ids::GRANITE_STAIRS);
		$this->mapStairs(Blocks::MOSSY_COBBLESTONE_STAIRS(), Ids::MOSSY_COBBLESTONE_STAIRS);
		$this->mapStairs(Blocks::MOSSY_STONE_BRICK_STAIRS(), Ids::MOSSY_STONE_BRICK_STAIRS);
		$this->mapStairs(Blocks::MUD_BRICK_STAIRS(), Ids::MUD_BRICK_STAIRS);
		$this->mapStairs(Blocks::NETHER_BRICK_STAIRS(), Ids::NETHER_BRICK_STAIRS);
		$this->mapStairs(Blocks::POLISHED_ANDESITE_STAIRS(), Ids::POLISHED_ANDESITE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_BLACKSTONE_BRICK_STAIRS(), Ids::POLISHED_BLACKSTONE_BRICK_STAIRS);
		$this->mapStairs(Blocks::POLISHED_BLACKSTONE_STAIRS(), Ids::POLISHED_BLACKSTONE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_DEEPSLATE_STAIRS(), Ids::POLISHED_DEEPSLATE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_DIORITE_STAIRS(), Ids::POLISHED_DIORITE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_GRANITE_STAIRS(), Ids::POLISHED_GRANITE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_TUFF_STAIRS(), Ids::POLISHED_TUFF_STAIRS);
		$this->mapStairs(Blocks::PRISMARINE_BRICKS_STAIRS(), Ids::PRISMARINE_BRICKS_STAIRS);
		$this->mapStairs(Blocks::PRISMARINE_STAIRS(), Ids::PRISMARINE_STAIRS);
		$this->mapStairs(Blocks::PURPUR_STAIRS(), Ids::PURPUR_STAIRS);
		$this->mapStairs(Blocks::QUARTZ_STAIRS(), Ids::QUARTZ_STAIRS);
		$this->mapStairs(Blocks::RED_NETHER_BRICK_STAIRS(), Ids::RED_NETHER_BRICK_STAIRS);
		$this->mapStairs(Blocks::RED_SANDSTONE_STAIRS(), Ids::RED_SANDSTONE_STAIRS);
		$this->mapStairs(Blocks::RESIN_BRICK_STAIRS(), Ids::RESIN_BRICK_STAIRS);
		$this->mapStairs(Blocks::SANDSTONE_STAIRS(), Ids::SANDSTONE_STAIRS);
		$this->mapStairs(Blocks::SMOOTH_QUARTZ_STAIRS(), Ids::SMOOTH_QUARTZ_STAIRS);
		$this->mapStairs(Blocks::SMOOTH_RED_SANDSTONE_STAIRS(), Ids::SMOOTH_RED_SANDSTONE_STAIRS);
		$this->mapStairs(Blocks::SMOOTH_SANDSTONE_STAIRS(), Ids::SMOOTH_SANDSTONE_STAIRS);
		$this->mapStairs(Blocks::STONE_BRICK_STAIRS(), Ids::STONE_BRICK_STAIRS);
		$this->mapStairs(Blocks::STONE_STAIRS(), Ids::NORMAL_STONE_STAIRS);
		$this->mapStairs(Blocks::TUFF_BRICK_STAIRS(), Ids::TUFF_BRICK_STAIRS);
		$this->mapStairs(Blocks::TUFF_STAIRS(), Ids::TUFF_STAIRS);
	}

	private function registerStoneLikeWallMappings() : void{
		foreach([
			Ids::ANDESITE_WALL => Blocks::ANDESITE_WALL(),
			Ids::BLACKSTONE_WALL => Blocks::BLACKSTONE_WALL(),
			Ids::BRICK_WALL => Blocks::BRICK_WALL(),
			Ids::COBBLED_DEEPSLATE_WALL => Blocks::COBBLED_DEEPSLATE_WALL(),
			Ids::COBBLESTONE_WALL => Blocks::COBBLESTONE_WALL(),
			Ids::DEEPSLATE_BRICK_WALL => Blocks::DEEPSLATE_BRICK_WALL(),
			Ids::DEEPSLATE_TILE_WALL => Blocks::DEEPSLATE_TILE_WALL(),
			Ids::DIORITE_WALL => Blocks::DIORITE_WALL(),
			Ids::END_STONE_BRICK_WALL => Blocks::END_STONE_BRICK_WALL(),
			Ids::GRANITE_WALL => Blocks::GRANITE_WALL(),
			Ids::MOSSY_COBBLESTONE_WALL => Blocks::MOSSY_COBBLESTONE_WALL(),
			Ids::MOSSY_STONE_BRICK_WALL => Blocks::MOSSY_STONE_BRICK_WALL(),
			Ids::MUD_BRICK_WALL => Blocks::MUD_BRICK_WALL(),
			Ids::NETHER_BRICK_WALL => Blocks::NETHER_BRICK_WALL(),
			Ids::POLISHED_BLACKSTONE_BRICK_WALL => Blocks::POLISHED_BLACKSTONE_BRICK_WALL(),
			Ids::POLISHED_BLACKSTONE_WALL => Blocks::POLISHED_BLACKSTONE_WALL(),
			Ids::POLISHED_DEEPSLATE_WALL => Blocks::POLISHED_DEEPSLATE_WALL(),
			Ids::POLISHED_TUFF_WALL => Blocks::POLISHED_TUFF_WALL(),
			Ids::PRISMARINE_WALL => Blocks::PRISMARINE_WALL(),
			Ids::RED_NETHER_BRICK_WALL => Blocks::RED_NETHER_BRICK_WALL(),
			Ids::RED_SANDSTONE_WALL => Blocks::RED_SANDSTONE_WALL(),
			Ids::RESIN_BRICK_WALL => Blocks::RESIN_BRICK_WALL(),
			Ids::SANDSTONE_WALL => Blocks::SANDSTONE_WALL(),
			Ids::STONE_BRICK_WALL => Blocks::STONE_BRICK_WALL(),
			Ids::TUFF_BRICK_WALL => Blocks::TUFF_BRICK_WALL(),
			Ids::TUFF_WALL => Blocks::TUFF_WALL()
		] as $id => $block){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeWall(...), BlockStateSerializerHelper::encodeWall(...));
		}
	}

	private function registerWoodMappings() : void{
		//buttons
		foreach([
			[Blocks::ACACIA_BUTTON(), Ids::ACACIA_BUTTON],
			[Blocks::BIRCH_BUTTON(), Ids::BIRCH_BUTTON],
			[Blocks::CHERRY_BUTTON(), Ids::CHERRY_BUTTON],
			[Blocks::CRIMSON_BUTTON(), Ids::CRIMSON_BUTTON],
			[Blocks::DARK_OAK_BUTTON(), Ids::DARK_OAK_BUTTON],
			[Blocks::JUNGLE_BUTTON(), Ids::JUNGLE_BUTTON],
			[Blocks::MANGROVE_BUTTON(), Ids::MANGROVE_BUTTON],
			[Blocks::OAK_BUTTON(), Ids::WOODEN_BUTTON],
			[Blocks::PALE_OAK_BUTTON(), Ids::PALE_OAK_BUTTON],
			[Blocks::SPRUCE_BUTTON(), Ids::SPRUCE_BUTTON],
			[Blocks::WARPED_BUTTON(), Ids::WARPED_BUTTON]
		] as [$block, $id]){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeButton(...), BlockStateSerializerHelper::encodeButton(...));
		}

		//doors
		foreach([
			[Blocks::ACACIA_DOOR(), Ids::ACACIA_DOOR],
			[Blocks::BIRCH_DOOR(), Ids::BIRCH_DOOR],
			[Blocks::CHERRY_DOOR(), Ids::CHERRY_DOOR],
			[Blocks::CRIMSON_DOOR(), Ids::CRIMSON_DOOR],
			[Blocks::DARK_OAK_DOOR(), Ids::DARK_OAK_DOOR],
			[Blocks::JUNGLE_DOOR(), Ids::JUNGLE_DOOR],
			[Blocks::MANGROVE_DOOR(), Ids::MANGROVE_DOOR],
			[Blocks::OAK_DOOR(), Ids::WOODEN_DOOR],
			[Blocks::PALE_OAK_DOOR(), Ids::PALE_OAK_DOOR],
			[Blocks::SPRUCE_DOOR(), Ids::SPRUCE_DOOR],
			[Blocks::WARPED_DOOR(), Ids::WARPED_DOOR]
		] as [$block, $id]){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeDoor(...), BlockStateSerializerHelper::encodeDoor(...));
		}

		//fences
		foreach([
			[Blocks::ACACIA_FENCE(), Ids::ACACIA_FENCE],
			[Blocks::BIRCH_FENCE(), Ids::BIRCH_FENCE],
			[Blocks::CHERRY_FENCE(), Ids::CHERRY_FENCE],
			[Blocks::DARK_OAK_FENCE(), Ids::DARK_OAK_FENCE],
			[Blocks::JUNGLE_FENCE(), Ids::JUNGLE_FENCE],
			[Blocks::MANGROVE_FENCE(), Ids::MANGROVE_FENCE],
			[Blocks::OAK_FENCE(), Ids::OAK_FENCE],
			[Blocks::PALE_OAK_FENCE(), Ids::PALE_OAK_FENCE],
			[Blocks::SPRUCE_FENCE(), Ids::SPRUCE_FENCE],
			[Blocks::CRIMSON_FENCE(), Ids::CRIMSON_FENCE],
			[Blocks::WARPED_FENCE(), Ids::WARPED_FENCE]
		] as [$block, $id]){
			$this->mapSimple($block, $id);
		}

		//fence gates
		foreach([
			[Blocks::ACACIA_FENCE_GATE(), Ids::ACACIA_FENCE_GATE],
			[Blocks::BIRCH_FENCE_GATE(), Ids::BIRCH_FENCE_GATE],
			[Blocks::CHERRY_FENCE_GATE(), Ids::CHERRY_FENCE_GATE],
			[Blocks::DARK_OAK_FENCE_GATE(), Ids::DARK_OAK_FENCE_GATE],
			[Blocks::JUNGLE_FENCE_GATE(), Ids::JUNGLE_FENCE_GATE],
			[Blocks::MANGROVE_FENCE_GATE(), Ids::MANGROVE_FENCE_GATE],
			[Blocks::OAK_FENCE_GATE(), Ids::FENCE_GATE],
			[Blocks::PALE_OAK_FENCE_GATE(), Ids::PALE_OAK_FENCE_GATE],
			[Blocks::SPRUCE_FENCE_GATE(), Ids::SPRUCE_FENCE_GATE],
			[Blocks::CRIMSON_FENCE_GATE(), Ids::CRIMSON_FENCE_GATE],
			[Blocks::WARPED_FENCE_GATE(), Ids::WARPED_FENCE_GATE]
		] as [$block, $id]){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeFenceGate(...), BlockStateSerializerHelper::encodeFenceGate(...));
		}

		//floor signs
		foreach([
			[Blocks::ACACIA_SIGN(), Ids::ACACIA_STANDING_SIGN],
			[Blocks::BIRCH_SIGN(), Ids::BIRCH_STANDING_SIGN],
			[Blocks::CHERRY_SIGN(), Ids::CHERRY_STANDING_SIGN],
			[Blocks::DARK_OAK_SIGN(), Ids::DARKOAK_STANDING_SIGN],
			[Blocks::JUNGLE_SIGN(), Ids::JUNGLE_STANDING_SIGN],
			[Blocks::MANGROVE_SIGN(), Ids::MANGROVE_STANDING_SIGN],
			[Blocks::OAK_SIGN(), Ids::STANDING_SIGN],
			[Blocks::PALE_OAK_SIGN(), Ids::PALE_OAK_STANDING_SIGN],
			[Blocks::SPRUCE_SIGN(), Ids::SPRUCE_STANDING_SIGN],
			[Blocks::CRIMSON_SIGN(), Ids::CRIMSON_STANDING_SIGN],
			[Blocks::WARPED_SIGN(), Ids::WARPED_STANDING_SIGN]
		] as [$block, $id]){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeFloorSign(...), BlockStateSerializerHelper::encodeFloorSign(...));
		}

		//logs
		foreach([
			[Blocks::ACACIA_LOG(), Ids::ACACIA_LOG, Ids::STRIPPED_ACACIA_LOG],
			[Blocks::BIRCH_LOG(), Ids::BIRCH_LOG, Ids::STRIPPED_BIRCH_LOG],
			[Blocks::CHERRY_LOG(), Ids::CHERRY_LOG, Ids::STRIPPED_CHERRY_LOG],
			[Blocks::DARK_OAK_LOG(), Ids::DARK_OAK_LOG, Ids::STRIPPED_DARK_OAK_LOG],
			[Blocks::JUNGLE_LOG(), Ids::JUNGLE_LOG, Ids::STRIPPED_JUNGLE_LOG],
			[Blocks::MANGROVE_LOG(), Ids::MANGROVE_LOG, Ids::STRIPPED_MANGROVE_LOG],
			[Blocks::OAK_LOG(), Ids::OAK_LOG, Ids::STRIPPED_OAK_LOG],
			[Blocks::PALE_OAK_LOG(), Ids::PALE_OAK_LOG, Ids::STRIPPED_PALE_OAK_LOG],
			[Blocks::SPRUCE_LOG(), Ids::SPRUCE_LOG, Ids::STRIPPED_SPRUCE_LOG],
			[Blocks::CRIMSON_STEM(), Ids::CRIMSON_STEM, Ids::STRIPPED_CRIMSON_STEM],
			[Blocks::WARPED_STEM(), Ids::WARPED_STEM, Ids::STRIPPED_WARPED_STEM]
		] as [$block, $unstrippedId, $strippedId]){
			$this->mapLog($block, $unstrippedId, $strippedId);
		}

		//logs all-sided
		foreach([
			[Blocks::ACACIA_WOOD(), Ids::ACACIA_WOOD, Ids::STRIPPED_ACACIA_WOOD],
			[Blocks::BIRCH_WOOD(), Ids::BIRCH_WOOD, Ids::STRIPPED_BIRCH_WOOD],
			[Blocks::CHERRY_WOOD(), Ids::CHERRY_WOOD, Ids::STRIPPED_CHERRY_WOOD],
			[Blocks::DARK_OAK_WOOD(), Ids::DARK_OAK_WOOD, Ids::STRIPPED_DARK_OAK_WOOD],
			[Blocks::JUNGLE_WOOD(), Ids::JUNGLE_WOOD, Ids::STRIPPED_JUNGLE_WOOD],
			[Blocks::MANGROVE_WOOD(), Ids::MANGROVE_WOOD, Ids::STRIPPED_MANGROVE_WOOD],
			[Blocks::OAK_WOOD(), Ids::OAK_WOOD, Ids::STRIPPED_OAK_WOOD],
			[Blocks::PALE_OAK_WOOD(), Ids::PALE_OAK_WOOD, Ids::STRIPPED_PALE_OAK_WOOD],
			[Blocks::SPRUCE_WOOD(), Ids::SPRUCE_WOOD, Ids::STRIPPED_SPRUCE_WOOD],
			[Blocks::CRIMSON_HYPHAE(), Ids::CRIMSON_HYPHAE, Ids::STRIPPED_CRIMSON_HYPHAE],
			[Blocks::WARPED_HYPHAE(), Ids::WARPED_HYPHAE, Ids::STRIPPED_WARPED_HYPHAE]
		] as [$block, $unstrippedId, $strippedId]){
			$this->mapLog($block, $unstrippedId, $strippedId);
		}

		//planks
		foreach([
			[Blocks::ACACIA_PLANKS(), Ids::ACACIA_PLANKS],
			[Blocks::BIRCH_PLANKS(), Ids::BIRCH_PLANKS],
			[Blocks::CHERRY_PLANKS(), Ids::CHERRY_PLANKS],
			[Blocks::DARK_OAK_PLANKS(), Ids::DARK_OAK_PLANKS],
			[Blocks::JUNGLE_PLANKS(), Ids::JUNGLE_PLANKS],
			[Blocks::MANGROVE_PLANKS(), Ids::MANGROVE_PLANKS],
			[Blocks::OAK_PLANKS(), Ids::OAK_PLANKS],
			[Blocks::PALE_OAK_PLANKS(), Ids::PALE_OAK_PLANKS],
			[Blocks::SPRUCE_PLANKS(), Ids::SPRUCE_PLANKS],
			[Blocks::CRIMSON_PLANKS(), Ids::CRIMSON_PLANKS],
			[Blocks::WARPED_PLANKS(), Ids::WARPED_PLANKS]
		] as [$block, $id]){
			$this->mapSimple($block, $id);
		}

		//pressure plates
		foreach([
			[Blocks::ACACIA_PRESSURE_PLATE(), Ids::ACACIA_PRESSURE_PLATE],
			[Blocks::BIRCH_PRESSURE_PLATE(), Ids::BIRCH_PRESSURE_PLATE],
			[Blocks::CHERRY_PRESSURE_PLATE(), Ids::CHERRY_PRESSURE_PLATE],
			[Blocks::DARK_OAK_PRESSURE_PLATE(), Ids::DARK_OAK_PRESSURE_PLATE],
			[Blocks::JUNGLE_PRESSURE_PLATE(), Ids::JUNGLE_PRESSURE_PLATE],
			[Blocks::MANGROVE_PRESSURE_PLATE(), Ids::MANGROVE_PRESSURE_PLATE],
			[Blocks::OAK_PRESSURE_PLATE(), Ids::WOODEN_PRESSURE_PLATE],
			[Blocks::PALE_OAK_PRESSURE_PLATE(), Ids::PALE_OAK_PRESSURE_PLATE],
			[Blocks::SPRUCE_PRESSURE_PLATE(), Ids::SPRUCE_PRESSURE_PLATE],
			[Blocks::CRIMSON_PRESSURE_PLATE(), Ids::CRIMSON_PRESSURE_PLATE],
			[Blocks::WARPED_PRESSURE_PLATE(), Ids::WARPED_PRESSURE_PLATE]
		] as [$block, $id]){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeSimplePressurePlate(...), BlockStateSerializerHelper::encodeSimplePressurePlate(...));
		}

		//slabs
		foreach([
			[Blocks::ACACIA_SLAB(), Ids::ACACIA_SLAB, Ids::ACACIA_DOUBLE_SLAB],
			[Blocks::BIRCH_SLAB(), Ids::BIRCH_SLAB, Ids::BIRCH_DOUBLE_SLAB],
			[Blocks::CHERRY_SLAB(), Ids::CHERRY_SLAB, Ids::CHERRY_DOUBLE_SLAB],
			[Blocks::DARK_OAK_SLAB(), Ids::DARK_OAK_SLAB, Ids::DARK_OAK_DOUBLE_SLAB],
			[Blocks::JUNGLE_SLAB(), Ids::JUNGLE_SLAB, Ids::JUNGLE_DOUBLE_SLAB],
			[Blocks::MANGROVE_SLAB(), Ids::MANGROVE_SLAB, Ids::MANGROVE_DOUBLE_SLAB],
			[Blocks::OAK_SLAB(), Ids::OAK_SLAB, Ids::OAK_DOUBLE_SLAB],
			[Blocks::PALE_OAK_SLAB(), Ids::PALE_OAK_SLAB, Ids::PALE_OAK_DOUBLE_SLAB],
			[Blocks::SPRUCE_SLAB(), Ids::SPRUCE_SLAB, Ids::SPRUCE_DOUBLE_SLAB],
			[Blocks::CRIMSON_SLAB(), Ids::CRIMSON_SLAB, Ids::CRIMSON_DOUBLE_SLAB],
			[Blocks::WARPED_SLAB(), Ids::WARPED_SLAB, Ids::WARPED_DOUBLE_SLAB]
		] as [$block, $singleId, $doubleId]){
			$this->mapSlab($block, $singleId, $doubleId);
		}

		//stairs
		foreach([
			[Blocks::ACACIA_STAIRS(), Ids::ACACIA_STAIRS],
			[Blocks::BIRCH_STAIRS(), Ids::BIRCH_STAIRS],
			[Blocks::CHERRY_STAIRS(), Ids::CHERRY_STAIRS],
			[Blocks::DARK_OAK_STAIRS(), Ids::DARK_OAK_STAIRS],
			[Blocks::JUNGLE_STAIRS(), Ids::JUNGLE_STAIRS],
			[Blocks::MANGROVE_STAIRS(), Ids::MANGROVE_STAIRS],
			[Blocks::OAK_STAIRS(), Ids::OAK_STAIRS],
			[Blocks::PALE_OAK_STAIRS(), Ids::PALE_OAK_STAIRS],
			[Blocks::SPRUCE_STAIRS(), Ids::SPRUCE_STAIRS],
			[Blocks::CRIMSON_STAIRS(), Ids::CRIMSON_STAIRS],
			[Blocks::WARPED_STAIRS(), Ids::WARPED_STAIRS]
		] as [$block, $id]){
			$this->mapStairs($block, $id);
		}

		//trapdoors
		foreach([
			[Blocks::ACACIA_TRAPDOOR(), Ids::ACACIA_TRAPDOOR],
			[Blocks::BIRCH_TRAPDOOR(), Ids::BIRCH_TRAPDOOR],
			[Blocks::CHERRY_TRAPDOOR(), Ids::CHERRY_TRAPDOOR],
			[Blocks::DARK_OAK_TRAPDOOR(), Ids::DARK_OAK_TRAPDOOR],
			[Blocks::JUNGLE_TRAPDOOR(), Ids::JUNGLE_TRAPDOOR],
			[Blocks::MANGROVE_TRAPDOOR(), Ids::MANGROVE_TRAPDOOR],
			[Blocks::OAK_TRAPDOOR(), Ids::TRAPDOOR],
			[Blocks::PALE_OAK_TRAPDOOR(), Ids::PALE_OAK_TRAPDOOR],
			[Blocks::SPRUCE_TRAPDOOR(), Ids::SPRUCE_TRAPDOOR],
			[Blocks::CRIMSON_TRAPDOOR(), Ids::CRIMSON_TRAPDOOR],
			[Blocks::WARPED_TRAPDOOR(), Ids::WARPED_TRAPDOOR]
		] as [$block, $id]){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeTrapdoor(...), BlockStateSerializerHelper::encodeTrapdoor(...));
		}

		//wall signs
		foreach([
			[Blocks::ACACIA_WALL_SIGN(), Ids::ACACIA_WALL_SIGN],
			[Blocks::BIRCH_WALL_SIGN(), Ids::BIRCH_WALL_SIGN],
			[Blocks::CHERRY_WALL_SIGN(), Ids::CHERRY_WALL_SIGN],
			[Blocks::DARK_OAK_WALL_SIGN(), Ids::DARKOAK_WALL_SIGN],
			[Blocks::JUNGLE_WALL_SIGN(), Ids::JUNGLE_WALL_SIGN],
			[Blocks::MANGROVE_WALL_SIGN(), Ids::MANGROVE_WALL_SIGN],
			[Blocks::OAK_WALL_SIGN(), Ids::WALL_SIGN],
			[Blocks::PALE_OAK_WALL_SIGN(), Ids::PALE_OAK_WALL_SIGN],
			[Blocks::SPRUCE_WALL_SIGN(), Ids::SPRUCE_WALL_SIGN],
			[Blocks::CRIMSON_WALL_SIGN(), Ids::CRIMSON_WALL_SIGN],
			[Blocks::WARPED_WALL_SIGN(), Ids::WARPED_WALL_SIGN]
		] as [$block, $id]){
			$this->mapStdHelper($block, $id, BlockStateDeserializerHelper::decodeWallSign(...), BlockStateSerializerHelper::encodeWallSign(...));
		}
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-param BlockDataModel<TBlock> $unifiedBlockSerializer
	 */
	private function mapModel(BlockDataModel $unifiedBlockSerializer) : void{
		$this->deserializer?->map($unifiedBlockSerializer->getId(), $unifiedBlockSerializer->deserialize(...));
		$this->serializer?->map($unifiedBlockSerializer->getBlockTemplate(), $unifiedBlockSerializer->serialize(...));
	}

	private function register1to1CustomMappings() : void{
		$this->mapModel(
			BlockDataModel::create(Blocks::ACTIVATOR_RAIL(), Ids::ACTIVATOR_RAIL)
				->bool(StateNames::RAIL_DATA_BIT, fn(ActivatorRail $b) => $b->isPowered(), fn(ActivatorRail $b, bool $v) => $b->setPowered($v))
				->int(StateNames::RAIL_DIRECTION, 0, 5, fn(ActivatorRail $b) => $b->getShape(), fn(ActivatorRail $b, int $v) => $b->setShape($v))
		);
		$this->mapModel(
			BlockDataModel::create(Blocks::BAMBOO_SAPLING(), Ids::BAMBOO_SAPLING)
				->bool(StateNames::AGE_BIT, fn(BambooSapling $b) => $b->isReady(), fn(BambooSapling $b, bool $v) => $b->setReady($v))
		);
		$this->mapModel(
			BlockDataModel::create(Blocks::BEDROCK(), Ids::BEDROCK)
				->bool(StateNames::INFINIBURN_BIT, fn(Bedrock $b) => $b->burnsForever(), fn(Bedrock $b, bool $v) => $b->setBurnsForever($v))
		);
		$this->mapModel(
			BlockDataModel::create(Blocks::CACTUS(), Ids::CACTUS)
				->int(StateNames::AGE, 0, 15, fn(Cactus $b) => $b->getAge(), fn(Cactus $b, int $v) => $b->setAge($v))
		);
		$this->mapModel(
			BlockDataModel::create(Blocks::TWISTING_VINES(), Ids::TWISTING_VINES)
				->int(StateNames::TWISTING_VINES_AGE, 0, 25, fn(NetherVines $b) => $b->getAge(), fn(NetherVines $b, int $v) => $b->setAge($v))
		);
		$this->mapModel(
			BlockDataModel::create(Blocks::WEEPING_VINES(), Ids::WEEPING_VINES)
				->int(StateNames::WEEPING_VINES_AGE, 0, 25,  fn(NetherVines $b) => $b->getAge(), fn(NetherVines $b, int $v) => $b->setAge($v))
		);
		$this->mapModel(
			BlockDataModel::create(Blocks::WEIGHTED_PRESSURE_PLATE_HEAVY(), Ids::HEAVY_WEIGHTED_PRESSURE_PLATE)
				->int(StateNames::REDSTONE_SIGNAL, 0, 15, fn(WeightedPressurePlate $b) => $b->getOutputSignalStrength(), fn(WeightedPressurePlate $b, int $v) => $b->setOutputSignalStrength($v))
		);
		$this->mapModel(
			BlockDataModel::create(Blocks::WEIGHTED_PRESSURE_PLATE_LIGHT(), Ids::LIGHT_WEIGHTED_PRESSURE_PLATE)
				->int(StateNames::REDSTONE_SIGNAL, 0, 15, fn(WeightedPressurePlate $b) => $b->getOutputSignalStrength(), fn(WeightedPressurePlate $b, int $v) => $b->setOutputSignalStrength($v))
		);
	}
}
