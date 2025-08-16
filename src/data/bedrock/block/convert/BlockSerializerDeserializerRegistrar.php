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

use pocketmine\block\Block;
use pocketmine\block\utils\Colored;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateReader as Reader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;

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
	}

	private function mapSimple(Block $block, string $id) : void{
		$this->deserializer?->mapSimple($id, fn() => clone $block);
		$this->serializer?->mapSimple($block, $id);
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
	 * @phpstan-param TBlock $block
	 * @phpstan-param \Closure(TBlock, Reader) : TBlock $readHelper
	 * @phpstan-param \Closure(TBlock, Writer) : Writer $writeHelper
	 */
	private function mapStdHelper(Block $block, string $id, \Closure $readHelper, \Closure $writeHelper) : void{
		$this->deserializer?->map($id, fn(Reader $in) => $readHelper(clone $block, $in));
		$this->serializer?->map($block, fn(Block $block) => $writeHelper($block, new Writer($id)));
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
}
