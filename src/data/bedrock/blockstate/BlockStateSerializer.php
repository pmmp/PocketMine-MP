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

namespace pocketmine\data\bedrock\blockstate;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\blockstate\BlockTypeNames as Ids;
use pocketmine\nbt\tag\CompoundTag;

final class BlockStateSerializer{
	/**
	 * These callables actually accept Block, but for the sake of type completeness, it has to be never, since we can't
	 * describe the bottom type of a type hierarchy only containing Block.
	 *
	 * @var \Closure[][]
	 * @phpstan-var array<int, array<class-string, \Closure(never) : BlockStateWriter>>
	 */
	private array $serializers = [];

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType                         $block
	 * @phpstan-param \Closure(TBlockType) : BlockStateWriter $serializer
	 */
	private function map(Block $block, \Closure $serializer) : void{
		$this->serializers[$block->getTypeId()][get_class($block)] = $serializer;
	}

	public function __construct(){
		$this->map(VanillaBlocks::AIR(), fn() => new BlockStateWriter(Ids::AIR));
		$this->map(VanillaBlocks::BARRIER(), fn() => new BlockStateWriter(Ids::BARRIER));
		$this->map(VanillaBlocks::BEACON(), fn() => new BlockStateWriter(Ids::BEACON));
		$this->map(VanillaBlocks::BLUE_ICE(), fn() => new BlockStateWriter(Ids::BLUE_ICE));
		$this->map(VanillaBlocks::BOOKSHELF(), fn() => new BlockStateWriter(Ids::BOOKSHELF));
		$this->map(VanillaBlocks::BRICKS(), fn() => new BlockStateWriter(Ids::BRICK_BLOCK));
		$this->map(VanillaBlocks::BROWN_MUSHROOM(), fn() => new BlockStateWriter(Ids::BROWN_MUSHROOM));
		$this->map(VanillaBlocks::CHEMICAL_HEAT(), fn() => new BlockStateWriter(Ids::CHEMICAL_HEAT));
		$this->map(VanillaBlocks::CLAY(), fn() => new BlockStateWriter(Ids::CLAY));
		$this->map(VanillaBlocks::COAL(), fn() => new BlockStateWriter(Ids::COAL_BLOCK));
		$this->map(VanillaBlocks::COAL_ORE(), fn() => new BlockStateWriter(Ids::COAL_ORE));
		$this->map(VanillaBlocks::COBBLESTONE(), fn() => new BlockStateWriter(Ids::COBBLESTONE));
		$this->map(VanillaBlocks::CRAFTING_TABLE(), fn() => new BlockStateWriter(Ids::CRAFTING_TABLE));
		$this->map(VanillaBlocks::DEAD_BUSH(), fn() => new BlockStateWriter(Ids::DEADBUSH));
		$this->map(VanillaBlocks::DIAMOND(), fn() => new BlockStateWriter(Ids::DIAMOND_BLOCK));
		$this->map(VanillaBlocks::DIAMOND_ORE(), fn() => new BlockStateWriter(Ids::DIAMOND_ORE));
		$this->map(VanillaBlocks::DRAGON_EGG(), fn() => new BlockStateWriter(Ids::DRAGON_EGG));
		$this->map(VanillaBlocks::DRIED_KELP(), fn() => new BlockStateWriter(Ids::DRIED_KELP_BLOCK));
		$this->map(VanillaBlocks::ELEMENT_ZERO(), fn() => new BlockStateWriter(Ids::ELEMENT_0));
		$this->map(VanillaBlocks::ELEMENT_HYDROGEN(), fn() => new BlockStateWriter(Ids::ELEMENT_1));
		$this->map(VanillaBlocks::ELEMENT_NEON(), fn() => new BlockStateWriter(Ids::ELEMENT_10));
		$this->map(VanillaBlocks::ELEMENT_FERMIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_100));
		$this->map(VanillaBlocks::ELEMENT_MENDELEVIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_101));
		$this->map(VanillaBlocks::ELEMENT_NOBELIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_102));
		$this->map(VanillaBlocks::ELEMENT_LAWRENCIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_103));
		$this->map(VanillaBlocks::ELEMENT_RUTHERFORDIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_104));
		$this->map(VanillaBlocks::ELEMENT_DUBNIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_105));
		$this->map(VanillaBlocks::ELEMENT_SEABORGIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_106));
		$this->map(VanillaBlocks::ELEMENT_BOHRIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_107));
		$this->map(VanillaBlocks::ELEMENT_HASSIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_108));
		$this->map(VanillaBlocks::ELEMENT_MEITNERIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_109));
		$this->map(VanillaBlocks::ELEMENT_SODIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_11));
		$this->map(VanillaBlocks::ELEMENT_DARMSTADTIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_110));
		$this->map(VanillaBlocks::ELEMENT_ROENTGENIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_111));
		$this->map(VanillaBlocks::ELEMENT_COPERNICIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_112));
		$this->map(VanillaBlocks::ELEMENT_NIHONIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_113));
		$this->map(VanillaBlocks::ELEMENT_FLEROVIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_114));
		$this->map(VanillaBlocks::ELEMENT_MOSCOVIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_115));
		$this->map(VanillaBlocks::ELEMENT_LIVERMORIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_116));
		$this->map(VanillaBlocks::ELEMENT_TENNESSINE(), fn() => new BlockStateWriter(Ids::ELEMENT_117));
		$this->map(VanillaBlocks::ELEMENT_OGANESSON(), fn() => new BlockStateWriter(Ids::ELEMENT_118));
		$this->map(VanillaBlocks::ELEMENT_MAGNESIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_12));
		$this->map(VanillaBlocks::ELEMENT_ALUMINUM(), fn() => new BlockStateWriter(Ids::ELEMENT_13));
		$this->map(VanillaBlocks::ELEMENT_SILICON(), fn() => new BlockStateWriter(Ids::ELEMENT_14));
		$this->map(VanillaBlocks::ELEMENT_PHOSPHORUS(), fn() => new BlockStateWriter(Ids::ELEMENT_15));
		$this->map(VanillaBlocks::ELEMENT_SULFUR(), fn() => new BlockStateWriter(Ids::ELEMENT_16));
		$this->map(VanillaBlocks::ELEMENT_CHLORINE(), fn() => new BlockStateWriter(Ids::ELEMENT_17));
		$this->map(VanillaBlocks::ELEMENT_ARGON(), fn() => new BlockStateWriter(Ids::ELEMENT_18));
		$this->map(VanillaBlocks::ELEMENT_POTASSIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_19));
		$this->map(VanillaBlocks::ELEMENT_HELIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_2));
		$this->map(VanillaBlocks::ELEMENT_CALCIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_20));
		$this->map(VanillaBlocks::ELEMENT_SCANDIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_21));
		$this->map(VanillaBlocks::ELEMENT_TITANIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_22));
		$this->map(VanillaBlocks::ELEMENT_VANADIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_23));
		$this->map(VanillaBlocks::ELEMENT_CHROMIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_24));
		$this->map(VanillaBlocks::ELEMENT_MANGANESE(), fn() => new BlockStateWriter(Ids::ELEMENT_25));
		$this->map(VanillaBlocks::ELEMENT_IRON(), fn() => new BlockStateWriter(Ids::ELEMENT_26));
		$this->map(VanillaBlocks::ELEMENT_COBALT(), fn() => new BlockStateWriter(Ids::ELEMENT_27));
		$this->map(VanillaBlocks::ELEMENT_NICKEL(), fn() => new BlockStateWriter(Ids::ELEMENT_28));
		$this->map(VanillaBlocks::ELEMENT_COPPER(), fn() => new BlockStateWriter(Ids::ELEMENT_29));
		$this->map(VanillaBlocks::ELEMENT_LITHIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_3));
		$this->map(VanillaBlocks::ELEMENT_ZINC(), fn() => new BlockStateWriter(Ids::ELEMENT_30));
		$this->map(VanillaBlocks::ELEMENT_GALLIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_31));
		$this->map(VanillaBlocks::ELEMENT_GERMANIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_32));
		$this->map(VanillaBlocks::ELEMENT_ARSENIC(), fn() => new BlockStateWriter(Ids::ELEMENT_33));
		$this->map(VanillaBlocks::ELEMENT_SELENIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_34));
		$this->map(VanillaBlocks::ELEMENT_BROMINE(), fn() => new BlockStateWriter(Ids::ELEMENT_35));
		$this->map(VanillaBlocks::ELEMENT_KRYPTON(), fn() => new BlockStateWriter(Ids::ELEMENT_36));
		$this->map(VanillaBlocks::ELEMENT_RUBIDIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_37));
		$this->map(VanillaBlocks::ELEMENT_STRONTIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_38));
		$this->map(VanillaBlocks::ELEMENT_YTTRIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_39));
		$this->map(VanillaBlocks::ELEMENT_BERYLLIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_4));
		$this->map(VanillaBlocks::ELEMENT_ZIRCONIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_40));
		$this->map(VanillaBlocks::ELEMENT_NIOBIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_41));
		$this->map(VanillaBlocks::ELEMENT_MOLYBDENUM(), fn() => new BlockStateWriter(Ids::ELEMENT_42));
		$this->map(VanillaBlocks::ELEMENT_TECHNETIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_43));
		$this->map(VanillaBlocks::ELEMENT_RUTHENIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_44));
		$this->map(VanillaBlocks::ELEMENT_RHODIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_45));
		$this->map(VanillaBlocks::ELEMENT_PALLADIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_46));
		$this->map(VanillaBlocks::ELEMENT_SILVER(), fn() => new BlockStateWriter(Ids::ELEMENT_47));
		$this->map(VanillaBlocks::ELEMENT_CADMIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_48));
		$this->map(VanillaBlocks::ELEMENT_INDIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_49));
		$this->map(VanillaBlocks::ELEMENT_BORON(), fn() => new BlockStateWriter(Ids::ELEMENT_5));
		$this->map(VanillaBlocks::ELEMENT_TIN(), fn() => new BlockStateWriter(Ids::ELEMENT_50));
		$this->map(VanillaBlocks::ELEMENT_ANTIMONY(), fn() => new BlockStateWriter(Ids::ELEMENT_51));
		$this->map(VanillaBlocks::ELEMENT_TELLURIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_52));
		$this->map(VanillaBlocks::ELEMENT_IODINE(), fn() => new BlockStateWriter(Ids::ELEMENT_53));
		$this->map(VanillaBlocks::ELEMENT_XENON(), fn() => new BlockStateWriter(Ids::ELEMENT_54));
		$this->map(VanillaBlocks::ELEMENT_CESIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_55));
		$this->map(VanillaBlocks::ELEMENT_BARIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_56));
		$this->map(VanillaBlocks::ELEMENT_LANTHANUM(), fn() => new BlockStateWriter(Ids::ELEMENT_57));
		$this->map(VanillaBlocks::ELEMENT_CERIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_58));
		$this->map(VanillaBlocks::ELEMENT_PRASEODYMIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_59));
		$this->map(VanillaBlocks::ELEMENT_CARBON(), fn() => new BlockStateWriter(Ids::ELEMENT_6));
		$this->map(VanillaBlocks::ELEMENT_NEODYMIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_60));
		$this->map(VanillaBlocks::ELEMENT_PROMETHIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_61));
		$this->map(VanillaBlocks::ELEMENT_SAMARIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_62));
		$this->map(VanillaBlocks::ELEMENT_EUROPIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_63));
		$this->map(VanillaBlocks::ELEMENT_GADOLINIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_64));
		$this->map(VanillaBlocks::ELEMENT_TERBIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_65));
		$this->map(VanillaBlocks::ELEMENT_DYSPROSIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_66));
		$this->map(VanillaBlocks::ELEMENT_HOLMIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_67));
		$this->map(VanillaBlocks::ELEMENT_ERBIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_68));
		$this->map(VanillaBlocks::ELEMENT_THULIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_69));
		$this->map(VanillaBlocks::ELEMENT_NITROGEN(), fn() => new BlockStateWriter(Ids::ELEMENT_7));
		$this->map(VanillaBlocks::ELEMENT_YTTERBIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_70));
		$this->map(VanillaBlocks::ELEMENT_LUTETIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_71));
		$this->map(VanillaBlocks::ELEMENT_HAFNIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_72));
		$this->map(VanillaBlocks::ELEMENT_TANTALUM(), fn() => new BlockStateWriter(Ids::ELEMENT_73));
		$this->map(VanillaBlocks::ELEMENT_TUNGSTEN(), fn() => new BlockStateWriter(Ids::ELEMENT_74));
		$this->map(VanillaBlocks::ELEMENT_RHENIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_75));
		$this->map(VanillaBlocks::ELEMENT_OSMIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_76));
		$this->map(VanillaBlocks::ELEMENT_IRIDIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_77));
		$this->map(VanillaBlocks::ELEMENT_PLATINUM(), fn() => new BlockStateWriter(Ids::ELEMENT_78));
		$this->map(VanillaBlocks::ELEMENT_GOLD(), fn() => new BlockStateWriter(Ids::ELEMENT_79));
		$this->map(VanillaBlocks::ELEMENT_OXYGEN(), fn() => new BlockStateWriter(Ids::ELEMENT_8));
		$this->map(VanillaBlocks::ELEMENT_MERCURY(), fn() => new BlockStateWriter(Ids::ELEMENT_80));
		$this->map(VanillaBlocks::ELEMENT_THALLIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_81));
		$this->map(VanillaBlocks::ELEMENT_LEAD(), fn() => new BlockStateWriter(Ids::ELEMENT_82));
		$this->map(VanillaBlocks::ELEMENT_BISMUTH(), fn() => new BlockStateWriter(Ids::ELEMENT_83));
		$this->map(VanillaBlocks::ELEMENT_POLONIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_84));
		$this->map(VanillaBlocks::ELEMENT_ASTATINE(), fn() => new BlockStateWriter(Ids::ELEMENT_85));
		$this->map(VanillaBlocks::ELEMENT_RADON(), fn() => new BlockStateWriter(Ids::ELEMENT_86));
		$this->map(VanillaBlocks::ELEMENT_FRANCIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_87));
		$this->map(VanillaBlocks::ELEMENT_RADIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_88));
		$this->map(VanillaBlocks::ELEMENT_ACTINIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_89));
		$this->map(VanillaBlocks::ELEMENT_FLUORINE(), fn() => new BlockStateWriter(Ids::ELEMENT_9));
		$this->map(VanillaBlocks::ELEMENT_THORIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_90));
		$this->map(VanillaBlocks::ELEMENT_PROTACTINIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_91));
		$this->map(VanillaBlocks::ELEMENT_URANIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_92));
		$this->map(VanillaBlocks::ELEMENT_NEPTUNIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_93));
		$this->map(VanillaBlocks::ELEMENT_PLUTONIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_94));
		$this->map(VanillaBlocks::ELEMENT_AMERICIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_95));
		$this->map(VanillaBlocks::ELEMENT_CURIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_96));
		$this->map(VanillaBlocks::ELEMENT_BERKELIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_97));
		$this->map(VanillaBlocks::ELEMENT_CALIFORNIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_98));
		$this->map(VanillaBlocks::ELEMENT_EINSTEINIUM(), fn() => new BlockStateWriter(Ids::ELEMENT_99));
		$this->map(VanillaBlocks::EMERALD(), fn() => new BlockStateWriter(Ids::EMERALD_BLOCK));
		$this->map(VanillaBlocks::EMERALD_ORE(), fn() => new BlockStateWriter(Ids::EMERALD_ORE));
		$this->map(VanillaBlocks::ENCHANTING_TABLE(), fn() => new BlockStateWriter(Ids::ENCHANTING_TABLE));
		$this->map(VanillaBlocks::END_STONE_BRICKS(), fn() => new BlockStateWriter(Ids::END_BRICKS));
		$this->map(VanillaBlocks::END_STONE(), fn() => new BlockStateWriter(Ids::END_STONE));
		$this->map(VanillaBlocks::FLETCHING_TABLE(), fn() => new BlockStateWriter(Ids::FLETCHING_TABLE));
		$this->map(VanillaBlocks::GLASS(), fn() => new BlockStateWriter(Ids::GLASS));
		$this->map(VanillaBlocks::GLASS_PANE(), fn() => new BlockStateWriter(Ids::GLASS_PANE));
		$this->map(VanillaBlocks::GLOWING_OBSIDIAN(), fn() => new BlockStateWriter(Ids::GLOWINGOBSIDIAN));
		$this->map(VanillaBlocks::GLOWSTONE(), fn() => new BlockStateWriter(Ids::GLOWSTONE));
		$this->map(VanillaBlocks::GOLD(), fn() => new BlockStateWriter(Ids::GOLD_BLOCK));
		$this->map(VanillaBlocks::GOLD_ORE(), fn() => new BlockStateWriter(Ids::GOLD_ORE));
		$this->map(VanillaBlocks::GRASS(), fn() => new BlockStateWriter(Ids::GRASS));
		$this->map(VanillaBlocks::GRASS_PATH(), fn() => new BlockStateWriter(Ids::GRASS_PATH));
		$this->map(VanillaBlocks::GRAVEL(), fn() => new BlockStateWriter(Ids::GRAVEL));
		$this->map(VanillaBlocks::HARDENED_GLASS(), fn() => new BlockStateWriter(Ids::HARD_GLASS));
		$this->map(VanillaBlocks::HARDENED_GLASS_PANE(), fn() => new BlockStateWriter(Ids::HARD_GLASS_PANE));
		$this->map(VanillaBlocks::HARDENED_CLAY(), fn() => new BlockStateWriter(Ids::HARDENED_CLAY));
		$this->map(VanillaBlocks::ICE(), fn() => new BlockStateWriter(Ids::ICE));
		$this->map(VanillaBlocks::INFO_UPDATE(), fn() => new BlockStateWriter(Ids::INFO_UPDATE));
		$this->map(VanillaBlocks::INFO_UPDATE2(), fn() => new BlockStateWriter(Ids::INFO_UPDATE2));
		$this->map(VanillaBlocks::INVISIBLE_BEDROCK(), fn() => new BlockStateWriter(Ids::INVISIBLEBEDROCK));
		$this->map(VanillaBlocks::IRON_BARS(), fn() => new BlockStateWriter(Ids::IRON_BARS));
		$this->map(VanillaBlocks::IRON(), fn() => new BlockStateWriter(Ids::IRON_BLOCK));
		$this->map(VanillaBlocks::IRON_ORE(), fn() => new BlockStateWriter(Ids::IRON_ORE));
		$this->map(VanillaBlocks::JUKEBOX(), fn() => new BlockStateWriter(Ids::JUKEBOX));
		$this->map(VanillaBlocks::LAPIS_LAZULI(), fn() => new BlockStateWriter(Ids::LAPIS_BLOCK));
		$this->map(VanillaBlocks::LAPIS_LAZULI_ORE(), fn() => new BlockStateWriter(Ids::LAPIS_ORE));
		$this->map(VanillaBlocks::REDSTONE_LAMP(), fn() => new BlockStateWriter(Ids::LIT_REDSTONE_LAMP));
		$this->map(VanillaBlocks::REDSTONE_ORE(), fn() => new BlockStateWriter(Ids::LIT_REDSTONE_ORE));
		$this->map(VanillaBlocks::MAGMA(), fn() => new BlockStateWriter(Ids::MAGMA));
		$this->map(VanillaBlocks::MELON(), fn() => new BlockStateWriter(Ids::MELON_BLOCK));
		$this->map(VanillaBlocks::MONSTER_SPAWNER(), fn() => new BlockStateWriter(Ids::MOB_SPAWNER));
		$this->map(VanillaBlocks::MOSSY_COBBLESTONE(), fn() => new BlockStateWriter(Ids::MOSSY_COBBLESTONE));
		$this->map(VanillaBlocks::MYCELIUM(), fn() => new BlockStateWriter(Ids::MYCELIUM));
		$this->map(VanillaBlocks::NETHER_BRICKS(), fn() => new BlockStateWriter(Ids::NETHER_BRICK));
		$this->map(VanillaBlocks::NETHER_BRICK_FENCE(), fn() => new BlockStateWriter(Ids::NETHER_BRICK_FENCE));
		$this->map(VanillaBlocks::NETHER_WART_BLOCK(), fn() => new BlockStateWriter(Ids::NETHER_WART_BLOCK));
		$this->map(VanillaBlocks::NETHERRACK(), fn() => new BlockStateWriter(Ids::NETHERRACK));
		$this->map(VanillaBlocks::NETHER_REACTOR_CORE(), fn() => new BlockStateWriter(Ids::NETHERREACTOR));
		$this->map(VanillaBlocks::NOTE_BLOCK(), fn() => new BlockStateWriter(Ids::NOTEBLOCK));
		$this->map(VanillaBlocks::OBSIDIAN(), fn() => new BlockStateWriter(Ids::OBSIDIAN));
		$this->map(VanillaBlocks::PACKED_ICE(), fn() => new BlockStateWriter(Ids::PACKED_ICE));
		$this->map(VanillaBlocks::PODZOL(), fn() => new BlockStateWriter(Ids::PODZOL));
		$this->map(VanillaBlocks::NETHER_QUARTZ_ORE(), fn() => new BlockStateWriter(Ids::QUARTZ_ORE));
		$this->map(VanillaBlocks::RED_MUSHROOM(), fn() => new BlockStateWriter(Ids::RED_MUSHROOM));
		$this->map(VanillaBlocks::RED_NETHER_BRICKS(), fn() => new BlockStateWriter(Ids::RED_NETHER_BRICK));
		$this->map(VanillaBlocks::REDSTONE(), fn() => new BlockStateWriter(Ids::REDSTONE_BLOCK));
		$this->map(VanillaBlocks::REDSTONE_LAMP(), fn() => new BlockStateWriter(Ids::REDSTONE_LAMP));
		$this->map(VanillaBlocks::REDSTONE_ORE(), fn() => new BlockStateWriter(Ids::REDSTONE_ORE));
		$this->map(VanillaBlocks::RESERVED6(), fn() => new BlockStateWriter(Ids::RESERVED6));
		$this->map(VanillaBlocks::SEA_LANTERN(), fn() => new BlockStateWriter(Ids::SEALANTERN));
		$this->map(VanillaBlocks::SLIME(), fn() => new BlockStateWriter(Ids::SLIME));
		$this->map(VanillaBlocks::SMOOTH_STONE(), fn() => new BlockStateWriter(Ids::SMOOTH_STONE));
		$this->map(VanillaBlocks::SNOW(), fn() => new BlockStateWriter(Ids::SNOW));
		$this->map(VanillaBlocks::SOUL_SAND(), fn() => new BlockStateWriter(Ids::SOUL_SAND));
		$this->map(VanillaBlocks::LEGACY_STONECUTTER(), fn() => new BlockStateWriter(Ids::STONECUTTER));
		$this->map(VanillaBlocks::SHULKER_BOX(), fn() => new BlockStateWriter(Ids::UNDYED_SHULKER_BOX));
		$this->map(VanillaBlocks::LILY_PAD(), fn() => new BlockStateWriter(Ids::WATERLILY));
		$this->map(VanillaBlocks::COBWEB(), fn() => new BlockStateWriter(Ids::WEB));
		$this->map(VanillaBlocks::DANDELION(), fn() => new BlockStateWriter(Ids::YELLOW_FLOWER));
		//$this->map(VanillaBlocks::ACACIA_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ACACIA_BUTTON())
			 * TODO: implement (de)serializer
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::ACACIA_DOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ACACIA_DOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::ACACIA_FENCE_GATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ACACIA_FENCE_GATE())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::ACACIA_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ACACIA_PRESSURE_PLATE())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::ACACIA_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ACACIA_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::ACACIA_STANDING_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ACACIA_SIGN())
			 * TODO: implement (de)serializer
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::ACACIA_TRAPDOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ACACIA_TRAPDOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::ACACIA_WALL_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ACACIA_WALL_SIGN())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::ACTIVATOR_RAIL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ACTIVATOR_RAIL())
			 * TODO: implement (de)serializer
			 * rail_data_bit (ByteTag) = 0, 1
			 * rail_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::AMETHYST_CLUSTER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::ANDESITE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ANDESITE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::ANVIL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ANVIL())
			 * TODO: implement (de)serializer
			 * damage (StringTag) = broken, slightly_damaged, undamaged, very_damaged
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::AZALEA_LEAVES(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * persistent_bit (ByteTag) = 0, 1
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::AZALEA_LEAVES_FLOWERED(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * persistent_bit (ByteTag) = 0, 1
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BAMBOO(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BAMBOO())
			 * TODO: implement (de)serializer
			 * age_bit (ByteTag) = 0, 1
			 * bamboo_leaf_size (StringTag) = large_leaves, no_leaves, small_leaves
			 * bamboo_stalk_thickness (StringTag) = thick, thin
			 */
		//});
		//$this->map(VanillaBlocks::BAMBOO_SAPLING(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BAMBOO_SAPLING())
			 * TODO: implement (de)serializer
			 * age_bit (ByteTag) = 0, 1
			 * sapling_type (StringTag) = acacia, birch, dark_oak, jungle, oak, spruce
			 */
		//});
		//$this->map(VanillaBlocks::BARREL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BARREL())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BASALT(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::BED(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BED())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * head_piece_bit (ByteTag) = 0, 1
			 * occupied_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BEDROCK(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BEDROCK())
			 * TODO: implement (de)serializer
			 * infiniburn_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BEEHIVE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * honey_level (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::BEETROOT(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BEETROOTS())
			 * TODO: implement (de)serializer
			 * growth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 */
		//});
		//$this->map(VanillaBlocks::BEE_NEST(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * honey_level (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::BELL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BELL())
			 * TODO: implement (de)serializer
			 * attachment (StringTag) = hanging, multiple, side, standing
			 * direction (IntTag) = 0, 1, 2, 3
			 * toggle_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BIG_DRIPLEAF(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * big_dripleaf_head (ByteTag) = 0, 1
			 * big_dripleaf_tilt (StringTag) = full_tilt, none, partial_tilt, unstable
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::BIRCH_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BIRCH_BUTTON())
			 * TODO: implement (de)serializer
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::BIRCH_DOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BIRCH_DOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BIRCH_FENCE_GATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BIRCH_FENCE_GATE())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BIRCH_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BIRCH_PRESSURE_PLATE())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::BIRCH_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BIRCH_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::BIRCH_STANDING_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BIRCH_SIGN())
			 * TODO: implement (de)serializer
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::BIRCH_TRAPDOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BIRCH_TRAPDOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BIRCH_WALL_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BIRCH_WALL_SIGN())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::BLACKSTONE_DOUBLE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BLACKSTONE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BLACKSTONE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::BLACKSTONE_WALL(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BLACK_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BLACK_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BLACK_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BLACK_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::BLAST_FURNACE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BLAST_FURNACE())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::BLUE_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BLUE_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BLUE_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BLUE_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::BONE_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BONE_BLOCK())
			 * TODO: implement (de)serializer
			 * deprecated (IntTag) = 0, 1, 2, 3
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::BORDER_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BREWING_STAND(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BREWING_STAND())
			 * TODO: implement (de)serializer
			 * brewing_stand_slot_a_bit (ByteTag) = 0, 1
			 * brewing_stand_slot_b_bit (ByteTag) = 0, 1
			 * brewing_stand_slot_c_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BRICK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BRICK_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::BROWN_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BROWN_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::BROWN_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BROWN_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::BROWN_MUSHROOM_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BROWN_MUSHROOM_BLOCK())
			 * TODO: implement (de)serializer
			 * huge_mushroom_bits (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::BUBBLE_COLUMN(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * drag_down (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CACTUS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CACTUS())
			 * TODO: implement (de)serializer
			 * age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CAKE())
			 * TODO: implement (de)serializer
			 * bite_counter (IntTag) = 0, 1, 2, 3, 4, 5, 6
			 */
		//});
		//$this->map(VanillaBlocks::CAMPFIRE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * extinguished (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CARPET(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CARPET())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::CARROTS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CARROTS())
			 * TODO: implement (de)serializer
			 * growth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 */
		//});
		//$this->map(VanillaBlocks::CARVED_PUMPKIN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CARVED_PUMPKIN())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::CAULDRON(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * cauldron_liquid (StringTag) = lava, powder_snow, water
			 * fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6
			 */
		//});
		//$this->map(VanillaBlocks::CAVE_VINES(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * growing_plant_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(VanillaBlocks::CAVE_VINES_BODY_WITH_BERRIES(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * growing_plant_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(VanillaBlocks::CAVE_VINES_HEAD_WITH_BERRIES(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * growing_plant_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(VanillaBlocks::CHAIN(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::CHAIN_COMMAND_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::CHEMISTRY_TABLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * chemistry_table_type (StringTag) = compound_creator, element_constructor, lab_table, material_reducer
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::CHEST(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CHEST())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::CHORUS_FLOWER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * age (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::COBBLED_DEEPSLATE_DOUBLE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::COBBLED_DEEPSLATE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::COBBLED_DEEPSLATE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::COBBLED_DEEPSLATE_WALL(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::COBBLESTONE_WALL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::COBBLESTONE_WALL())
			 * TODO: implement (de)serializer
			 * wall_block_type (StringTag) = andesite, brick, cobblestone, diorite, end_brick, granite, mossy_cobblestone, mossy_stone_brick, nether_brick, prismarine, red_nether_brick, red_sandstone, sandstone, stone_brick
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::COCOA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::COCOA_POD())
			 * TODO: implement (de)serializer
			 * age (IntTag) = 0, 1, 2
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::COLORED_TORCH_BP(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * color_bit (ByteTag) = 0, 1
			 * torch_facing_direction (StringTag) = east, north, south, top, unknown, west
			 */
		//});
		//$this->map(VanillaBlocks::COLORED_TORCH_RG(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * color_bit (ByteTag) = 0, 1
			 * torch_facing_direction (StringTag) = east, north, south, top, unknown, west
			 */
		//});
		//$this->map(VanillaBlocks::COMMAND_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::COMPOSTER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * composter_fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8
			 */
		//});
		//$this->map(VanillaBlocks::CONCRETE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CONCRETE())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::CONCRETEPOWDER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CONCRETE_POWDER())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::CORAL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CORAL())
			 * TODO: implement (de)serializer
			 * coral_color (StringTag) = blue, pink, purple, red, yellow
			 * dead_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CORAL_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CORAL_BLOCK())
			 * TODO: implement (de)serializer
			 * coral_color (StringTag) = blue, pink, purple, red, yellow
			 * dead_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CORAL_FAN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CORAL_FAN())
			 * TODO: implement (de)serializer
			 * coral_color (StringTag) = blue, pink, purple, red, yellow
			 * coral_fan_direction (IntTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CORAL_FAN_DEAD(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * coral_color (StringTag) = blue, pink, purple, red, yellow
			 * coral_fan_direction (IntTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CORAL_FAN_HANG(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WALL_CORAL_FAN())
			 * TODO: implement (de)serializer
			 * coral_direction (IntTag) = 0, 1, 2, 3
			 * coral_hang_type_bit (ByteTag) = 0, 1
			 * dead_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CORAL_FAN_HANG2(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WALL_CORAL_FAN())
			 * TODO: implement (de)serializer
			 * coral_direction (IntTag) = 0, 1, 2, 3
			 * coral_hang_type_bit (ByteTag) = 0, 1
			 * dead_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CORAL_FAN_HANG3(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WALL_CORAL_FAN())
			 * TODO: implement (de)serializer
			 * coral_direction (IntTag) = 0, 1, 2, 3
			 * coral_hang_type_bit (ByteTag) = 0, 1
			 * dead_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_DOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_DOUBLE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_FENCE_GATE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_HYPHAE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_STANDING_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_STEM(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_TRAPDOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CRIMSON_WALL_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CUT_COPPER_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::CYAN_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CYAN_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::CYAN_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::CYAN_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::DARKOAK_STANDING_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DARK_OAK_SIGN())
			 * TODO: implement (de)serializer
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::DARKOAK_WALL_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DARK_OAK_WALL_SIGN())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::DARK_OAK_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DARK_OAK_BUTTON())
			 * TODO: implement (de)serializer
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::DARK_OAK_DOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DARK_OAK_DOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DARK_OAK_FENCE_GATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DARK_OAK_FENCE_GATE())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DARK_OAK_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DARK_OAK_PRESSURE_PLATE())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::DARK_OAK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DARK_OAK_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::DARK_OAK_TRAPDOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DARK_OAK_TRAPDOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DARK_PRISMARINE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DARK_PRISMARINE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::DAYLIGHT_DETECTOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DAYLIGHT_SENSOR())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::DAYLIGHT_DETECTOR_INVERTED(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DAYLIGHT_SENSOR())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::DEEPSLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::DEEPSLATE_BRICK_DOUBLE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DEEPSLATE_BRICK_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DEEPSLATE_BRICK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::DEEPSLATE_BRICK_WALL(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DEEPSLATE_TILE_DOUBLE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DEEPSLATE_TILE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DEEPSLATE_TILE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::DEEPSLATE_TILE_WALL(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DETECTOR_RAIL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DETECTOR_RAIL())
			 * TODO: implement (de)serializer
			 * rail_data_bit (ByteTag) = 0, 1
			 * rail_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::DIORITE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DIORITE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::DIRT(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DIRT())
			 * TODO: implement (de)serializer
			 * dirt_type (StringTag) = coarse, normal
			 */
		//});
		//$this->map(VanillaBlocks::DISPENSER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * triggered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DOUBLE_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DOUBLE_PLANT(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * double_plant_type (StringTag) = fern, grass, paeonia, rose, sunflower, syringa
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DOUBLE_STONE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * stone_slab_type (StringTag) = brick, cobblestone, nether_brick, quartz, sandstone, smooth_stone, stone_brick, wood
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DOUBLE_STONE_SLAB2(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * stone_slab_type_2 (StringTag) = mossy_cobblestone, prismarine_brick, prismarine_dark, prismarine_rough, purpur, red_nether_brick, red_sandstone, smooth_sandstone
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DOUBLE_STONE_SLAB3(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * stone_slab_type_3 (StringTag) = andesite, diorite, end_stone_brick, granite, polished_andesite, polished_diorite, polished_granite, smooth_red_sandstone
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DOUBLE_STONE_SLAB4(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * stone_slab_type_4 (StringTag) = cut_red_sandstone, cut_sandstone, mossy_stone_brick, smooth_quartz, stone
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::DOUBLE_WOODEN_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 * wood_type (StringTag) = acacia, birch, dark_oak, jungle, oak, spruce
			 */
		//});
		//$this->map(VanillaBlocks::DROPPER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * triggered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::ENDER_CHEST(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ENDER_CHEST())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::END_BRICK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::END_STONE_BRICK_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::END_PORTAL_FRAME(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::END_PORTAL_FRAME())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * end_portal_eye_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::END_ROD(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::END_ROD())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::EXPOSED_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::EXPOSED_CUT_COPPER_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::EXPOSED_DOUBLE_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::FARMLAND(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::FARMLAND())
			 * TODO: implement (de)serializer
			 * moisturized_amount (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 */
		//});
		//$this->map(VanillaBlocks::FENCE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wood_type (StringTag) = acacia, birch, dark_oak, jungle, oak, spruce
			 */
		//});
		//$this->map(VanillaBlocks::FENCE_GATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::OAK_FENCE_GATE())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::FIRE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::FIRE())
			 * TODO: implement (de)serializer
			 * age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::FLOWER_POT(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::FLOWER_POT())
			 * TODO: implement (de)serializer
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::FLOWING_LAVA(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * liquid_depth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::FLOWING_WATER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * liquid_depth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::FRAME(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * item_frame_map_bit (ByteTag) = 0, 1
			 * item_frame_photo_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::FROSTED_ICE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::FROSTED_ICE())
			 * TODO: implement (de)serializer
			 * age (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::FURNACE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::FURNACE())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::GLOW_FRAME(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * item_frame_map_bit (ByteTag) = 0, 1
			 * item_frame_photo_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::GLOW_LICHEN(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * multi_face_direction_bits (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63
			 */
		//});
		//$this->map(VanillaBlocks::GOLDEN_RAIL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::POWERED_RAIL())
			 * TODO: implement (de)serializer
			 * rail_data_bit (ByteTag) = 0, 1
			 * rail_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::GRANITE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::GRANITE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::GRAY_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::GRAY_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::GRAY_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::GRAY_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::GREEN_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::GREEN_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::GREEN_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::GREEN_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::GRINDSTONE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * attachment (StringTag) = hanging, multiple, side, standing
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::HARD_STAINED_GLASS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STAINED_HARDENED_GLASS())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::HARD_STAINED_GLASS_PANE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STAINED_HARDENED_GLASS_PANE())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::HAY_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::HAY_BALE())
			 * TODO: implement (de)serializer
			 * deprecated (IntTag) = 0, 1, 2, 3
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::HEAVY_WEIGHTED_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WEIGHTED_PRESSURE_PLATE_HEAVY())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::HOPPER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::HOPPER())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * toggle_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::INFESTED_DEEPSLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::IRON_DOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::IRON_DOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::IRON_TRAPDOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::IRON_TRAPDOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::JIGSAW(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * rotation (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::JUNGLE_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::JUNGLE_BUTTON())
			 * TODO: implement (de)serializer
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::JUNGLE_DOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::JUNGLE_DOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::JUNGLE_FENCE_GATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::JUNGLE_FENCE_GATE())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::JUNGLE_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::JUNGLE_PRESSURE_PLATE())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::JUNGLE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::JUNGLE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::JUNGLE_STANDING_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::JUNGLE_SIGN())
			 * TODO: implement (de)serializer
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::JUNGLE_TRAPDOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::JUNGLE_TRAPDOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::JUNGLE_WALL_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::JUNGLE_WALL_SIGN())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::KELP(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * kelp_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(VanillaBlocks::LADDER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LADDER())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::LANTERN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LANTERN())
			 * TODO: implement (de)serializer
			 * hanging (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LARGE_AMETHYST_BUD(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::LAVA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LAVA())
			 * TODO: implement (de)serializer
			 * liquid_depth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::LAVA_CAULDRON(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * cauldron_liquid (StringTag) = lava, powder_snow, water
			 * fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6
			 */
		//});
		//$this->map(VanillaBlocks::LEAVES(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * old_leaf_type (StringTag) = birch, jungle, oak, spruce
			 * persistent_bit (ByteTag) = 0, 1
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LEAVES2(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * new_leaf_type (StringTag) = acacia, dark_oak
			 * persistent_bit (ByteTag) = 0, 1
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LECTERN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LECTERN())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LEVER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LEVER())
			 * TODO: implement (de)serializer
			 * lever_direction (StringTag) = down_east_west, down_north_south, east, north, south, up_east_west, up_north_south, west
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LIGHTNING_ROD(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::LIGHT_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * block_light_level (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::LIGHT_BLUE_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LIGHT_BLUE_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LIGHT_BLUE_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LIGHT_BLUE_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::LIGHT_GRAY_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LIGHT_GRAY_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LIGHT_WEIGHTED_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WEIGHTED_PRESSURE_PLATE_LIGHT())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::LIME_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LIME_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::LIME_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LIME_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::LIT_BLAST_FURNACE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BLAST_FURNACE())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::LIT_FURNACE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::FURNACE())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::LIT_PUMPKIN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LIT_PUMPKIN())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::LIT_SMOKER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SMOKER())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::LOG(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * old_log_type (StringTag) = birch, jungle, oak, spruce
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::LOG2(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * new_log_type (StringTag) = acacia, dark_oak
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::LOOM(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LOOM())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::MAGENTA_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::MAGENTA_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::MAGENTA_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::MAGENTA_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::MEDIUM_AMETHYST_BUD(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::MELON_STEM(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::MELON_STEM())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * growth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 */
		//});
		//$this->map(VanillaBlocks::MONSTER_EGG(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * monster_egg_stone_type (StringTag) = chiseled_stone_brick, cobblestone, cracked_stone_brick, mossy_stone_brick, stone, stone_brick
			 */
		//});
		//$this->map(VanillaBlocks::MOSSY_COBBLESTONE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::MOSSY_COBBLESTONE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::MOSSY_STONE_BRICK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::MOSSY_STONE_BRICK_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::NETHER_BRICK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::NETHER_BRICK_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::NETHER_WART(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::NETHER_WART())
			 * TODO: implement (de)serializer
			 * age (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::NORMAL_STONE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STONE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::OAK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::OAK_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::OBSERVER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::ORANGE_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::ORANGE_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::ORANGE_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::ORANGE_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::OXIDIZED_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::OXIDIZED_CUT_COPPER_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::OXIDIZED_DOUBLE_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::PINK_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::PINK_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::PINK_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::PINK_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::PISTON(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::PISTONARMCOLLISION(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::PLANKS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wood_type (StringTag) = acacia, birch, dark_oak, jungle, oak, spruce
			 */
		//});
		//$this->map(VanillaBlocks::POINTED_DRIPSTONE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * dripstone_thickness (StringTag) = base, frustum, merge, middle, tip
			 * hanging (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_ANDESITE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::POLISHED_ANDESITE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BASALT(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_BRICK_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_BRICK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_BRICK_WALL(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_DOUBLE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_BLACKSTONE_WALL(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_DEEPSLATE_DOUBLE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_DEEPSLATE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_DEEPSLATE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_DEEPSLATE_WALL(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_DIORITE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::POLISHED_DIORITE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::POLISHED_GRANITE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::POLISHED_GRANITE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::PORTAL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::NETHER_PORTAL())
			 * TODO: implement (de)serializer
			 * portal_axis (StringTag) = unknown, x, z
			 */
		//});
		//$this->map(VanillaBlocks::POTATOES(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::POTATOES())
			 * TODO: implement (de)serializer
			 * growth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 */
		//});
		//$this->map(VanillaBlocks::POWERED_COMPARATOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::REDSTONE_COMPARATOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * output_lit_bit (ByteTag) = 0, 1
			 * output_subtract_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::POWERED_REPEATER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * repeater_delay (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::PRISMARINE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::PRISMARINE())
			 * TODO: implement (de)serializer
			 * prismarine_block_type (StringTag) = bricks, dark, default
			 */
		//});
		//$this->map(VanillaBlocks::PRISMARINE_BRICKS_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::PRISMARINE_BRICKS_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::PRISMARINE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::PRISMARINE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::PUMPKIN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::PUMPKIN())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::PUMPKIN_STEM(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::PUMPKIN_STEM())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * growth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 */
		//});
		//$this->map(VanillaBlocks::PURPLE_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::PURPLE_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::PURPLE_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::PURPLE_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::PURPUR_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * chisel_type (StringTag) = chiseled, default, lines, smooth
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::PURPUR_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::PURPUR_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::QUARTZ_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * chisel_type (StringTag) = chiseled, default, lines, smooth
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::QUARTZ_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::QUARTZ_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::RAIL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::RAIL())
			 * TODO: implement (de)serializer
			 * rail_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9
			 */
		//});
		//$this->map(VanillaBlocks::REDSTONE_TORCH(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::REDSTONE_TORCH())
			 * TODO: implement (de)serializer
			 * torch_facing_direction (StringTag) = east, north, south, top, unknown, west
			 */
		//});
		//$this->map(VanillaBlocks::REDSTONE_WIRE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::REDSTONE_WIRE())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::RED_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::RED_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::RED_FLOWER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * flower_type (StringTag) = allium, cornflower, houstonia, lily_of_the_valley, orchid, oxeye, poppy, tulip_orange, tulip_pink, tulip_red, tulip_white
			 */
		//});
		//$this->map(VanillaBlocks::RED_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::RED_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::RED_MUSHROOM_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::RED_MUSHROOM_BLOCK())
			 * TODO: implement (de)serializer
			 * huge_mushroom_bits (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::RED_NETHER_BRICK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::RED_NETHER_BRICK_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::RED_SANDSTONE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::RED_SANDSTONE())
			 * TODO: implement (de)serializer
			 * sand_stone_type (StringTag) = cut, default, heiroglyphs, smooth
			 */
		//});
		//$this->map(VanillaBlocks::RED_SANDSTONE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::RED_SANDSTONE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::REEDS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SUGARCANE())
			 * TODO: implement (de)serializer
			 * age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::REPEATING_COMMAND_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::RESPAWN_ANCHOR(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * respawn_anchor_charge (IntTag) = 0, 1, 2, 3, 4
			 */
		//});
		//$this->map(VanillaBlocks::SAND(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SAND())
			 * TODO: implement (de)serializer
			 * sand_type (StringTag) = normal, red
			 */
		//});
		//$this->map(VanillaBlocks::SANDSTONE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SANDSTONE())
			 * TODO: implement (de)serializer
			 * sand_stone_type (StringTag) = cut, default, heiroglyphs, smooth
			 */
		//});
		//$this->map(VanillaBlocks::SANDSTONE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SANDSTONE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::SAPLING(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * age_bit (ByteTag) = 0, 1
			 * sapling_type (StringTag) = acacia, birch, dark_oak, jungle, oak, spruce
			 */
		//});
		//$this->map(VanillaBlocks::SCAFFOLDING(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * stability (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 * stability_check (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SCULK_CATALYST(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * bloom (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SCULK_SENSOR(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SCULK_SHRIEKER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * active (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SCULK_VEIN(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * multi_face_direction_bits (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63
			 */
		//});
		//$this->map(VanillaBlocks::SEAGRASS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * sea_grass_type (StringTag) = default, double_bot, double_top
			 */
		//});
		//$this->map(VanillaBlocks::SEA_PICKLE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SEA_PICKLE())
			 * TODO: implement (de)serializer
			 * cluster_count (IntTag) = 0, 1, 2, 3
			 * dead_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SHULKER_BOX(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::DYED_SHULKER_BOX())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::SILVER_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::LIGHT_GRAY_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::SKULL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::MOB_HEAD())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * no_drop_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SMALL_AMETHYST_BUD(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::SMALL_DRIPLEAF_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SMOKER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SMOKER())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::SMOOTH_QUARTZ_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SMOOTH_QUARTZ_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::SMOOTH_RED_SANDSTONE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SMOOTH_RED_SANDSTONE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::SMOOTH_SANDSTONE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SMOOTH_SANDSTONE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::SNOW_LAYER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SNOW_LAYER())
			 * TODO: implement (de)serializer
			 * covered_bit (ByteTag) = 0, 1
			 * height (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 */
		//});
		//$this->map(VanillaBlocks::SOUL_CAMPFIRE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * extinguished (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SOUL_FIRE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::SOUL_LANTERN(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * hanging (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SOUL_TORCH(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * torch_facing_direction (StringTag) = east, north, south, top, unknown, west
			 */
		//});
		//$this->map(VanillaBlocks::SPONGE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SPONGE())
			 * TODO: implement (de)serializer
			 * sponge_type (StringTag) = dry, wet
			 */
		//});
		//$this->map(VanillaBlocks::SPRUCE_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SPRUCE_BUTTON())
			 * TODO: implement (de)serializer
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::SPRUCE_DOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SPRUCE_DOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SPRUCE_FENCE_GATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SPRUCE_FENCE_GATE())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SPRUCE_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SPRUCE_PRESSURE_PLATE())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::SPRUCE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SPRUCE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::SPRUCE_STANDING_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SPRUCE_SIGN())
			 * TODO: implement (de)serializer
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::SPRUCE_TRAPDOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SPRUCE_TRAPDOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::SPRUCE_WALL_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SPRUCE_WALL_SIGN())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::STAINED_GLASS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STAINED_GLASS())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::STAINED_GLASS_PANE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STAINED_GLASS_PANE())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::STAINED_HARDENED_CLAY(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STAINED_CLAY())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::STANDING_BANNER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::BANNER())
			 * TODO: implement (de)serializer
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::STANDING_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::OAK_SIGN())
			 * TODO: implement (de)serializer
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::STICKYPISTONARMCOLLISION(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::STICKY_PISTON(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::STONE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STONE())
			 * TODO: implement (de)serializer
			 * stone_type (StringTag) = andesite, andesite_smooth, diorite, diorite_smooth, granite, granite_smooth, stone
			 */
		//});
		//$this->map(VanillaBlocks::STONEBRICK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * stone_brick_type (StringTag) = chiseled, cracked, default, mossy, smooth
			 */
		//});
		//$this->map(VanillaBlocks::STONECUTTER_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::STONE_BRICK_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STONE_BRICK_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::STONE_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STONE_BUTTON())
			 * TODO: implement (de)serializer
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::STONE_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STONE_PRESSURE_PLATE())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::STONE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STONE_SLAB())
			 * TODO: implement (de)serializer
			 * stone_slab_type (StringTag) = brick, cobblestone, nether_brick, quartz, sandstone, smooth_stone, stone_brick, wood
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::STONE_SLAB2(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * stone_slab_type_2 (StringTag) = mossy_cobblestone, prismarine_brick, prismarine_dark, prismarine_rough, purpur, red_nether_brick, red_sandstone, smooth_sandstone
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::STONE_SLAB3(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * stone_slab_type_3 (StringTag) = andesite, diorite, end_stone_brick, granite, polished_andesite, polished_diorite, polished_granite, smooth_red_sandstone
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::STONE_SLAB4(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * stone_slab_type_4 (StringTag) = cut_red_sandstone, cut_sandstone, mossy_stone_brick, smooth_quartz, stone
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::STONE_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::COBBLESTONE_STAIRS())
			 * TODO: implement (de)serializer
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_ACACIA_LOG(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STRIPPED_ACACIA_LOG())
			 * TODO: implement (de)serializer
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_BIRCH_LOG(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STRIPPED_BIRCH_LOG())
			 * TODO: implement (de)serializer
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_CRIMSON_HYPHAE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_CRIMSON_STEM(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_DARK_OAK_LOG(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STRIPPED_DARK_OAK_LOG())
			 * TODO: implement (de)serializer
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_JUNGLE_LOG(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STRIPPED_JUNGLE_LOG())
			 * TODO: implement (de)serializer
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_OAK_LOG(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STRIPPED_OAK_LOG())
			 * TODO: implement (de)serializer
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_SPRUCE_LOG(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::STRIPPED_SPRUCE_LOG())
			 * TODO: implement (de)serializer
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_WARPED_HYPHAE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRIPPED_WARPED_STEM(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::STRUCTURE_BLOCK(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * structure_block_type (StringTag) = corner, data, export, invalid, load, save
			 */
		//});
		//$this->map(VanillaBlocks::STRUCTURE_VOID(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * structure_void_type (StringTag) = air, void
			 */
		//});
		//$this->map(VanillaBlocks::SWEET_BERRY_BUSH(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::SWEET_BERRY_BUSH())
			 * TODO: implement (de)serializer
			 * growth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 */
		//});
		//$this->map(VanillaBlocks::TALLGRASS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * tall_grass_type (StringTag) = default, fern, snow, tall
			 */
		//});
		//$this->map(VanillaBlocks::TNT(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::TNT())
			 * TODO: implement (de)serializer
			 * allow_underwater_bit (ByteTag) = 0, 1
			 * explode_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::TORCH(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::TORCH())
			 * TODO: implement (de)serializer
			 * torch_facing_direction (StringTag) = east, north, south, top, unknown, west
			 */
		//});
		//$this->map(VanillaBlocks::TRAPDOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::OAK_TRAPDOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::TRAPPED_CHEST(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::TRAPPED_CHEST())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::TRIPWIRE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::TRIPWIRE())
			 * TODO: implement (de)serializer
			 * attached_bit (ByteTag) = 0, 1
			 * disarmed_bit (ByteTag) = 0, 1
			 * powered_bit (ByteTag) = 0, 1
			 * suspended_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::TRIPWIRE_HOOK(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::TRIPWIRE_HOOK())
			 * TODO: implement (de)serializer
			 * attached_bit (ByteTag) = 0, 1
			 * direction (IntTag) = 0, 1, 2, 3
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::TURTLE_EGG(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * cracked_state (StringTag) = cracked, max_cracked, no_cracks
			 * turtle_egg_count (StringTag) = four_egg, one_egg, three_egg, two_egg
			 */
		//});
		//$this->map(VanillaBlocks::TWISTING_VINES(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * twisting_vines_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(VanillaBlocks::UNDERWATER_TORCH(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::UNDERWATER_TORCH())
			 * TODO: implement (de)serializer
			 * torch_facing_direction (StringTag) = east, north, south, top, unknown, west
			 */
		//});
		//$this->map(VanillaBlocks::UNLIT_REDSTONE_TORCH(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::REDSTONE_TORCH())
			 * TODO: implement (de)serializer
			 * torch_facing_direction (StringTag) = east, north, south, top, unknown, west
			 */
		//});
		//$this->map(VanillaBlocks::UNPOWERED_COMPARATOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::REDSTONE_COMPARATOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * output_lit_bit (ByteTag) = 0, 1
			 * output_subtract_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::UNPOWERED_REPEATER(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * repeater_delay (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::VINE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * vine_direction_bits (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::WALL_BANNER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WALL_BANNER())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::WALL_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::OAK_WALL_SIGN())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_DOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_DOUBLE_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_FENCE_GATE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_HYPHAE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_STANDING_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_STEM(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_TRAPDOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WARPED_WALL_SIGN(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::WATER(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WATER())
			 * TODO: implement (de)serializer
			 * liquid_depth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_CUT_COPPER_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_DOUBLE_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_EXPOSED_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_EXPOSED_CUT_COPPER_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_OXIDIZED_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_OXIDIZED_CUT_COPPER_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_WEATHERED_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_WEATHERED_CUT_COPPER_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WEATHERED_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WEATHERED_CUT_COPPER_STAIRS(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(VanillaBlocks::WEATHERED_DOUBLE_CUT_COPPER_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WEEPING_VINES(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * weeping_vines_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(VanillaBlocks::WHEAT(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WHEAT())
			 * TODO: implement (de)serializer
			 * growth (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 */
		//});
		//$this->map(VanillaBlocks::WHITE_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WHITE_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WHITE_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WHITE_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::WOOD(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 * stripped_bit (ByteTag) = 0, 1
			 * wood_type (StringTag) = acacia, birch, dark_oak, jungle, oak, spruce
			 */
		//});
		//$this->map(VanillaBlocks::WOODEN_BUTTON(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::OAK_BUTTON())
			 * TODO: implement (de)serializer
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(VanillaBlocks::WOODEN_DOOR(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::OAK_DOOR())
			 * TODO: implement (de)serializer
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::WOODEN_PRESSURE_PLATE(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::OAK_PRESSURE_PLATE())
			 * TODO: implement (de)serializer
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(VanillaBlocks::WOODEN_SLAB(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 * wood_type (StringTag) = acacia, birch, dark_oak, jungle, oak, spruce
			 */
		//});
		//$this->map(VanillaBlocks::WOOL(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::WOOL())
			 * TODO: implement (de)serializer
			 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
			 */
		//});
		//$this->map(VanillaBlocks::YELLOW_CANDLE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::YELLOW_CANDLE_CAKE(), function(Block $block) : BlockStateWriter{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(VanillaBlocks::YELLOW_GLAZED_TERRACOTTA(), function(Block $block) : BlockStateWriter{
			/*
			 * This block is implemented (VanillaBlocks::YELLOW_GLAZED_TERRACOTTA())
			 * TODO: implement (de)serializer
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
	}
}
