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

namespace pocketmine\block;

use pocketmine\utils\Utils;
use function array_map;
use function mb_strtoupper;

/**
 * This class is generated automatically from source class {@link VanillaBlocksInputs}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/generate-registry-interface.php
 */
final class VanillaBlocks{
	private static WoodenButton $_mACACIA_BUTTON;
	private static CeilingCenterHangingSign $_mACACIA_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mACACIA_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mACACIA_DOOR;
	private static WoodenFence $_mACACIA_FENCE;
	private static FenceGate $_mACACIA_FENCE_GATE;
	private static Leaves $_mACACIA_LEAVES;
	private static Wood $_mACACIA_LOG;
	private static Planks $_mACACIA_PLANKS;
	private static WoodenPressurePlate $_mACACIA_PRESSURE_PLATE;
	private static Sapling $_mACACIA_SAPLING;
	private static FloorSign $_mACACIA_SIGN;
	private static WoodenSlab $_mACACIA_SLAB;
	private static WoodenStairs $_mACACIA_STAIRS;
	private static WoodenTrapdoor $_mACACIA_TRAPDOOR;
	private static WallHangingSign $_mACACIA_WALL_HANGING_SIGN;
	private static WallSign $_mACACIA_WALL_SIGN;
	private static Wood $_mACACIA_WOOD;
	private static ActivatorRail $_mACTIVATOR_RAIL;
	private static Air $_mAIR;
	private static Flower $_mALLIUM;
	private static MushroomStem $_mALL_SIDED_MUSHROOM_STEM;
	private static Opaque $_mAMETHYST;
	private static AmethystCluster $_mAMETHYST_CLUSTER;
	private static Opaque $_mANCIENT_DEBRIS;
	private static Opaque $_mANDESITE;
	private static Slab $_mANDESITE_SLAB;
	private static Stair $_mANDESITE_STAIRS;
	private static Wall $_mANDESITE_WALL;
	private static Anvil $_mANVIL;
	private static Leaves $_mAZALEA_LEAVES;
	private static Flower $_mAZURE_BLUET;
	private static Bamboo $_mBAMBOO;
	private static BambooSapling $_mBAMBOO_SAPLING;
	private static FloorBanner $_mBANNER;
	private static Barrel $_mBARREL;
	private static Transparent $_mBARRIER;
	private static SimplePillar $_mBASALT;
	private static Beacon $_mBEACON;
	private static Bed $_mBED;
	private static Bedrock $_mBEDROCK;
	private static Beetroot $_mBEETROOTS;
	private static Bell $_mBELL;
	private static BigDripleafHead $_mBIG_DRIPLEAF_HEAD;
	private static BigDripleafStem $_mBIG_DRIPLEAF_STEM;
	private static WoodenButton $_mBIRCH_BUTTON;
	private static CeilingCenterHangingSign $_mBIRCH_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mBIRCH_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mBIRCH_DOOR;
	private static WoodenFence $_mBIRCH_FENCE;
	private static FenceGate $_mBIRCH_FENCE_GATE;
	private static Leaves $_mBIRCH_LEAVES;
	private static Wood $_mBIRCH_LOG;
	private static Planks $_mBIRCH_PLANKS;
	private static WoodenPressurePlate $_mBIRCH_PRESSURE_PLATE;
	private static Sapling $_mBIRCH_SAPLING;
	private static FloorSign $_mBIRCH_SIGN;
	private static WoodenSlab $_mBIRCH_SLAB;
	private static WoodenStairs $_mBIRCH_STAIRS;
	private static WoodenTrapdoor $_mBIRCH_TRAPDOOR;
	private static WallHangingSign $_mBIRCH_WALL_HANGING_SIGN;
	private static WallSign $_mBIRCH_WALL_SIGN;
	private static Wood $_mBIRCH_WOOD;
	private static Opaque $_mBLACKSTONE;
	private static Slab $_mBLACKSTONE_SLAB;
	private static Stair $_mBLACKSTONE_STAIRS;
	private static Wall $_mBLACKSTONE_WALL;
	private static Furnace $_mBLAST_FURNACE;
	private static BlueIce $_mBLUE_ICE;
	private static Flower $_mBLUE_ORCHID;
	private static Torch $_mBLUE_TORCH;
	private static BoneBlock $_mBONE_BLOCK;
	private static Bookshelf $_mBOOKSHELF;
	private static BrewingStand $_mBREWING_STAND;
	private static Opaque $_mBRICKS;
	private static Slab $_mBRICK_SLAB;
	private static Stair $_mBRICK_STAIRS;
	private static Wall $_mBRICK_WALL;
	private static BrownMushroom $_mBROWN_MUSHROOM;
	private static BrownMushroomBlock $_mBROWN_MUSHROOM_BLOCK;
	private static BuddingAmethyst $_mBUDDING_AMETHYST;
	private static Cactus $_mCACTUS;
	private static CactusFlower $_mCACTUS_FLOWER;
	private static Cake $_mCAKE;
	private static CakeWithCandle $_mCAKE_WITH_CANDLE;
	private static CakeWithDyedCandle $_mCAKE_WITH_DYED_CANDLE;
	private static Opaque $_mCALCITE;
	private static Campfire $_mCAMPFIRE;
	private static Candle $_mCANDLE;
	private static Carpet $_mCARPET;
	private static Carrot $_mCARROTS;
	private static CartographyTable $_mCARTOGRAPHY_TABLE;
	private static CarvedPumpkin $_mCARVED_PUMPKIN;
	private static Cauldron $_mCAULDRON;
	private static CaveVines $_mCAVE_VINES;
	private static Chain $_mCHAIN;
	private static ChemicalHeat $_mCHEMICAL_HEAT;
	private static WoodenButton $_mCHERRY_BUTTON;
	private static CeilingCenterHangingSign $_mCHERRY_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mCHERRY_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mCHERRY_DOOR;
	private static WoodenFence $_mCHERRY_FENCE;
	private static FenceGate $_mCHERRY_FENCE_GATE;
	private static Leaves $_mCHERRY_LEAVES;
	private static Wood $_mCHERRY_LOG;
	private static Planks $_mCHERRY_PLANKS;
	private static WoodenPressurePlate $_mCHERRY_PRESSURE_PLATE;
	private static FloorSign $_mCHERRY_SIGN;
	private static WoodenSlab $_mCHERRY_SLAB;
	private static WoodenStairs $_mCHERRY_STAIRS;
	private static WoodenTrapdoor $_mCHERRY_TRAPDOOR;
	private static WallHangingSign $_mCHERRY_WALL_HANGING_SIGN;
	private static WallSign $_mCHERRY_WALL_SIGN;
	private static Wood $_mCHERRY_WOOD;
	private static Chest $_mCHEST;
	private static ChiseledBookshelf $_mCHISELED_BOOKSHELF;
	private static Copper $_mCHISELED_COPPER;
	private static Opaque $_mCHISELED_DEEPSLATE;
	private static Opaque $_mCHISELED_NETHER_BRICKS;
	private static Opaque $_mCHISELED_POLISHED_BLACKSTONE;
	private static SimplePillar $_mCHISELED_QUARTZ;
	private static Opaque $_mCHISELED_RED_SANDSTONE;
	private static Opaque $_mCHISELED_RESIN_BRICKS;
	private static Opaque $_mCHISELED_SANDSTONE;
	private static Opaque $_mCHISELED_STONE_BRICKS;
	private static Opaque $_mCHISELED_TUFF;
	private static Opaque $_mCHISELED_TUFF_BRICKS;
	private static ChorusFlower $_mCHORUS_FLOWER;
	private static ChorusPlant $_mCHORUS_PLANT;
	private static Clay $_mCLAY;
	private static Coal $_mCOAL;
	private static CoalOre $_mCOAL_ORE;
	private static Opaque $_mCOBBLED_DEEPSLATE;
	private static Slab $_mCOBBLED_DEEPSLATE_SLAB;
	private static Stair $_mCOBBLED_DEEPSLATE_STAIRS;
	private static Wall $_mCOBBLED_DEEPSLATE_WALL;
	private static Opaque $_mCOBBLESTONE;
	private static Slab $_mCOBBLESTONE_SLAB;
	private static Stair $_mCOBBLESTONE_STAIRS;
	private static Wall $_mCOBBLESTONE_WALL;
	private static Cobweb $_mCOBWEB;
	private static CocoaBlock $_mCOCOA_POD;
	private static ChemistryTable $_mCOMPOUND_CREATOR;
	private static Concrete $_mCONCRETE;
	private static ConcretePowder $_mCONCRETE_POWDER;
	private static Copper $_mCOPPER;
	private static CopperBars $_mCOPPER_BARS;
	private static CopperBulb $_mCOPPER_BULB;
	private static CopperChain $_mCOPPER_CHAIN;
	private static CopperDoor $_mCOPPER_DOOR;
	private static CopperGrate $_mCOPPER_GRATE;
	private static CopperLantern $_mCOPPER_LANTERN;
	private static CopperOre $_mCOPPER_ORE;
	private static Torch $_mCOPPER_TORCH;
	private static CopperTrapdoor $_mCOPPER_TRAPDOOR;
	private static Coral $_mCORAL;
	private static CoralBlock $_mCORAL_BLOCK;
	private static FloorCoralFan $_mCORAL_FAN;
	private static Flower $_mCORNFLOWER;
	private static Opaque $_mCRACKED_DEEPSLATE_BRICKS;
	private static Opaque $_mCRACKED_DEEPSLATE_TILES;
	private static Opaque $_mCRACKED_NETHER_BRICKS;
	private static Opaque $_mCRACKED_POLISHED_BLACKSTONE_BRICKS;
	private static Opaque $_mCRACKED_STONE_BRICKS;
	private static CraftingTable $_mCRAFTING_TABLE;
	private static WoodenButton $_mCRIMSON_BUTTON;
	private static CeilingCenterHangingSign $_mCRIMSON_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mCRIMSON_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mCRIMSON_DOOR;
	private static WoodenFence $_mCRIMSON_FENCE;
	private static FenceGate $_mCRIMSON_FENCE_GATE;
	private static NetherFungus $_mCRIMSON_FUNGUS;
	private static Wood $_mCRIMSON_HYPHAE;
	private static Nylium $_mCRIMSON_NYLIUM;
	private static Planks $_mCRIMSON_PLANKS;
	private static WoodenPressurePlate $_mCRIMSON_PRESSURE_PLATE;
	private static NetherRoots $_mCRIMSON_ROOTS;
	private static FloorSign $_mCRIMSON_SIGN;
	private static WoodenSlab $_mCRIMSON_SLAB;
	private static WoodenStairs $_mCRIMSON_STAIRS;
	private static Wood $_mCRIMSON_STEM;
	private static WoodenTrapdoor $_mCRIMSON_TRAPDOOR;
	private static WallHangingSign $_mCRIMSON_WALL_HANGING_SIGN;
	private static WallSign $_mCRIMSON_WALL_SIGN;
	private static Opaque $_mCRYING_OBSIDIAN;
	private static Copper $_mCUT_COPPER;
	private static CopperSlab $_mCUT_COPPER_SLAB;
	private static CopperStairs $_mCUT_COPPER_STAIRS;
	private static Opaque $_mCUT_RED_SANDSTONE;
	private static Slab $_mCUT_RED_SANDSTONE_SLAB;
	private static Opaque $_mCUT_SANDSTONE;
	private static Slab $_mCUT_SANDSTONE_SLAB;
	private static Flower $_mDANDELION;
	private static WoodenButton $_mDARK_OAK_BUTTON;
	private static CeilingCenterHangingSign $_mDARK_OAK_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mDARK_OAK_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mDARK_OAK_DOOR;
	private static WoodenFence $_mDARK_OAK_FENCE;
	private static FenceGate $_mDARK_OAK_FENCE_GATE;
	private static Leaves $_mDARK_OAK_LEAVES;
	private static Wood $_mDARK_OAK_LOG;
	private static Planks $_mDARK_OAK_PLANKS;
	private static WoodenPressurePlate $_mDARK_OAK_PRESSURE_PLATE;
	private static Sapling $_mDARK_OAK_SAPLING;
	private static FloorSign $_mDARK_OAK_SIGN;
	private static WoodenSlab $_mDARK_OAK_SLAB;
	private static WoodenStairs $_mDARK_OAK_STAIRS;
	private static WoodenTrapdoor $_mDARK_OAK_TRAPDOOR;
	private static WallHangingSign $_mDARK_OAK_WALL_HANGING_SIGN;
	private static WallSign $_mDARK_OAK_WALL_SIGN;
	private static Wood $_mDARK_OAK_WOOD;
	private static Opaque $_mDARK_PRISMARINE;
	private static Slab $_mDARK_PRISMARINE_SLAB;
	private static Stair $_mDARK_PRISMARINE_STAIRS;
	private static DaylightSensor $_mDAYLIGHT_SENSOR;
	private static DeadBush $_mDEAD_BUSH;
	private static SimplePillar $_mDEEPSLATE;
	private static Opaque $_mDEEPSLATE_BRICKS;
	private static Slab $_mDEEPSLATE_BRICK_SLAB;
	private static Stair $_mDEEPSLATE_BRICK_STAIRS;
	private static Wall $_mDEEPSLATE_BRICK_WALL;
	private static CoalOre $_mDEEPSLATE_COAL_ORE;
	private static CopperOre $_mDEEPSLATE_COPPER_ORE;
	private static DiamondOre $_mDEEPSLATE_DIAMOND_ORE;
	private static EmeraldOre $_mDEEPSLATE_EMERALD_ORE;
	private static GoldOre $_mDEEPSLATE_GOLD_ORE;
	private static IronOre $_mDEEPSLATE_IRON_ORE;
	private static LapisOre $_mDEEPSLATE_LAPIS_LAZULI_ORE;
	private static RedstoneOre $_mDEEPSLATE_REDSTONE_ORE;
	private static Opaque $_mDEEPSLATE_TILES;
	private static Slab $_mDEEPSLATE_TILE_SLAB;
	private static Stair $_mDEEPSLATE_TILE_STAIRS;
	private static Wall $_mDEEPSLATE_TILE_WALL;
	private static DetectorRail $_mDETECTOR_RAIL;
	private static Opaque $_mDIAMOND;
	private static DiamondOre $_mDIAMOND_ORE;
	private static Opaque $_mDIORITE;
	private static Slab $_mDIORITE_SLAB;
	private static Stair $_mDIORITE_STAIRS;
	private static Wall $_mDIORITE_WALL;
	private static Dirt $_mDIRT;
	private static DoublePitcherCrop $_mDOUBLE_PITCHER_CROP;
	private static DoubleTallGrass $_mDOUBLE_TALLGRASS;
	private static DragonEgg $_mDRAGON_EGG;
	private static DriedKelp $_mDRIED_KELP;
	private static DyedCandle $_mDYED_CANDLE;
	private static DyedShulkerBox $_mDYED_SHULKER_BOX;
	private static Element $_mELEMENT_ACTINIUM;
	private static Element $_mELEMENT_ALUMINUM;
	private static Element $_mELEMENT_AMERICIUM;
	private static Element $_mELEMENT_ANTIMONY;
	private static Element $_mELEMENT_ARGON;
	private static Element $_mELEMENT_ARSENIC;
	private static Element $_mELEMENT_ASTATINE;
	private static Element $_mELEMENT_BARIUM;
	private static Element $_mELEMENT_BERKELIUM;
	private static Element $_mELEMENT_BERYLLIUM;
	private static Element $_mELEMENT_BISMUTH;
	private static Element $_mELEMENT_BOHRIUM;
	private static Element $_mELEMENT_BORON;
	private static Element $_mELEMENT_BROMINE;
	private static Element $_mELEMENT_CADMIUM;
	private static Element $_mELEMENT_CALCIUM;
	private static Element $_mELEMENT_CALIFORNIUM;
	private static Element $_mELEMENT_CARBON;
	private static Element $_mELEMENT_CERIUM;
	private static Element $_mELEMENT_CESIUM;
	private static Element $_mELEMENT_CHLORINE;
	private static Element $_mELEMENT_CHROMIUM;
	private static Element $_mELEMENT_COBALT;
	private static ChemistryTable $_mELEMENT_CONSTRUCTOR;
	private static Element $_mELEMENT_COPERNICIUM;
	private static Element $_mELEMENT_COPPER;
	private static Element $_mELEMENT_CURIUM;
	private static Element $_mELEMENT_DARMSTADTIUM;
	private static Element $_mELEMENT_DUBNIUM;
	private static Element $_mELEMENT_DYSPROSIUM;
	private static Element $_mELEMENT_EINSTEINIUM;
	private static Element $_mELEMENT_ERBIUM;
	private static Element $_mELEMENT_EUROPIUM;
	private static Element $_mELEMENT_FERMIUM;
	private static Element $_mELEMENT_FLEROVIUM;
	private static Element $_mELEMENT_FLUORINE;
	private static Element $_mELEMENT_FRANCIUM;
	private static Element $_mELEMENT_GADOLINIUM;
	private static Element $_mELEMENT_GALLIUM;
	private static Element $_mELEMENT_GERMANIUM;
	private static Element $_mELEMENT_GOLD;
	private static Element $_mELEMENT_HAFNIUM;
	private static Element $_mELEMENT_HASSIUM;
	private static Element $_mELEMENT_HELIUM;
	private static Element $_mELEMENT_HOLMIUM;
	private static Element $_mELEMENT_HYDROGEN;
	private static Element $_mELEMENT_INDIUM;
	private static Element $_mELEMENT_IODINE;
	private static Element $_mELEMENT_IRIDIUM;
	private static Element $_mELEMENT_IRON;
	private static Element $_mELEMENT_KRYPTON;
	private static Element $_mELEMENT_LANTHANUM;
	private static Element $_mELEMENT_LAWRENCIUM;
	private static Element $_mELEMENT_LEAD;
	private static Element $_mELEMENT_LITHIUM;
	private static Element $_mELEMENT_LIVERMORIUM;
	private static Element $_mELEMENT_LUTETIUM;
	private static Element $_mELEMENT_MAGNESIUM;
	private static Element $_mELEMENT_MANGANESE;
	private static Element $_mELEMENT_MEITNERIUM;
	private static Element $_mELEMENT_MENDELEVIUM;
	private static Element $_mELEMENT_MERCURY;
	private static Element $_mELEMENT_MOLYBDENUM;
	private static Element $_mELEMENT_MOSCOVIUM;
	private static Element $_mELEMENT_NEODYMIUM;
	private static Element $_mELEMENT_NEON;
	private static Element $_mELEMENT_NEPTUNIUM;
	private static Element $_mELEMENT_NICKEL;
	private static Element $_mELEMENT_NIHONIUM;
	private static Element $_mELEMENT_NIOBIUM;
	private static Element $_mELEMENT_NITROGEN;
	private static Element $_mELEMENT_NOBELIUM;
	private static Element $_mELEMENT_OGANESSON;
	private static Element $_mELEMENT_OSMIUM;
	private static Element $_mELEMENT_OXYGEN;
	private static Element $_mELEMENT_PALLADIUM;
	private static Element $_mELEMENT_PHOSPHORUS;
	private static Element $_mELEMENT_PLATINUM;
	private static Element $_mELEMENT_PLUTONIUM;
	private static Element $_mELEMENT_POLONIUM;
	private static Element $_mELEMENT_POTASSIUM;
	private static Element $_mELEMENT_PRASEODYMIUM;
	private static Element $_mELEMENT_PROMETHIUM;
	private static Element $_mELEMENT_PROTACTINIUM;
	private static Element $_mELEMENT_RADIUM;
	private static Element $_mELEMENT_RADON;
	private static Element $_mELEMENT_RHENIUM;
	private static Element $_mELEMENT_RHODIUM;
	private static Element $_mELEMENT_ROENTGENIUM;
	private static Element $_mELEMENT_RUBIDIUM;
	private static Element $_mELEMENT_RUTHENIUM;
	private static Element $_mELEMENT_RUTHERFORDIUM;
	private static Element $_mELEMENT_SAMARIUM;
	private static Element $_mELEMENT_SCANDIUM;
	private static Element $_mELEMENT_SEABORGIUM;
	private static Element $_mELEMENT_SELENIUM;
	private static Element $_mELEMENT_SILICON;
	private static Element $_mELEMENT_SILVER;
	private static Element $_mELEMENT_SODIUM;
	private static Element $_mELEMENT_STRONTIUM;
	private static Element $_mELEMENT_SULFUR;
	private static Element $_mELEMENT_TANTALUM;
	private static Element $_mELEMENT_TECHNETIUM;
	private static Element $_mELEMENT_TELLURIUM;
	private static Element $_mELEMENT_TENNESSINE;
	private static Element $_mELEMENT_TERBIUM;
	private static Element $_mELEMENT_THALLIUM;
	private static Element $_mELEMENT_THORIUM;
	private static Element $_mELEMENT_THULIUM;
	private static Element $_mELEMENT_TIN;
	private static Element $_mELEMENT_TITANIUM;
	private static Element $_mELEMENT_TUNGSTEN;
	private static Element $_mELEMENT_URANIUM;
	private static Element $_mELEMENT_VANADIUM;
	private static Element $_mELEMENT_XENON;
	private static Element $_mELEMENT_YTTERBIUM;
	private static Element $_mELEMENT_YTTRIUM;
	private static Opaque $_mELEMENT_ZERO;
	private static Element $_mELEMENT_ZINC;
	private static Element $_mELEMENT_ZIRCONIUM;
	private static Opaque $_mEMERALD;
	private static EmeraldOre $_mEMERALD_ORE;
	private static EnchantingTable $_mENCHANTING_TABLE;
	private static EnderChest $_mENDER_CHEST;
	private static EndPortalFrame $_mEND_PORTAL_FRAME;
	private static EndRod $_mEND_ROD;
	private static Opaque $_mEND_STONE;
	private static Opaque $_mEND_STONE_BRICKS;
	private static Slab $_mEND_STONE_BRICK_SLAB;
	private static Stair $_mEND_STONE_BRICK_STAIRS;
	private static Wall $_mEND_STONE_BRICK_WALL;
	private static Slab $_mFAKE_WOODEN_SLAB;
	private static Farmland $_mFARMLAND;
	private static TallGrass $_mFERN;
	private static Fire $_mFIRE;
	private static FletchingTable $_mFLETCHING_TABLE;
	private static Leaves $_mFLOWERING_AZALEA_LEAVES;
	private static FlowerPot $_mFLOWER_POT;
	private static Froglight $_mFROGLIGHT;
	private static FrostedIce $_mFROSTED_ICE;
	private static Furnace $_mFURNACE;
	private static GildedBlackstone $_mGILDED_BLACKSTONE;
	private static Glass $_mGLASS;
	private static GlassPane $_mGLASS_PANE;
	private static GlazedTerracotta $_mGLAZED_TERRACOTTA;
	private static ItemFrame $_mGLOWING_ITEM_FRAME;
	private static GlowingObsidian $_mGLOWING_OBSIDIAN;
	private static Glowstone $_mGLOWSTONE;
	private static GlowLichen $_mGLOW_LICHEN;
	private static Opaque $_mGOLD;
	private static GoldOre $_mGOLD_ORE;
	private static Opaque $_mGRANITE;
	private static Slab $_mGRANITE_SLAB;
	private static Stair $_mGRANITE_STAIRS;
	private static Wall $_mGRANITE_WALL;
	private static Grass $_mGRASS;
	private static GrassPath $_mGRASS_PATH;
	private static Gravel $_mGRAVEL;
	private static Torch $_mGREEN_TORCH;
	private static HangingRoots $_mHANGING_ROOTS;
	private static HardenedClay $_mHARDENED_CLAY;
	private static HardenedGlass $_mHARDENED_GLASS;
	private static HardenedGlassPane $_mHARDENED_GLASS_PANE;
	private static HayBale $_mHAY_BALE;
	private static Opaque $_mHONEYCOMB;
	private static Hopper $_mHOPPER;
	private static Ice $_mICE;
	private static InfestedStone $_mINFESTED_CHISELED_STONE_BRICK;
	private static InfestedStone $_mINFESTED_COBBLESTONE;
	private static InfestedStone $_mINFESTED_CRACKED_STONE_BRICK;
	private static InfestedPillar $_mINFESTED_DEEPSLATE;
	private static InfestedStone $_mINFESTED_MOSSY_STONE_BRICK;
	private static InfestedStone $_mINFESTED_STONE;
	private static InfestedStone $_mINFESTED_STONE_BRICK;
	private static Opaque $_mINFO_UPDATE;
	private static Opaque $_mINFO_UPDATE2;
	private static Transparent $_mINVISIBLE_BEDROCK;
	private static Opaque $_mIRON;
	private static Thin $_mIRON_BARS;
	private static Door $_mIRON_DOOR;
	private static IronOre $_mIRON_ORE;
	private static Trapdoor $_mIRON_TRAPDOOR;
	private static ItemFrame $_mITEM_FRAME;
	private static Jukebox $_mJUKEBOX;
	private static WoodenButton $_mJUNGLE_BUTTON;
	private static CeilingCenterHangingSign $_mJUNGLE_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mJUNGLE_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mJUNGLE_DOOR;
	private static WoodenFence $_mJUNGLE_FENCE;
	private static FenceGate $_mJUNGLE_FENCE_GATE;
	private static Leaves $_mJUNGLE_LEAVES;
	private static Wood $_mJUNGLE_LOG;
	private static Planks $_mJUNGLE_PLANKS;
	private static WoodenPressurePlate $_mJUNGLE_PRESSURE_PLATE;
	private static Sapling $_mJUNGLE_SAPLING;
	private static FloorSign $_mJUNGLE_SIGN;
	private static WoodenSlab $_mJUNGLE_SLAB;
	private static WoodenStairs $_mJUNGLE_STAIRS;
	private static WoodenTrapdoor $_mJUNGLE_TRAPDOOR;
	private static WallHangingSign $_mJUNGLE_WALL_HANGING_SIGN;
	private static WallSign $_mJUNGLE_WALL_SIGN;
	private static Wood $_mJUNGLE_WOOD;
	private static ChemistryTable $_mLAB_TABLE;
	private static Ladder $_mLADDER;
	private static Lantern $_mLANTERN;
	private static Opaque $_mLAPIS_LAZULI;
	private static LapisOre $_mLAPIS_LAZULI_ORE;
	private static DoubleTallGrass $_mLARGE_FERN;
	private static Lava $_mLAVA;
	private static LavaCauldron $_mLAVA_CAULDRON;
	private static Lectern $_mLECTERN;
	private static Opaque $_mLEGACY_STONECUTTER;
	private static Lever $_mLEVER;
	private static Light $_mLIGHT;
	private static LightningRod $_mLIGHTNING_ROD;
	private static DoublePlant $_mLILAC;
	private static Flower $_mLILY_OF_THE_VALLEY;
	private static WaterLily $_mLILY_PAD;
	private static LitPumpkin $_mLIT_PUMPKIN;
	private static Loom $_mLOOM;
	private static Magma $_mMAGMA;
	private static WoodenButton $_mMANGROVE_BUTTON;
	private static CeilingCenterHangingSign $_mMANGROVE_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mMANGROVE_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mMANGROVE_DOOR;
	private static WoodenFence $_mMANGROVE_FENCE;
	private static FenceGate $_mMANGROVE_FENCE_GATE;
	private static Leaves $_mMANGROVE_LEAVES;
	private static Wood $_mMANGROVE_LOG;
	private static Planks $_mMANGROVE_PLANKS;
	private static WoodenPressurePlate $_mMANGROVE_PRESSURE_PLATE;
	private static MangroveRoots $_mMANGROVE_ROOTS;
	private static FloorSign $_mMANGROVE_SIGN;
	private static WoodenSlab $_mMANGROVE_SLAB;
	private static WoodenStairs $_mMANGROVE_STAIRS;
	private static WoodenTrapdoor $_mMANGROVE_TRAPDOOR;
	private static WallHangingSign $_mMANGROVE_WALL_HANGING_SIGN;
	private static WallSign $_mMANGROVE_WALL_SIGN;
	private static Wood $_mMANGROVE_WOOD;
	private static ChemistryTable $_mMATERIAL_REDUCER;
	private static Melon $_mMELON;
	private static MelonStem $_mMELON_STEM;
	private static MobHead $_mMOB_HEAD;
	private static MonsterSpawner $_mMONSTER_SPAWNER;
	private static Opaque $_mMOSSY_COBBLESTONE;
	private static Slab $_mMOSSY_COBBLESTONE_SLAB;
	private static Stair $_mMOSSY_COBBLESTONE_STAIRS;
	private static Wall $_mMOSSY_COBBLESTONE_WALL;
	private static Opaque $_mMOSSY_STONE_BRICKS;
	private static Slab $_mMOSSY_STONE_BRICK_SLAB;
	private static Stair $_mMOSSY_STONE_BRICK_STAIRS;
	private static Wall $_mMOSSY_STONE_BRICK_WALL;
	private static Opaque $_mMUD;
	private static SimplePillar $_mMUDDY_MANGROVE_ROOTS;
	private static Opaque $_mMUD_BRICKS;
	private static Slab $_mMUD_BRICK_SLAB;
	private static Stair $_mMUD_BRICK_STAIRS;
	private static Wall $_mMUD_BRICK_WALL;
	private static MushroomStem $_mMUSHROOM_STEM;
	private static Mycelium $_mMYCELIUM;
	private static Opaque $_mNETHERITE;
	private static Netherrack $_mNETHERRACK;
	private static Opaque $_mNETHER_BRICKS;
	private static Fence $_mNETHER_BRICK_FENCE;
	private static Slab $_mNETHER_BRICK_SLAB;
	private static Stair $_mNETHER_BRICK_STAIRS;
	private static Wall $_mNETHER_BRICK_WALL;
	private static NetherGoldOre $_mNETHER_GOLD_ORE;
	private static NetherPortal $_mNETHER_PORTAL;
	private static NetherQuartzOre $_mNETHER_QUARTZ_ORE;
	private static NetherReactor $_mNETHER_REACTOR_CORE;
	private static NetherSprouts $_mNETHER_SPROUTS;
	private static NetherWartPlant $_mNETHER_WART;
	private static Opaque $_mNETHER_WART_BLOCK;
	private static Note $_mNOTE_BLOCK;
	private static WoodenButton $_mOAK_BUTTON;
	private static CeilingCenterHangingSign $_mOAK_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mOAK_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mOAK_DOOR;
	private static WoodenFence $_mOAK_FENCE;
	private static FenceGate $_mOAK_FENCE_GATE;
	private static Leaves $_mOAK_LEAVES;
	private static Wood $_mOAK_LOG;
	private static Planks $_mOAK_PLANKS;
	private static WoodenPressurePlate $_mOAK_PRESSURE_PLATE;
	private static Sapling $_mOAK_SAPLING;
	private static FloorSign $_mOAK_SIGN;
	private static WoodenSlab $_mOAK_SLAB;
	private static WoodenStairs $_mOAK_STAIRS;
	private static WoodenTrapdoor $_mOAK_TRAPDOOR;
	private static WallHangingSign $_mOAK_WALL_HANGING_SIGN;
	private static WallSign $_mOAK_WALL_SIGN;
	private static Wood $_mOAK_WOOD;
	private static Opaque $_mOBSIDIAN;
	private static OminousFloorBanner $_mOMINOUS_BANNER;
	private static OminousWallBanner $_mOMINOUS_WALL_BANNER;
	private static Flower $_mORANGE_TULIP;
	private static Flower $_mOXEYE_DAISY;
	private static PackedIce $_mPACKED_ICE;
	private static Opaque $_mPACKED_MUD;
	private static WoodenButton $_mPALE_OAK_BUTTON;
	private static CeilingCenterHangingSign $_mPALE_OAK_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mPALE_OAK_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mPALE_OAK_DOOR;
	private static WoodenFence $_mPALE_OAK_FENCE;
	private static FenceGate $_mPALE_OAK_FENCE_GATE;
	private static Leaves $_mPALE_OAK_LEAVES;
	private static Wood $_mPALE_OAK_LOG;
	private static Planks $_mPALE_OAK_PLANKS;
	private static WoodenPressurePlate $_mPALE_OAK_PRESSURE_PLATE;
	private static FloorSign $_mPALE_OAK_SIGN;
	private static WoodenSlab $_mPALE_OAK_SLAB;
	private static WoodenStairs $_mPALE_OAK_STAIRS;
	private static WoodenTrapdoor $_mPALE_OAK_TRAPDOOR;
	private static WallHangingSign $_mPALE_OAK_WALL_HANGING_SIGN;
	private static WallSign $_mPALE_OAK_WALL_SIGN;
	private static Wood $_mPALE_OAK_WOOD;
	private static DoublePlant $_mPEONY;
	private static PinkPetals $_mPINK_PETALS;
	private static Flower $_mPINK_TULIP;
	private static PitcherCrop $_mPITCHER_CROP;
	private static DoublePlant $_mPITCHER_PLANT;
	private static Podzol $_mPODZOL;
	private static Opaque $_mPOLISHED_ANDESITE;
	private static Slab $_mPOLISHED_ANDESITE_SLAB;
	private static Stair $_mPOLISHED_ANDESITE_STAIRS;
	private static SimplePillar $_mPOLISHED_BASALT;
	private static Opaque $_mPOLISHED_BLACKSTONE;
	private static Opaque $_mPOLISHED_BLACKSTONE_BRICKS;
	private static Slab $_mPOLISHED_BLACKSTONE_BRICK_SLAB;
	private static Stair $_mPOLISHED_BLACKSTONE_BRICK_STAIRS;
	private static Wall $_mPOLISHED_BLACKSTONE_BRICK_WALL;
	private static StoneButton $_mPOLISHED_BLACKSTONE_BUTTON;
	private static StonePressurePlate $_mPOLISHED_BLACKSTONE_PRESSURE_PLATE;
	private static Slab $_mPOLISHED_BLACKSTONE_SLAB;
	private static Stair $_mPOLISHED_BLACKSTONE_STAIRS;
	private static Wall $_mPOLISHED_BLACKSTONE_WALL;
	private static Opaque $_mPOLISHED_DEEPSLATE;
	private static Slab $_mPOLISHED_DEEPSLATE_SLAB;
	private static Stair $_mPOLISHED_DEEPSLATE_STAIRS;
	private static Wall $_mPOLISHED_DEEPSLATE_WALL;
	private static Opaque $_mPOLISHED_DIORITE;
	private static Slab $_mPOLISHED_DIORITE_SLAB;
	private static Stair $_mPOLISHED_DIORITE_STAIRS;
	private static Opaque $_mPOLISHED_GRANITE;
	private static Slab $_mPOLISHED_GRANITE_SLAB;
	private static Stair $_mPOLISHED_GRANITE_STAIRS;
	private static Opaque $_mPOLISHED_TUFF;
	private static Slab $_mPOLISHED_TUFF_SLAB;
	private static Stair $_mPOLISHED_TUFF_STAIRS;
	private static Wall $_mPOLISHED_TUFF_WALL;
	private static Flower $_mPOPPY;
	private static Potato $_mPOTATOES;
	private static PotionCauldron $_mPOTION_CAULDRON;
	private static PoweredRail $_mPOWERED_RAIL;
	private static Opaque $_mPRISMARINE;
	private static Opaque $_mPRISMARINE_BRICKS;
	private static Slab $_mPRISMARINE_BRICKS_SLAB;
	private static Stair $_mPRISMARINE_BRICKS_STAIRS;
	private static Slab $_mPRISMARINE_SLAB;
	private static Stair $_mPRISMARINE_STAIRS;
	private static Wall $_mPRISMARINE_WALL;
	private static Pumpkin $_mPUMPKIN;
	private static PumpkinStem $_mPUMPKIN_STEM;
	private static Torch $_mPURPLE_TORCH;
	private static Opaque $_mPURPUR;
	private static SimplePillar $_mPURPUR_PILLAR;
	private static Slab $_mPURPUR_SLAB;
	private static Stair $_mPURPUR_STAIRS;
	private static Opaque $_mQUARTZ;
	private static Opaque $_mQUARTZ_BRICKS;
	private static SimplePillar $_mQUARTZ_PILLAR;
	private static Slab $_mQUARTZ_SLAB;
	private static Stair $_mQUARTZ_STAIRS;
	private static Rail $_mRAIL;
	private static Opaque $_mRAW_COPPER;
	private static Opaque $_mRAW_GOLD;
	private static Opaque $_mRAW_IRON;
	private static Redstone $_mREDSTONE;
	private static RedstoneComparator $_mREDSTONE_COMPARATOR;
	private static RedstoneLamp $_mREDSTONE_LAMP;
	private static RedstoneOre $_mREDSTONE_ORE;
	private static RedstoneRepeater $_mREDSTONE_REPEATER;
	private static RedstoneTorch $_mREDSTONE_TORCH;
	private static RedstoneWire $_mREDSTONE_WIRE;
	private static RedMushroom $_mRED_MUSHROOM;
	private static RedMushroomBlock $_mRED_MUSHROOM_BLOCK;
	private static Opaque $_mRED_NETHER_BRICKS;
	private static Slab $_mRED_NETHER_BRICK_SLAB;
	private static Stair $_mRED_NETHER_BRICK_STAIRS;
	private static Wall $_mRED_NETHER_BRICK_WALL;
	private static Sand $_mRED_SAND;
	private static Opaque $_mRED_SANDSTONE;
	private static Slab $_mRED_SANDSTONE_SLAB;
	private static Stair $_mRED_SANDSTONE_STAIRS;
	private static Wall $_mRED_SANDSTONE_WALL;
	private static Torch $_mRED_TORCH;
	private static Flower $_mRED_TULIP;
	private static Opaque $_mREINFORCED_DEEPSLATE;
	private static Reserved6 $_mRESERVED6;
	private static Opaque $_mRESIN;
	private static Opaque $_mRESIN_BRICKS;
	private static Slab $_mRESIN_BRICK_SLAB;
	private static Stair $_mRESIN_BRICK_STAIRS;
	private static Wall $_mRESIN_BRICK_WALL;
	private static ResinClump $_mRESIN_CLUMP;
	private static RespawnAnchor $_mRESPAWN_ANCHOR;
	private static DoublePlant $_mROSE_BUSH;
	private static Sand $_mSAND;
	private static Opaque $_mSANDSTONE;
	private static Slab $_mSANDSTONE_SLAB;
	private static Stair $_mSANDSTONE_STAIRS;
	private static Wall $_mSANDSTONE_WALL;
	private static Sculk $_mSCULK;
	private static SeaLantern $_mSEA_LANTERN;
	private static SeaPickle $_mSEA_PICKLE;
	private static Opaque $_mSHROOMLIGHT;
	private static ShulkerBox $_mSHULKER_BOX;
	private static Slime $_mSLIME;
	private static SmallDripleaf $_mSMALL_DRIPLEAF;
	private static SmithingTable $_mSMITHING_TABLE;
	private static Furnace $_mSMOKER;
	private static Opaque $_mSMOOTH_BASALT;
	private static Opaque $_mSMOOTH_QUARTZ;
	private static Slab $_mSMOOTH_QUARTZ_SLAB;
	private static Stair $_mSMOOTH_QUARTZ_STAIRS;
	private static Opaque $_mSMOOTH_RED_SANDSTONE;
	private static Slab $_mSMOOTH_RED_SANDSTONE_SLAB;
	private static Stair $_mSMOOTH_RED_SANDSTONE_STAIRS;
	private static Opaque $_mSMOOTH_SANDSTONE;
	private static Slab $_mSMOOTH_SANDSTONE_SLAB;
	private static Stair $_mSMOOTH_SANDSTONE_STAIRS;
	private static Opaque $_mSMOOTH_STONE;
	private static Slab $_mSMOOTH_STONE_SLAB;
	private static Snow $_mSNOW;
	private static SnowLayer $_mSNOW_LAYER;
	private static SoulCampfire $_mSOUL_CAMPFIRE;
	private static SoulFire $_mSOUL_FIRE;
	private static Lantern $_mSOUL_LANTERN;
	private static SoulSand $_mSOUL_SAND;
	private static Opaque $_mSOUL_SOIL;
	private static Torch $_mSOUL_TORCH;
	private static Sponge $_mSPONGE;
	private static SporeBlossom $_mSPORE_BLOSSOM;
	private static WoodenButton $_mSPRUCE_BUTTON;
	private static CeilingCenterHangingSign $_mSPRUCE_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mSPRUCE_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mSPRUCE_DOOR;
	private static WoodenFence $_mSPRUCE_FENCE;
	private static FenceGate $_mSPRUCE_FENCE_GATE;
	private static Leaves $_mSPRUCE_LEAVES;
	private static Wood $_mSPRUCE_LOG;
	private static Planks $_mSPRUCE_PLANKS;
	private static WoodenPressurePlate $_mSPRUCE_PRESSURE_PLATE;
	private static Sapling $_mSPRUCE_SAPLING;
	private static FloorSign $_mSPRUCE_SIGN;
	private static WoodenSlab $_mSPRUCE_SLAB;
	private static WoodenStairs $_mSPRUCE_STAIRS;
	private static WoodenTrapdoor $_mSPRUCE_TRAPDOOR;
	private static WallHangingSign $_mSPRUCE_WALL_HANGING_SIGN;
	private static WallSign $_mSPRUCE_WALL_SIGN;
	private static Wood $_mSPRUCE_WOOD;
	private static StainedHardenedClay $_mSTAINED_CLAY;
	private static StainedGlass $_mSTAINED_GLASS;
	private static StainedGlassPane $_mSTAINED_GLASS_PANE;
	private static StainedHardenedGlass $_mSTAINED_HARDENED_GLASS;
	private static StainedHardenedGlassPane $_mSTAINED_HARDENED_GLASS_PANE;
	private static Opaque $_mSTONE;
	private static Stonecutter $_mSTONECUTTER;
	private static Opaque $_mSTONE_BRICKS;
	private static Slab $_mSTONE_BRICK_SLAB;
	private static Stair $_mSTONE_BRICK_STAIRS;
	private static Wall $_mSTONE_BRICK_WALL;
	private static StoneButton $_mSTONE_BUTTON;
	private static StonePressurePlate $_mSTONE_PRESSURE_PLATE;
	private static Slab $_mSTONE_SLAB;
	private static Stair $_mSTONE_STAIRS;
	private static StructureVoid $_mSTRUCTURE_VOID;
	private static Sugarcane $_mSUGARCANE;
	private static DoublePlant $_mSUNFLOWER;
	private static SweetBerryBush $_mSWEET_BERRY_BUSH;
	private static TallGrass $_mTALL_GRASS;
	private static TintedGlass $_mTINTED_GLASS;
	private static TNT $_mTNT;
	private static Torch $_mTORCH;
	private static Flower $_mTORCHFLOWER;
	private static TorchflowerCrop $_mTORCHFLOWER_CROP;
	private static TrappedChest $_mTRAPPED_CHEST;
	private static Tripwire $_mTRIPWIRE;
	private static TripwireHook $_mTRIPWIRE_HOOK;
	private static Opaque $_mTUFF;
	private static Opaque $_mTUFF_BRICKS;
	private static Slab $_mTUFF_BRICK_SLAB;
	private static Stair $_mTUFF_BRICK_STAIRS;
	private static Wall $_mTUFF_BRICK_WALL;
	private static Slab $_mTUFF_SLAB;
	private static Stair $_mTUFF_STAIRS;
	private static Wall $_mTUFF_WALL;
	private static NetherVines $_mTWISTING_VINES;
	private static UnderwaterTorch $_mUNDERWATER_TORCH;
	private static Vine $_mVINES;
	private static WallBanner $_mWALL_BANNER;
	private static WallCoralFan $_mWALL_CORAL_FAN;
	private static WoodenButton $_mWARPED_BUTTON;
	private static CeilingCenterHangingSign $_mWARPED_CEILING_CENTER_HANGING_SIGN;
	private static CeilingEdgesHangingSign $_mWARPED_CEILING_EDGES_HANGING_SIGN;
	private static WoodenDoor $_mWARPED_DOOR;
	private static WoodenFence $_mWARPED_FENCE;
	private static FenceGate $_mWARPED_FENCE_GATE;
	private static NetherFungus $_mWARPED_FUNGUS;
	private static Wood $_mWARPED_HYPHAE;
	private static Nylium $_mWARPED_NYLIUM;
	private static Planks $_mWARPED_PLANKS;
	private static WoodenPressurePlate $_mWARPED_PRESSURE_PLATE;
	private static NetherRoots $_mWARPED_ROOTS;
	private static FloorSign $_mWARPED_SIGN;
	private static WoodenSlab $_mWARPED_SLAB;
	private static WoodenStairs $_mWARPED_STAIRS;
	private static Wood $_mWARPED_STEM;
	private static WoodenTrapdoor $_mWARPED_TRAPDOOR;
	private static WallHangingSign $_mWARPED_WALL_HANGING_SIGN;
	private static WallSign $_mWARPED_WALL_SIGN;
	private static Opaque $_mWARPED_WART_BLOCK;
	private static Water $_mWATER;
	private static WaterCauldron $_mWATER_CAULDRON;
	private static NetherVines $_mWEEPING_VINES;
	private static WeightedPressurePlateHeavy $_mWEIGHTED_PRESSURE_PLATE_HEAVY;
	private static WeightedPressurePlateLight $_mWEIGHTED_PRESSURE_PLATE_LIGHT;
	private static Wheat $_mWHEAT;
	private static Flower $_mWHITE_TULIP;
	private static WitherRose $_mWITHER_ROSE;
	private static Wool $_mWOOL;

	/**
	 * @var Block[]
	 * @phpstan-var array<string, Block>
	 */
	private static array $members;

	private function __construct(){
		//NOOP
	}

	/**
	 * Hack to allow ignoring PHPStan wrong type assignment error in one place instead of hundreds or thousands
	 * Assumes that the input value already matches the expected type. If not, a TypeError will be thrown on assignment.
	 *
	 * @phpstan-template TValue of Block
	 * @phpstan-param \Closure(TValue): TValue $closure
	 */
	private static function unsafeAssign(\Closure $closure, Block $memberValue) : void{
		/** @phpstan-var TValue $memberValue */
		$closure($memberValue);
	}

	private static function init() : void{
		//This nasty mess of closures allows us to suppress PHPStan type assignment errors in one place instead of
		//on every single assignment. This will only run one time on first init, so it's fine for performance.
		$values = VanillaBlocksInputs::getAll();
		foreach(Utils::stringifyKeys($values) as $name => $value){
			self::$members[mb_strtoupper($name)] = $value;
		}

		self::unsafeAssign(fn(WoodenButton $v) => self::$_mACACIA_BUTTON = $v, $values["acacia_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mACACIA_CEILING_CENTER_HANGING_SIGN = $v, $values["acacia_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mACACIA_CEILING_EDGES_HANGING_SIGN = $v, $values["acacia_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mACACIA_DOOR = $v, $values["acacia_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mACACIA_FENCE = $v, $values["acacia_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mACACIA_FENCE_GATE = $v, $values["acacia_fence_gate"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mACACIA_LEAVES = $v, $values["acacia_leaves"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mACACIA_LOG = $v, $values["acacia_log"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mACACIA_PLANKS = $v, $values["acacia_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mACACIA_PRESSURE_PLATE = $v, $values["acacia_pressure_plate"]);
		self::unsafeAssign(fn(Sapling $v) => self::$_mACACIA_SAPLING = $v, $values["acacia_sapling"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mACACIA_SIGN = $v, $values["acacia_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mACACIA_SLAB = $v, $values["acacia_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mACACIA_STAIRS = $v, $values["acacia_stairs"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mACACIA_TRAPDOOR = $v, $values["acacia_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mACACIA_WALL_HANGING_SIGN = $v, $values["acacia_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mACACIA_WALL_SIGN = $v, $values["acacia_wall_sign"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mACACIA_WOOD = $v, $values["acacia_wood"]);
		self::unsafeAssign(fn(ActivatorRail $v) => self::$_mACTIVATOR_RAIL = $v, $values["activator_rail"]);
		self::unsafeAssign(fn(Air $v) => self::$_mAIR = $v, $values["air"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mALLIUM = $v, $values["allium"]);
		self::unsafeAssign(fn(MushroomStem $v) => self::$_mALL_SIDED_MUSHROOM_STEM = $v, $values["all_sided_mushroom_stem"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mAMETHYST = $v, $values["amethyst"]);
		self::unsafeAssign(fn(AmethystCluster $v) => self::$_mAMETHYST_CLUSTER = $v, $values["amethyst_cluster"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mANCIENT_DEBRIS = $v, $values["ancient_debris"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mANDESITE = $v, $values["andesite"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mANDESITE_SLAB = $v, $values["andesite_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mANDESITE_STAIRS = $v, $values["andesite_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mANDESITE_WALL = $v, $values["andesite_wall"]);
		self::unsafeAssign(fn(Anvil $v) => self::$_mANVIL = $v, $values["anvil"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mAZALEA_LEAVES = $v, $values["azalea_leaves"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mAZURE_BLUET = $v, $values["azure_bluet"]);
		self::unsafeAssign(fn(Bamboo $v) => self::$_mBAMBOO = $v, $values["bamboo"]);
		self::unsafeAssign(fn(BambooSapling $v) => self::$_mBAMBOO_SAPLING = $v, $values["bamboo_sapling"]);
		self::unsafeAssign(fn(FloorBanner $v) => self::$_mBANNER = $v, $values["banner"]);
		self::unsafeAssign(fn(Barrel $v) => self::$_mBARREL = $v, $values["barrel"]);
		self::unsafeAssign(fn(Transparent $v) => self::$_mBARRIER = $v, $values["barrier"]);
		self::unsafeAssign(fn(SimplePillar $v) => self::$_mBASALT = $v, $values["basalt"]);
		self::unsafeAssign(fn(Beacon $v) => self::$_mBEACON = $v, $values["beacon"]);
		self::unsafeAssign(fn(Bed $v) => self::$_mBED = $v, $values["bed"]);
		self::unsafeAssign(fn(Bedrock $v) => self::$_mBEDROCK = $v, $values["bedrock"]);
		self::unsafeAssign(fn(Beetroot $v) => self::$_mBEETROOTS = $v, $values["beetroots"]);
		self::unsafeAssign(fn(Bell $v) => self::$_mBELL = $v, $values["bell"]);
		self::unsafeAssign(fn(BigDripleafHead $v) => self::$_mBIG_DRIPLEAF_HEAD = $v, $values["big_dripleaf_head"]);
		self::unsafeAssign(fn(BigDripleafStem $v) => self::$_mBIG_DRIPLEAF_STEM = $v, $values["big_dripleaf_stem"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mBIRCH_BUTTON = $v, $values["birch_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mBIRCH_CEILING_CENTER_HANGING_SIGN = $v, $values["birch_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mBIRCH_CEILING_EDGES_HANGING_SIGN = $v, $values["birch_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mBIRCH_DOOR = $v, $values["birch_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mBIRCH_FENCE = $v, $values["birch_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mBIRCH_FENCE_GATE = $v, $values["birch_fence_gate"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mBIRCH_LEAVES = $v, $values["birch_leaves"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mBIRCH_LOG = $v, $values["birch_log"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mBIRCH_PLANKS = $v, $values["birch_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mBIRCH_PRESSURE_PLATE = $v, $values["birch_pressure_plate"]);
		self::unsafeAssign(fn(Sapling $v) => self::$_mBIRCH_SAPLING = $v, $values["birch_sapling"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mBIRCH_SIGN = $v, $values["birch_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mBIRCH_SLAB = $v, $values["birch_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mBIRCH_STAIRS = $v, $values["birch_stairs"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mBIRCH_TRAPDOOR = $v, $values["birch_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mBIRCH_WALL_HANGING_SIGN = $v, $values["birch_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mBIRCH_WALL_SIGN = $v, $values["birch_wall_sign"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mBIRCH_WOOD = $v, $values["birch_wood"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mBLACKSTONE = $v, $values["blackstone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mBLACKSTONE_SLAB = $v, $values["blackstone_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mBLACKSTONE_STAIRS = $v, $values["blackstone_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mBLACKSTONE_WALL = $v, $values["blackstone_wall"]);
		self::unsafeAssign(fn(Furnace $v) => self::$_mBLAST_FURNACE = $v, $values["blast_furnace"]);
		self::unsafeAssign(fn(BlueIce $v) => self::$_mBLUE_ICE = $v, $values["blue_ice"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mBLUE_ORCHID = $v, $values["blue_orchid"]);
		self::unsafeAssign(fn(Torch $v) => self::$_mBLUE_TORCH = $v, $values["blue_torch"]);
		self::unsafeAssign(fn(BoneBlock $v) => self::$_mBONE_BLOCK = $v, $values["bone_block"]);
		self::unsafeAssign(fn(Bookshelf $v) => self::$_mBOOKSHELF = $v, $values["bookshelf"]);
		self::unsafeAssign(fn(BrewingStand $v) => self::$_mBREWING_STAND = $v, $values["brewing_stand"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mBRICKS = $v, $values["bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mBRICK_SLAB = $v, $values["brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mBRICK_STAIRS = $v, $values["brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mBRICK_WALL = $v, $values["brick_wall"]);
		self::unsafeAssign(fn(BrownMushroom $v) => self::$_mBROWN_MUSHROOM = $v, $values["brown_mushroom"]);
		self::unsafeAssign(fn(BrownMushroomBlock $v) => self::$_mBROWN_MUSHROOM_BLOCK = $v, $values["brown_mushroom_block"]);
		self::unsafeAssign(fn(BuddingAmethyst $v) => self::$_mBUDDING_AMETHYST = $v, $values["budding_amethyst"]);
		self::unsafeAssign(fn(Cactus $v) => self::$_mCACTUS = $v, $values["cactus"]);
		self::unsafeAssign(fn(CactusFlower $v) => self::$_mCACTUS_FLOWER = $v, $values["cactus_flower"]);
		self::unsafeAssign(fn(Cake $v) => self::$_mCAKE = $v, $values["cake"]);
		self::unsafeAssign(fn(CakeWithCandle $v) => self::$_mCAKE_WITH_CANDLE = $v, $values["cake_with_candle"]);
		self::unsafeAssign(fn(CakeWithDyedCandle $v) => self::$_mCAKE_WITH_DYED_CANDLE = $v, $values["cake_with_dyed_candle"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCALCITE = $v, $values["calcite"]);
		self::unsafeAssign(fn(Campfire $v) => self::$_mCAMPFIRE = $v, $values["campfire"]);
		self::unsafeAssign(fn(Candle $v) => self::$_mCANDLE = $v, $values["candle"]);
		self::unsafeAssign(fn(Carpet $v) => self::$_mCARPET = $v, $values["carpet"]);
		self::unsafeAssign(fn(Carrot $v) => self::$_mCARROTS = $v, $values["carrots"]);
		self::unsafeAssign(fn(CartographyTable $v) => self::$_mCARTOGRAPHY_TABLE = $v, $values["cartography_table"]);
		self::unsafeAssign(fn(CarvedPumpkin $v) => self::$_mCARVED_PUMPKIN = $v, $values["carved_pumpkin"]);
		self::unsafeAssign(fn(Cauldron $v) => self::$_mCAULDRON = $v, $values["cauldron"]);
		self::unsafeAssign(fn(CaveVines $v) => self::$_mCAVE_VINES = $v, $values["cave_vines"]);
		self::unsafeAssign(fn(Chain $v) => self::$_mCHAIN = $v, $values["chain"]);
		self::unsafeAssign(fn(ChemicalHeat $v) => self::$_mCHEMICAL_HEAT = $v, $values["chemical_heat"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mCHERRY_BUTTON = $v, $values["cherry_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mCHERRY_CEILING_CENTER_HANGING_SIGN = $v, $values["cherry_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mCHERRY_CEILING_EDGES_HANGING_SIGN = $v, $values["cherry_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mCHERRY_DOOR = $v, $values["cherry_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mCHERRY_FENCE = $v, $values["cherry_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mCHERRY_FENCE_GATE = $v, $values["cherry_fence_gate"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mCHERRY_LEAVES = $v, $values["cherry_leaves"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mCHERRY_LOG = $v, $values["cherry_log"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mCHERRY_PLANKS = $v, $values["cherry_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mCHERRY_PRESSURE_PLATE = $v, $values["cherry_pressure_plate"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mCHERRY_SIGN = $v, $values["cherry_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mCHERRY_SLAB = $v, $values["cherry_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mCHERRY_STAIRS = $v, $values["cherry_stairs"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mCHERRY_TRAPDOOR = $v, $values["cherry_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mCHERRY_WALL_HANGING_SIGN = $v, $values["cherry_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mCHERRY_WALL_SIGN = $v, $values["cherry_wall_sign"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mCHERRY_WOOD = $v, $values["cherry_wood"]);
		self::unsafeAssign(fn(Chest $v) => self::$_mCHEST = $v, $values["chest"]);
		self::unsafeAssign(fn(ChiseledBookshelf $v) => self::$_mCHISELED_BOOKSHELF = $v, $values["chiseled_bookshelf"]);
		self::unsafeAssign(fn(Copper $v) => self::$_mCHISELED_COPPER = $v, $values["chiseled_copper"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCHISELED_DEEPSLATE = $v, $values["chiseled_deepslate"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCHISELED_NETHER_BRICKS = $v, $values["chiseled_nether_bricks"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCHISELED_POLISHED_BLACKSTONE = $v, $values["chiseled_polished_blackstone"]);
		self::unsafeAssign(fn(SimplePillar $v) => self::$_mCHISELED_QUARTZ = $v, $values["chiseled_quartz"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCHISELED_RED_SANDSTONE = $v, $values["chiseled_red_sandstone"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCHISELED_RESIN_BRICKS = $v, $values["chiseled_resin_bricks"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCHISELED_SANDSTONE = $v, $values["chiseled_sandstone"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCHISELED_STONE_BRICKS = $v, $values["chiseled_stone_bricks"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCHISELED_TUFF = $v, $values["chiseled_tuff"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCHISELED_TUFF_BRICKS = $v, $values["chiseled_tuff_bricks"]);
		self::unsafeAssign(fn(ChorusFlower $v) => self::$_mCHORUS_FLOWER = $v, $values["chorus_flower"]);
		self::unsafeAssign(fn(ChorusPlant $v) => self::$_mCHORUS_PLANT = $v, $values["chorus_plant"]);
		self::unsafeAssign(fn(Clay $v) => self::$_mCLAY = $v, $values["clay"]);
		self::unsafeAssign(fn(Coal $v) => self::$_mCOAL = $v, $values["coal"]);
		self::unsafeAssign(fn(CoalOre $v) => self::$_mCOAL_ORE = $v, $values["coal_ore"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCOBBLED_DEEPSLATE = $v, $values["cobbled_deepslate"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mCOBBLED_DEEPSLATE_SLAB = $v, $values["cobbled_deepslate_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mCOBBLED_DEEPSLATE_STAIRS = $v, $values["cobbled_deepslate_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mCOBBLED_DEEPSLATE_WALL = $v, $values["cobbled_deepslate_wall"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCOBBLESTONE = $v, $values["cobblestone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mCOBBLESTONE_SLAB = $v, $values["cobblestone_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mCOBBLESTONE_STAIRS = $v, $values["cobblestone_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mCOBBLESTONE_WALL = $v, $values["cobblestone_wall"]);
		self::unsafeAssign(fn(Cobweb $v) => self::$_mCOBWEB = $v, $values["cobweb"]);
		self::unsafeAssign(fn(CocoaBlock $v) => self::$_mCOCOA_POD = $v, $values["cocoa_pod"]);
		self::unsafeAssign(fn(ChemistryTable $v) => self::$_mCOMPOUND_CREATOR = $v, $values["compound_creator"]);
		self::unsafeAssign(fn(Concrete $v) => self::$_mCONCRETE = $v, $values["concrete"]);
		self::unsafeAssign(fn(ConcretePowder $v) => self::$_mCONCRETE_POWDER = $v, $values["concrete_powder"]);
		self::unsafeAssign(fn(Copper $v) => self::$_mCOPPER = $v, $values["copper"]);
		self::unsafeAssign(fn(CopperBars $v) => self::$_mCOPPER_BARS = $v, $values["copper_bars"]);
		self::unsafeAssign(fn(CopperBulb $v) => self::$_mCOPPER_BULB = $v, $values["copper_bulb"]);
		self::unsafeAssign(fn(CopperChain $v) => self::$_mCOPPER_CHAIN = $v, $values["copper_chain"]);
		self::unsafeAssign(fn(CopperDoor $v) => self::$_mCOPPER_DOOR = $v, $values["copper_door"]);
		self::unsafeAssign(fn(CopperGrate $v) => self::$_mCOPPER_GRATE = $v, $values["copper_grate"]);
		self::unsafeAssign(fn(CopperLantern $v) => self::$_mCOPPER_LANTERN = $v, $values["copper_lantern"]);
		self::unsafeAssign(fn(CopperOre $v) => self::$_mCOPPER_ORE = $v, $values["copper_ore"]);
		self::unsafeAssign(fn(Torch $v) => self::$_mCOPPER_TORCH = $v, $values["copper_torch"]);
		self::unsafeAssign(fn(CopperTrapdoor $v) => self::$_mCOPPER_TRAPDOOR = $v, $values["copper_trapdoor"]);
		self::unsafeAssign(fn(Coral $v) => self::$_mCORAL = $v, $values["coral"]);
		self::unsafeAssign(fn(CoralBlock $v) => self::$_mCORAL_BLOCK = $v, $values["coral_block"]);
		self::unsafeAssign(fn(FloorCoralFan $v) => self::$_mCORAL_FAN = $v, $values["coral_fan"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mCORNFLOWER = $v, $values["cornflower"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCRACKED_DEEPSLATE_BRICKS = $v, $values["cracked_deepslate_bricks"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCRACKED_DEEPSLATE_TILES = $v, $values["cracked_deepslate_tiles"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCRACKED_NETHER_BRICKS = $v, $values["cracked_nether_bricks"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCRACKED_POLISHED_BLACKSTONE_BRICKS = $v, $values["cracked_polished_blackstone_bricks"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCRACKED_STONE_BRICKS = $v, $values["cracked_stone_bricks"]);
		self::unsafeAssign(fn(CraftingTable $v) => self::$_mCRAFTING_TABLE = $v, $values["crafting_table"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mCRIMSON_BUTTON = $v, $values["crimson_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mCRIMSON_CEILING_CENTER_HANGING_SIGN = $v, $values["crimson_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mCRIMSON_CEILING_EDGES_HANGING_SIGN = $v, $values["crimson_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mCRIMSON_DOOR = $v, $values["crimson_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mCRIMSON_FENCE = $v, $values["crimson_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mCRIMSON_FENCE_GATE = $v, $values["crimson_fence_gate"]);
		self::unsafeAssign(fn(NetherFungus $v) => self::$_mCRIMSON_FUNGUS = $v, $values["crimson_fungus"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mCRIMSON_HYPHAE = $v, $values["crimson_hyphae"]);
		self::unsafeAssign(fn(Nylium $v) => self::$_mCRIMSON_NYLIUM = $v, $values["crimson_nylium"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mCRIMSON_PLANKS = $v, $values["crimson_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mCRIMSON_PRESSURE_PLATE = $v, $values["crimson_pressure_plate"]);
		self::unsafeAssign(fn(NetherRoots $v) => self::$_mCRIMSON_ROOTS = $v, $values["crimson_roots"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mCRIMSON_SIGN = $v, $values["crimson_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mCRIMSON_SLAB = $v, $values["crimson_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mCRIMSON_STAIRS = $v, $values["crimson_stairs"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mCRIMSON_STEM = $v, $values["crimson_stem"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mCRIMSON_TRAPDOOR = $v, $values["crimson_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mCRIMSON_WALL_HANGING_SIGN = $v, $values["crimson_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mCRIMSON_WALL_SIGN = $v, $values["crimson_wall_sign"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCRYING_OBSIDIAN = $v, $values["crying_obsidian"]);
		self::unsafeAssign(fn(Copper $v) => self::$_mCUT_COPPER = $v, $values["cut_copper"]);
		self::unsafeAssign(fn(CopperSlab $v) => self::$_mCUT_COPPER_SLAB = $v, $values["cut_copper_slab"]);
		self::unsafeAssign(fn(CopperStairs $v) => self::$_mCUT_COPPER_STAIRS = $v, $values["cut_copper_stairs"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCUT_RED_SANDSTONE = $v, $values["cut_red_sandstone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mCUT_RED_SANDSTONE_SLAB = $v, $values["cut_red_sandstone_slab"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mCUT_SANDSTONE = $v, $values["cut_sandstone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mCUT_SANDSTONE_SLAB = $v, $values["cut_sandstone_slab"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mDANDELION = $v, $values["dandelion"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mDARK_OAK_BUTTON = $v, $values["dark_oak_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mDARK_OAK_CEILING_CENTER_HANGING_SIGN = $v, $values["dark_oak_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mDARK_OAK_CEILING_EDGES_HANGING_SIGN = $v, $values["dark_oak_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mDARK_OAK_DOOR = $v, $values["dark_oak_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mDARK_OAK_FENCE = $v, $values["dark_oak_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mDARK_OAK_FENCE_GATE = $v, $values["dark_oak_fence_gate"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mDARK_OAK_LEAVES = $v, $values["dark_oak_leaves"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mDARK_OAK_LOG = $v, $values["dark_oak_log"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mDARK_OAK_PLANKS = $v, $values["dark_oak_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mDARK_OAK_PRESSURE_PLATE = $v, $values["dark_oak_pressure_plate"]);
		self::unsafeAssign(fn(Sapling $v) => self::$_mDARK_OAK_SAPLING = $v, $values["dark_oak_sapling"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mDARK_OAK_SIGN = $v, $values["dark_oak_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mDARK_OAK_SLAB = $v, $values["dark_oak_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mDARK_OAK_STAIRS = $v, $values["dark_oak_stairs"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mDARK_OAK_TRAPDOOR = $v, $values["dark_oak_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mDARK_OAK_WALL_HANGING_SIGN = $v, $values["dark_oak_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mDARK_OAK_WALL_SIGN = $v, $values["dark_oak_wall_sign"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mDARK_OAK_WOOD = $v, $values["dark_oak_wood"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mDARK_PRISMARINE = $v, $values["dark_prismarine"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mDARK_PRISMARINE_SLAB = $v, $values["dark_prismarine_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mDARK_PRISMARINE_STAIRS = $v, $values["dark_prismarine_stairs"]);
		self::unsafeAssign(fn(DaylightSensor $v) => self::$_mDAYLIGHT_SENSOR = $v, $values["daylight_sensor"]);
		self::unsafeAssign(fn(DeadBush $v) => self::$_mDEAD_BUSH = $v, $values["dead_bush"]);
		self::unsafeAssign(fn(SimplePillar $v) => self::$_mDEEPSLATE = $v, $values["deepslate"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mDEEPSLATE_BRICKS = $v, $values["deepslate_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mDEEPSLATE_BRICK_SLAB = $v, $values["deepslate_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mDEEPSLATE_BRICK_STAIRS = $v, $values["deepslate_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mDEEPSLATE_BRICK_WALL = $v, $values["deepslate_brick_wall"]);
		self::unsafeAssign(fn(CoalOre $v) => self::$_mDEEPSLATE_COAL_ORE = $v, $values["deepslate_coal_ore"]);
		self::unsafeAssign(fn(CopperOre $v) => self::$_mDEEPSLATE_COPPER_ORE = $v, $values["deepslate_copper_ore"]);
		self::unsafeAssign(fn(DiamondOre $v) => self::$_mDEEPSLATE_DIAMOND_ORE = $v, $values["deepslate_diamond_ore"]);
		self::unsafeAssign(fn(EmeraldOre $v) => self::$_mDEEPSLATE_EMERALD_ORE = $v, $values["deepslate_emerald_ore"]);
		self::unsafeAssign(fn(GoldOre $v) => self::$_mDEEPSLATE_GOLD_ORE = $v, $values["deepslate_gold_ore"]);
		self::unsafeAssign(fn(IronOre $v) => self::$_mDEEPSLATE_IRON_ORE = $v, $values["deepslate_iron_ore"]);
		self::unsafeAssign(fn(LapisOre $v) => self::$_mDEEPSLATE_LAPIS_LAZULI_ORE = $v, $values["deepslate_lapis_lazuli_ore"]);
		self::unsafeAssign(fn(RedstoneOre $v) => self::$_mDEEPSLATE_REDSTONE_ORE = $v, $values["deepslate_redstone_ore"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mDEEPSLATE_TILES = $v, $values["deepslate_tiles"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mDEEPSLATE_TILE_SLAB = $v, $values["deepslate_tile_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mDEEPSLATE_TILE_STAIRS = $v, $values["deepslate_tile_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mDEEPSLATE_TILE_WALL = $v, $values["deepslate_tile_wall"]);
		self::unsafeAssign(fn(DetectorRail $v) => self::$_mDETECTOR_RAIL = $v, $values["detector_rail"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mDIAMOND = $v, $values["diamond"]);
		self::unsafeAssign(fn(DiamondOre $v) => self::$_mDIAMOND_ORE = $v, $values["diamond_ore"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mDIORITE = $v, $values["diorite"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mDIORITE_SLAB = $v, $values["diorite_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mDIORITE_STAIRS = $v, $values["diorite_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mDIORITE_WALL = $v, $values["diorite_wall"]);
		self::unsafeAssign(fn(Dirt $v) => self::$_mDIRT = $v, $values["dirt"]);
		self::unsafeAssign(fn(DoublePitcherCrop $v) => self::$_mDOUBLE_PITCHER_CROP = $v, $values["double_pitcher_crop"]);
		self::unsafeAssign(fn(DoubleTallGrass $v) => self::$_mDOUBLE_TALLGRASS = $v, $values["double_tallgrass"]);
		self::unsafeAssign(fn(DragonEgg $v) => self::$_mDRAGON_EGG = $v, $values["dragon_egg"]);
		self::unsafeAssign(fn(DriedKelp $v) => self::$_mDRIED_KELP = $v, $values["dried_kelp"]);
		self::unsafeAssign(fn(DyedCandle $v) => self::$_mDYED_CANDLE = $v, $values["dyed_candle"]);
		self::unsafeAssign(fn(DyedShulkerBox $v) => self::$_mDYED_SHULKER_BOX = $v, $values["dyed_shulker_box"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ACTINIUM = $v, $values["element_actinium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ALUMINUM = $v, $values["element_aluminum"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_AMERICIUM = $v, $values["element_americium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ANTIMONY = $v, $values["element_antimony"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ARGON = $v, $values["element_argon"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ARSENIC = $v, $values["element_arsenic"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ASTATINE = $v, $values["element_astatine"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_BARIUM = $v, $values["element_barium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_BERKELIUM = $v, $values["element_berkelium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_BERYLLIUM = $v, $values["element_beryllium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_BISMUTH = $v, $values["element_bismuth"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_BOHRIUM = $v, $values["element_bohrium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_BORON = $v, $values["element_boron"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_BROMINE = $v, $values["element_bromine"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_CADMIUM = $v, $values["element_cadmium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_CALCIUM = $v, $values["element_calcium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_CALIFORNIUM = $v, $values["element_californium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_CARBON = $v, $values["element_carbon"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_CERIUM = $v, $values["element_cerium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_CESIUM = $v, $values["element_cesium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_CHLORINE = $v, $values["element_chlorine"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_CHROMIUM = $v, $values["element_chromium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_COBALT = $v, $values["element_cobalt"]);
		self::unsafeAssign(fn(ChemistryTable $v) => self::$_mELEMENT_CONSTRUCTOR = $v, $values["element_constructor"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_COPERNICIUM = $v, $values["element_copernicium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_COPPER = $v, $values["element_copper"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_CURIUM = $v, $values["element_curium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_DARMSTADTIUM = $v, $values["element_darmstadtium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_DUBNIUM = $v, $values["element_dubnium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_DYSPROSIUM = $v, $values["element_dysprosium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_EINSTEINIUM = $v, $values["element_einsteinium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ERBIUM = $v, $values["element_erbium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_EUROPIUM = $v, $values["element_europium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_FERMIUM = $v, $values["element_fermium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_FLEROVIUM = $v, $values["element_flerovium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_FLUORINE = $v, $values["element_fluorine"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_FRANCIUM = $v, $values["element_francium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_GADOLINIUM = $v, $values["element_gadolinium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_GALLIUM = $v, $values["element_gallium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_GERMANIUM = $v, $values["element_germanium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_GOLD = $v, $values["element_gold"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_HAFNIUM = $v, $values["element_hafnium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_HASSIUM = $v, $values["element_hassium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_HELIUM = $v, $values["element_helium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_HOLMIUM = $v, $values["element_holmium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_HYDROGEN = $v, $values["element_hydrogen"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_INDIUM = $v, $values["element_indium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_IODINE = $v, $values["element_iodine"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_IRIDIUM = $v, $values["element_iridium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_IRON = $v, $values["element_iron"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_KRYPTON = $v, $values["element_krypton"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_LANTHANUM = $v, $values["element_lanthanum"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_LAWRENCIUM = $v, $values["element_lawrencium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_LEAD = $v, $values["element_lead"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_LITHIUM = $v, $values["element_lithium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_LIVERMORIUM = $v, $values["element_livermorium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_LUTETIUM = $v, $values["element_lutetium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_MAGNESIUM = $v, $values["element_magnesium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_MANGANESE = $v, $values["element_manganese"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_MEITNERIUM = $v, $values["element_meitnerium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_MENDELEVIUM = $v, $values["element_mendelevium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_MERCURY = $v, $values["element_mercury"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_MOLYBDENUM = $v, $values["element_molybdenum"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_MOSCOVIUM = $v, $values["element_moscovium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_NEODYMIUM = $v, $values["element_neodymium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_NEON = $v, $values["element_neon"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_NEPTUNIUM = $v, $values["element_neptunium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_NICKEL = $v, $values["element_nickel"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_NIHONIUM = $v, $values["element_nihonium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_NIOBIUM = $v, $values["element_niobium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_NITROGEN = $v, $values["element_nitrogen"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_NOBELIUM = $v, $values["element_nobelium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_OGANESSON = $v, $values["element_oganesson"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_OSMIUM = $v, $values["element_osmium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_OXYGEN = $v, $values["element_oxygen"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_PALLADIUM = $v, $values["element_palladium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_PHOSPHORUS = $v, $values["element_phosphorus"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_PLATINUM = $v, $values["element_platinum"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_PLUTONIUM = $v, $values["element_plutonium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_POLONIUM = $v, $values["element_polonium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_POTASSIUM = $v, $values["element_potassium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_PRASEODYMIUM = $v, $values["element_praseodymium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_PROMETHIUM = $v, $values["element_promethium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_PROTACTINIUM = $v, $values["element_protactinium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_RADIUM = $v, $values["element_radium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_RADON = $v, $values["element_radon"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_RHENIUM = $v, $values["element_rhenium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_RHODIUM = $v, $values["element_rhodium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ROENTGENIUM = $v, $values["element_roentgenium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_RUBIDIUM = $v, $values["element_rubidium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_RUTHENIUM = $v, $values["element_ruthenium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_RUTHERFORDIUM = $v, $values["element_rutherfordium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_SAMARIUM = $v, $values["element_samarium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_SCANDIUM = $v, $values["element_scandium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_SEABORGIUM = $v, $values["element_seaborgium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_SELENIUM = $v, $values["element_selenium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_SILICON = $v, $values["element_silicon"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_SILVER = $v, $values["element_silver"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_SODIUM = $v, $values["element_sodium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_STRONTIUM = $v, $values["element_strontium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_SULFUR = $v, $values["element_sulfur"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_TANTALUM = $v, $values["element_tantalum"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_TECHNETIUM = $v, $values["element_technetium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_TELLURIUM = $v, $values["element_tellurium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_TENNESSINE = $v, $values["element_tennessine"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_TERBIUM = $v, $values["element_terbium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_THALLIUM = $v, $values["element_thallium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_THORIUM = $v, $values["element_thorium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_THULIUM = $v, $values["element_thulium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_TIN = $v, $values["element_tin"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_TITANIUM = $v, $values["element_titanium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_TUNGSTEN = $v, $values["element_tungsten"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_URANIUM = $v, $values["element_uranium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_VANADIUM = $v, $values["element_vanadium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_XENON = $v, $values["element_xenon"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_YTTERBIUM = $v, $values["element_ytterbium"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_YTTRIUM = $v, $values["element_yttrium"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mELEMENT_ZERO = $v, $values["element_zero"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ZINC = $v, $values["element_zinc"]);
		self::unsafeAssign(fn(Element $v) => self::$_mELEMENT_ZIRCONIUM = $v, $values["element_zirconium"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mEMERALD = $v, $values["emerald"]);
		self::unsafeAssign(fn(EmeraldOre $v) => self::$_mEMERALD_ORE = $v, $values["emerald_ore"]);
		self::unsafeAssign(fn(EnchantingTable $v) => self::$_mENCHANTING_TABLE = $v, $values["enchanting_table"]);
		self::unsafeAssign(fn(EnderChest $v) => self::$_mENDER_CHEST = $v, $values["ender_chest"]);
		self::unsafeAssign(fn(EndPortalFrame $v) => self::$_mEND_PORTAL_FRAME = $v, $values["end_portal_frame"]);
		self::unsafeAssign(fn(EndRod $v) => self::$_mEND_ROD = $v, $values["end_rod"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mEND_STONE = $v, $values["end_stone"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mEND_STONE_BRICKS = $v, $values["end_stone_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mEND_STONE_BRICK_SLAB = $v, $values["end_stone_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mEND_STONE_BRICK_STAIRS = $v, $values["end_stone_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mEND_STONE_BRICK_WALL = $v, $values["end_stone_brick_wall"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mFAKE_WOODEN_SLAB = $v, $values["fake_wooden_slab"]);
		self::unsafeAssign(fn(Farmland $v) => self::$_mFARMLAND = $v, $values["farmland"]);
		self::unsafeAssign(fn(TallGrass $v) => self::$_mFERN = $v, $values["fern"]);
		self::unsafeAssign(fn(Fire $v) => self::$_mFIRE = $v, $values["fire"]);
		self::unsafeAssign(fn(FletchingTable $v) => self::$_mFLETCHING_TABLE = $v, $values["fletching_table"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mFLOWERING_AZALEA_LEAVES = $v, $values["flowering_azalea_leaves"]);
		self::unsafeAssign(fn(FlowerPot $v) => self::$_mFLOWER_POT = $v, $values["flower_pot"]);
		self::unsafeAssign(fn(Froglight $v) => self::$_mFROGLIGHT = $v, $values["froglight"]);
		self::unsafeAssign(fn(FrostedIce $v) => self::$_mFROSTED_ICE = $v, $values["frosted_ice"]);
		self::unsafeAssign(fn(Furnace $v) => self::$_mFURNACE = $v, $values["furnace"]);
		self::unsafeAssign(fn(GildedBlackstone $v) => self::$_mGILDED_BLACKSTONE = $v, $values["gilded_blackstone"]);
		self::unsafeAssign(fn(Glass $v) => self::$_mGLASS = $v, $values["glass"]);
		self::unsafeAssign(fn(GlassPane $v) => self::$_mGLASS_PANE = $v, $values["glass_pane"]);
		self::unsafeAssign(fn(GlazedTerracotta $v) => self::$_mGLAZED_TERRACOTTA = $v, $values["glazed_terracotta"]);
		self::unsafeAssign(fn(ItemFrame $v) => self::$_mGLOWING_ITEM_FRAME = $v, $values["glowing_item_frame"]);
		self::unsafeAssign(fn(GlowingObsidian $v) => self::$_mGLOWING_OBSIDIAN = $v, $values["glowing_obsidian"]);
		self::unsafeAssign(fn(Glowstone $v) => self::$_mGLOWSTONE = $v, $values["glowstone"]);
		self::unsafeAssign(fn(GlowLichen $v) => self::$_mGLOW_LICHEN = $v, $values["glow_lichen"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mGOLD = $v, $values["gold"]);
		self::unsafeAssign(fn(GoldOre $v) => self::$_mGOLD_ORE = $v, $values["gold_ore"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mGRANITE = $v, $values["granite"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mGRANITE_SLAB = $v, $values["granite_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mGRANITE_STAIRS = $v, $values["granite_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mGRANITE_WALL = $v, $values["granite_wall"]);
		self::unsafeAssign(fn(Grass $v) => self::$_mGRASS = $v, $values["grass"]);
		self::unsafeAssign(fn(GrassPath $v) => self::$_mGRASS_PATH = $v, $values["grass_path"]);
		self::unsafeAssign(fn(Gravel $v) => self::$_mGRAVEL = $v, $values["gravel"]);
		self::unsafeAssign(fn(Torch $v) => self::$_mGREEN_TORCH = $v, $values["green_torch"]);
		self::unsafeAssign(fn(HangingRoots $v) => self::$_mHANGING_ROOTS = $v, $values["hanging_roots"]);
		self::unsafeAssign(fn(HardenedClay $v) => self::$_mHARDENED_CLAY = $v, $values["hardened_clay"]);
		self::unsafeAssign(fn(HardenedGlass $v) => self::$_mHARDENED_GLASS = $v, $values["hardened_glass"]);
		self::unsafeAssign(fn(HardenedGlassPane $v) => self::$_mHARDENED_GLASS_PANE = $v, $values["hardened_glass_pane"]);
		self::unsafeAssign(fn(HayBale $v) => self::$_mHAY_BALE = $v, $values["hay_bale"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mHONEYCOMB = $v, $values["honeycomb"]);
		self::unsafeAssign(fn(Hopper $v) => self::$_mHOPPER = $v, $values["hopper"]);
		self::unsafeAssign(fn(Ice $v) => self::$_mICE = $v, $values["ice"]);
		self::unsafeAssign(fn(InfestedStone $v) => self::$_mINFESTED_CHISELED_STONE_BRICK = $v, $values["infested_chiseled_stone_brick"]);
		self::unsafeAssign(fn(InfestedStone $v) => self::$_mINFESTED_COBBLESTONE = $v, $values["infested_cobblestone"]);
		self::unsafeAssign(fn(InfestedStone $v) => self::$_mINFESTED_CRACKED_STONE_BRICK = $v, $values["infested_cracked_stone_brick"]);
		self::unsafeAssign(fn(InfestedPillar $v) => self::$_mINFESTED_DEEPSLATE = $v, $values["infested_deepslate"]);
		self::unsafeAssign(fn(InfestedStone $v) => self::$_mINFESTED_MOSSY_STONE_BRICK = $v, $values["infested_mossy_stone_brick"]);
		self::unsafeAssign(fn(InfestedStone $v) => self::$_mINFESTED_STONE = $v, $values["infested_stone"]);
		self::unsafeAssign(fn(InfestedStone $v) => self::$_mINFESTED_STONE_BRICK = $v, $values["infested_stone_brick"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mINFO_UPDATE = $v, $values["info_update"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mINFO_UPDATE2 = $v, $values["info_update2"]);
		self::unsafeAssign(fn(Transparent $v) => self::$_mINVISIBLE_BEDROCK = $v, $values["invisible_bedrock"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mIRON = $v, $values["iron"]);
		self::unsafeAssign(fn(Thin $v) => self::$_mIRON_BARS = $v, $values["iron_bars"]);
		self::unsafeAssign(fn(Door $v) => self::$_mIRON_DOOR = $v, $values["iron_door"]);
		self::unsafeAssign(fn(IronOre $v) => self::$_mIRON_ORE = $v, $values["iron_ore"]);
		self::unsafeAssign(fn(Trapdoor $v) => self::$_mIRON_TRAPDOOR = $v, $values["iron_trapdoor"]);
		self::unsafeAssign(fn(ItemFrame $v) => self::$_mITEM_FRAME = $v, $values["item_frame"]);
		self::unsafeAssign(fn(Jukebox $v) => self::$_mJUKEBOX = $v, $values["jukebox"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mJUNGLE_BUTTON = $v, $values["jungle_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mJUNGLE_CEILING_CENTER_HANGING_SIGN = $v, $values["jungle_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mJUNGLE_CEILING_EDGES_HANGING_SIGN = $v, $values["jungle_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mJUNGLE_DOOR = $v, $values["jungle_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mJUNGLE_FENCE = $v, $values["jungle_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mJUNGLE_FENCE_GATE = $v, $values["jungle_fence_gate"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mJUNGLE_LEAVES = $v, $values["jungle_leaves"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mJUNGLE_LOG = $v, $values["jungle_log"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mJUNGLE_PLANKS = $v, $values["jungle_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mJUNGLE_PRESSURE_PLATE = $v, $values["jungle_pressure_plate"]);
		self::unsafeAssign(fn(Sapling $v) => self::$_mJUNGLE_SAPLING = $v, $values["jungle_sapling"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mJUNGLE_SIGN = $v, $values["jungle_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mJUNGLE_SLAB = $v, $values["jungle_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mJUNGLE_STAIRS = $v, $values["jungle_stairs"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mJUNGLE_TRAPDOOR = $v, $values["jungle_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mJUNGLE_WALL_HANGING_SIGN = $v, $values["jungle_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mJUNGLE_WALL_SIGN = $v, $values["jungle_wall_sign"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mJUNGLE_WOOD = $v, $values["jungle_wood"]);
		self::unsafeAssign(fn(ChemistryTable $v) => self::$_mLAB_TABLE = $v, $values["lab_table"]);
		self::unsafeAssign(fn(Ladder $v) => self::$_mLADDER = $v, $values["ladder"]);
		self::unsafeAssign(fn(Lantern $v) => self::$_mLANTERN = $v, $values["lantern"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mLAPIS_LAZULI = $v, $values["lapis_lazuli"]);
		self::unsafeAssign(fn(LapisOre $v) => self::$_mLAPIS_LAZULI_ORE = $v, $values["lapis_lazuli_ore"]);
		self::unsafeAssign(fn(DoubleTallGrass $v) => self::$_mLARGE_FERN = $v, $values["large_fern"]);
		self::unsafeAssign(fn(Lava $v) => self::$_mLAVA = $v, $values["lava"]);
		self::unsafeAssign(fn(LavaCauldron $v) => self::$_mLAVA_CAULDRON = $v, $values["lava_cauldron"]);
		self::unsafeAssign(fn(Lectern $v) => self::$_mLECTERN = $v, $values["lectern"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mLEGACY_STONECUTTER = $v, $values["legacy_stonecutter"]);
		self::unsafeAssign(fn(Lever $v) => self::$_mLEVER = $v, $values["lever"]);
		self::unsafeAssign(fn(Light $v) => self::$_mLIGHT = $v, $values["light"]);
		self::unsafeAssign(fn(LightningRod $v) => self::$_mLIGHTNING_ROD = $v, $values["lightning_rod"]);
		self::unsafeAssign(fn(DoublePlant $v) => self::$_mLILAC = $v, $values["lilac"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mLILY_OF_THE_VALLEY = $v, $values["lily_of_the_valley"]);
		self::unsafeAssign(fn(WaterLily $v) => self::$_mLILY_PAD = $v, $values["lily_pad"]);
		self::unsafeAssign(fn(LitPumpkin $v) => self::$_mLIT_PUMPKIN = $v, $values["lit_pumpkin"]);
		self::unsafeAssign(fn(Loom $v) => self::$_mLOOM = $v, $values["loom"]);
		self::unsafeAssign(fn(Magma $v) => self::$_mMAGMA = $v, $values["magma"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mMANGROVE_BUTTON = $v, $values["mangrove_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mMANGROVE_CEILING_CENTER_HANGING_SIGN = $v, $values["mangrove_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mMANGROVE_CEILING_EDGES_HANGING_SIGN = $v, $values["mangrove_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mMANGROVE_DOOR = $v, $values["mangrove_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mMANGROVE_FENCE = $v, $values["mangrove_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mMANGROVE_FENCE_GATE = $v, $values["mangrove_fence_gate"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mMANGROVE_LEAVES = $v, $values["mangrove_leaves"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mMANGROVE_LOG = $v, $values["mangrove_log"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mMANGROVE_PLANKS = $v, $values["mangrove_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mMANGROVE_PRESSURE_PLATE = $v, $values["mangrove_pressure_plate"]);
		self::unsafeAssign(fn(MangroveRoots $v) => self::$_mMANGROVE_ROOTS = $v, $values["mangrove_roots"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mMANGROVE_SIGN = $v, $values["mangrove_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mMANGROVE_SLAB = $v, $values["mangrove_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mMANGROVE_STAIRS = $v, $values["mangrove_stairs"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mMANGROVE_TRAPDOOR = $v, $values["mangrove_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mMANGROVE_WALL_HANGING_SIGN = $v, $values["mangrove_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mMANGROVE_WALL_SIGN = $v, $values["mangrove_wall_sign"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mMANGROVE_WOOD = $v, $values["mangrove_wood"]);
		self::unsafeAssign(fn(ChemistryTable $v) => self::$_mMATERIAL_REDUCER = $v, $values["material_reducer"]);
		self::unsafeAssign(fn(Melon $v) => self::$_mMELON = $v, $values["melon"]);
		self::unsafeAssign(fn(MelonStem $v) => self::$_mMELON_STEM = $v, $values["melon_stem"]);
		self::unsafeAssign(fn(MobHead $v) => self::$_mMOB_HEAD = $v, $values["mob_head"]);
		self::unsafeAssign(fn(MonsterSpawner $v) => self::$_mMONSTER_SPAWNER = $v, $values["monster_spawner"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mMOSSY_COBBLESTONE = $v, $values["mossy_cobblestone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mMOSSY_COBBLESTONE_SLAB = $v, $values["mossy_cobblestone_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mMOSSY_COBBLESTONE_STAIRS = $v, $values["mossy_cobblestone_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mMOSSY_COBBLESTONE_WALL = $v, $values["mossy_cobblestone_wall"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mMOSSY_STONE_BRICKS = $v, $values["mossy_stone_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mMOSSY_STONE_BRICK_SLAB = $v, $values["mossy_stone_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mMOSSY_STONE_BRICK_STAIRS = $v, $values["mossy_stone_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mMOSSY_STONE_BRICK_WALL = $v, $values["mossy_stone_brick_wall"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mMUD = $v, $values["mud"]);
		self::unsafeAssign(fn(SimplePillar $v) => self::$_mMUDDY_MANGROVE_ROOTS = $v, $values["muddy_mangrove_roots"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mMUD_BRICKS = $v, $values["mud_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mMUD_BRICK_SLAB = $v, $values["mud_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mMUD_BRICK_STAIRS = $v, $values["mud_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mMUD_BRICK_WALL = $v, $values["mud_brick_wall"]);
		self::unsafeAssign(fn(MushroomStem $v) => self::$_mMUSHROOM_STEM = $v, $values["mushroom_stem"]);
		self::unsafeAssign(fn(Mycelium $v) => self::$_mMYCELIUM = $v, $values["mycelium"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mNETHERITE = $v, $values["netherite"]);
		self::unsafeAssign(fn(Netherrack $v) => self::$_mNETHERRACK = $v, $values["netherrack"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mNETHER_BRICKS = $v, $values["nether_bricks"]);
		self::unsafeAssign(fn(Fence $v) => self::$_mNETHER_BRICK_FENCE = $v, $values["nether_brick_fence"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mNETHER_BRICK_SLAB = $v, $values["nether_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mNETHER_BRICK_STAIRS = $v, $values["nether_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mNETHER_BRICK_WALL = $v, $values["nether_brick_wall"]);
		self::unsafeAssign(fn(NetherGoldOre $v) => self::$_mNETHER_GOLD_ORE = $v, $values["nether_gold_ore"]);
		self::unsafeAssign(fn(NetherPortal $v) => self::$_mNETHER_PORTAL = $v, $values["nether_portal"]);
		self::unsafeAssign(fn(NetherQuartzOre $v) => self::$_mNETHER_QUARTZ_ORE = $v, $values["nether_quartz_ore"]);
		self::unsafeAssign(fn(NetherReactor $v) => self::$_mNETHER_REACTOR_CORE = $v, $values["nether_reactor_core"]);
		self::unsafeAssign(fn(NetherSprouts $v) => self::$_mNETHER_SPROUTS = $v, $values["nether_sprouts"]);
		self::unsafeAssign(fn(NetherWartPlant $v) => self::$_mNETHER_WART = $v, $values["nether_wart"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mNETHER_WART_BLOCK = $v, $values["nether_wart_block"]);
		self::unsafeAssign(fn(Note $v) => self::$_mNOTE_BLOCK = $v, $values["note_block"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mOAK_BUTTON = $v, $values["oak_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mOAK_CEILING_CENTER_HANGING_SIGN = $v, $values["oak_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mOAK_CEILING_EDGES_HANGING_SIGN = $v, $values["oak_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mOAK_DOOR = $v, $values["oak_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mOAK_FENCE = $v, $values["oak_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mOAK_FENCE_GATE = $v, $values["oak_fence_gate"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mOAK_LEAVES = $v, $values["oak_leaves"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mOAK_LOG = $v, $values["oak_log"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mOAK_PLANKS = $v, $values["oak_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mOAK_PRESSURE_PLATE = $v, $values["oak_pressure_plate"]);
		self::unsafeAssign(fn(Sapling $v) => self::$_mOAK_SAPLING = $v, $values["oak_sapling"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mOAK_SIGN = $v, $values["oak_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mOAK_SLAB = $v, $values["oak_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mOAK_STAIRS = $v, $values["oak_stairs"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mOAK_TRAPDOOR = $v, $values["oak_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mOAK_WALL_HANGING_SIGN = $v, $values["oak_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mOAK_WALL_SIGN = $v, $values["oak_wall_sign"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mOAK_WOOD = $v, $values["oak_wood"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mOBSIDIAN = $v, $values["obsidian"]);
		self::unsafeAssign(fn(OminousFloorBanner $v) => self::$_mOMINOUS_BANNER = $v, $values["ominous_banner"]);
		self::unsafeAssign(fn(OminousWallBanner $v) => self::$_mOMINOUS_WALL_BANNER = $v, $values["ominous_wall_banner"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mORANGE_TULIP = $v, $values["orange_tulip"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mOXEYE_DAISY = $v, $values["oxeye_daisy"]);
		self::unsafeAssign(fn(PackedIce $v) => self::$_mPACKED_ICE = $v, $values["packed_ice"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPACKED_MUD = $v, $values["packed_mud"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mPALE_OAK_BUTTON = $v, $values["pale_oak_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mPALE_OAK_CEILING_CENTER_HANGING_SIGN = $v, $values["pale_oak_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mPALE_OAK_CEILING_EDGES_HANGING_SIGN = $v, $values["pale_oak_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mPALE_OAK_DOOR = $v, $values["pale_oak_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mPALE_OAK_FENCE = $v, $values["pale_oak_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mPALE_OAK_FENCE_GATE = $v, $values["pale_oak_fence_gate"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mPALE_OAK_LEAVES = $v, $values["pale_oak_leaves"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mPALE_OAK_LOG = $v, $values["pale_oak_log"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mPALE_OAK_PLANKS = $v, $values["pale_oak_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mPALE_OAK_PRESSURE_PLATE = $v, $values["pale_oak_pressure_plate"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mPALE_OAK_SIGN = $v, $values["pale_oak_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mPALE_OAK_SLAB = $v, $values["pale_oak_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mPALE_OAK_STAIRS = $v, $values["pale_oak_stairs"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mPALE_OAK_TRAPDOOR = $v, $values["pale_oak_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mPALE_OAK_WALL_HANGING_SIGN = $v, $values["pale_oak_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mPALE_OAK_WALL_SIGN = $v, $values["pale_oak_wall_sign"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mPALE_OAK_WOOD = $v, $values["pale_oak_wood"]);
		self::unsafeAssign(fn(DoublePlant $v) => self::$_mPEONY = $v, $values["peony"]);
		self::unsafeAssign(fn(PinkPetals $v) => self::$_mPINK_PETALS = $v, $values["pink_petals"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mPINK_TULIP = $v, $values["pink_tulip"]);
		self::unsafeAssign(fn(PitcherCrop $v) => self::$_mPITCHER_CROP = $v, $values["pitcher_crop"]);
		self::unsafeAssign(fn(DoublePlant $v) => self::$_mPITCHER_PLANT = $v, $values["pitcher_plant"]);
		self::unsafeAssign(fn(Podzol $v) => self::$_mPODZOL = $v, $values["podzol"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPOLISHED_ANDESITE = $v, $values["polished_andesite"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPOLISHED_ANDESITE_SLAB = $v, $values["polished_andesite_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPOLISHED_ANDESITE_STAIRS = $v, $values["polished_andesite_stairs"]);
		self::unsafeAssign(fn(SimplePillar $v) => self::$_mPOLISHED_BASALT = $v, $values["polished_basalt"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPOLISHED_BLACKSTONE = $v, $values["polished_blackstone"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPOLISHED_BLACKSTONE_BRICKS = $v, $values["polished_blackstone_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPOLISHED_BLACKSTONE_BRICK_SLAB = $v, $values["polished_blackstone_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPOLISHED_BLACKSTONE_BRICK_STAIRS = $v, $values["polished_blackstone_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mPOLISHED_BLACKSTONE_BRICK_WALL = $v, $values["polished_blackstone_brick_wall"]);
		self::unsafeAssign(fn(StoneButton $v) => self::$_mPOLISHED_BLACKSTONE_BUTTON = $v, $values["polished_blackstone_button"]);
		self::unsafeAssign(fn(StonePressurePlate $v) => self::$_mPOLISHED_BLACKSTONE_PRESSURE_PLATE = $v, $values["polished_blackstone_pressure_plate"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPOLISHED_BLACKSTONE_SLAB = $v, $values["polished_blackstone_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPOLISHED_BLACKSTONE_STAIRS = $v, $values["polished_blackstone_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mPOLISHED_BLACKSTONE_WALL = $v, $values["polished_blackstone_wall"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPOLISHED_DEEPSLATE = $v, $values["polished_deepslate"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPOLISHED_DEEPSLATE_SLAB = $v, $values["polished_deepslate_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPOLISHED_DEEPSLATE_STAIRS = $v, $values["polished_deepslate_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mPOLISHED_DEEPSLATE_WALL = $v, $values["polished_deepslate_wall"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPOLISHED_DIORITE = $v, $values["polished_diorite"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPOLISHED_DIORITE_SLAB = $v, $values["polished_diorite_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPOLISHED_DIORITE_STAIRS = $v, $values["polished_diorite_stairs"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPOLISHED_GRANITE = $v, $values["polished_granite"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPOLISHED_GRANITE_SLAB = $v, $values["polished_granite_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPOLISHED_GRANITE_STAIRS = $v, $values["polished_granite_stairs"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPOLISHED_TUFF = $v, $values["polished_tuff"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPOLISHED_TUFF_SLAB = $v, $values["polished_tuff_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPOLISHED_TUFF_STAIRS = $v, $values["polished_tuff_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mPOLISHED_TUFF_WALL = $v, $values["polished_tuff_wall"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mPOPPY = $v, $values["poppy"]);
		self::unsafeAssign(fn(Potato $v) => self::$_mPOTATOES = $v, $values["potatoes"]);
		self::unsafeAssign(fn(PotionCauldron $v) => self::$_mPOTION_CAULDRON = $v, $values["potion_cauldron"]);
		self::unsafeAssign(fn(PoweredRail $v) => self::$_mPOWERED_RAIL = $v, $values["powered_rail"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPRISMARINE = $v, $values["prismarine"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPRISMARINE_BRICKS = $v, $values["prismarine_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPRISMARINE_BRICKS_SLAB = $v, $values["prismarine_bricks_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPRISMARINE_BRICKS_STAIRS = $v, $values["prismarine_bricks_stairs"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPRISMARINE_SLAB = $v, $values["prismarine_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPRISMARINE_STAIRS = $v, $values["prismarine_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mPRISMARINE_WALL = $v, $values["prismarine_wall"]);
		self::unsafeAssign(fn(Pumpkin $v) => self::$_mPUMPKIN = $v, $values["pumpkin"]);
		self::unsafeAssign(fn(PumpkinStem $v) => self::$_mPUMPKIN_STEM = $v, $values["pumpkin_stem"]);
		self::unsafeAssign(fn(Torch $v) => self::$_mPURPLE_TORCH = $v, $values["purple_torch"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mPURPUR = $v, $values["purpur"]);
		self::unsafeAssign(fn(SimplePillar $v) => self::$_mPURPUR_PILLAR = $v, $values["purpur_pillar"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mPURPUR_SLAB = $v, $values["purpur_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mPURPUR_STAIRS = $v, $values["purpur_stairs"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mQUARTZ = $v, $values["quartz"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mQUARTZ_BRICKS = $v, $values["quartz_bricks"]);
		self::unsafeAssign(fn(SimplePillar $v) => self::$_mQUARTZ_PILLAR = $v, $values["quartz_pillar"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mQUARTZ_SLAB = $v, $values["quartz_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mQUARTZ_STAIRS = $v, $values["quartz_stairs"]);
		self::unsafeAssign(fn(Rail $v) => self::$_mRAIL = $v, $values["rail"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mRAW_COPPER = $v, $values["raw_copper"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mRAW_GOLD = $v, $values["raw_gold"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mRAW_IRON = $v, $values["raw_iron"]);
		self::unsafeAssign(fn(Redstone $v) => self::$_mREDSTONE = $v, $values["redstone"]);
		self::unsafeAssign(fn(RedstoneComparator $v) => self::$_mREDSTONE_COMPARATOR = $v, $values["redstone_comparator"]);
		self::unsafeAssign(fn(RedstoneLamp $v) => self::$_mREDSTONE_LAMP = $v, $values["redstone_lamp"]);
		self::unsafeAssign(fn(RedstoneOre $v) => self::$_mREDSTONE_ORE = $v, $values["redstone_ore"]);
		self::unsafeAssign(fn(RedstoneRepeater $v) => self::$_mREDSTONE_REPEATER = $v, $values["redstone_repeater"]);
		self::unsafeAssign(fn(RedstoneTorch $v) => self::$_mREDSTONE_TORCH = $v, $values["redstone_torch"]);
		self::unsafeAssign(fn(RedstoneWire $v) => self::$_mREDSTONE_WIRE = $v, $values["redstone_wire"]);
		self::unsafeAssign(fn(RedMushroom $v) => self::$_mRED_MUSHROOM = $v, $values["red_mushroom"]);
		self::unsafeAssign(fn(RedMushroomBlock $v) => self::$_mRED_MUSHROOM_BLOCK = $v, $values["red_mushroom_block"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mRED_NETHER_BRICKS = $v, $values["red_nether_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mRED_NETHER_BRICK_SLAB = $v, $values["red_nether_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mRED_NETHER_BRICK_STAIRS = $v, $values["red_nether_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mRED_NETHER_BRICK_WALL = $v, $values["red_nether_brick_wall"]);
		self::unsafeAssign(fn(Sand $v) => self::$_mRED_SAND = $v, $values["red_sand"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mRED_SANDSTONE = $v, $values["red_sandstone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mRED_SANDSTONE_SLAB = $v, $values["red_sandstone_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mRED_SANDSTONE_STAIRS = $v, $values["red_sandstone_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mRED_SANDSTONE_WALL = $v, $values["red_sandstone_wall"]);
		self::unsafeAssign(fn(Torch $v) => self::$_mRED_TORCH = $v, $values["red_torch"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mRED_TULIP = $v, $values["red_tulip"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mREINFORCED_DEEPSLATE = $v, $values["reinforced_deepslate"]);
		self::unsafeAssign(fn(Reserved6 $v) => self::$_mRESERVED6 = $v, $values["reserved6"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mRESIN = $v, $values["resin"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mRESIN_BRICKS = $v, $values["resin_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mRESIN_BRICK_SLAB = $v, $values["resin_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mRESIN_BRICK_STAIRS = $v, $values["resin_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mRESIN_BRICK_WALL = $v, $values["resin_brick_wall"]);
		self::unsafeAssign(fn(ResinClump $v) => self::$_mRESIN_CLUMP = $v, $values["resin_clump"]);
		self::unsafeAssign(fn(RespawnAnchor $v) => self::$_mRESPAWN_ANCHOR = $v, $values["respawn_anchor"]);
		self::unsafeAssign(fn(DoublePlant $v) => self::$_mROSE_BUSH = $v, $values["rose_bush"]);
		self::unsafeAssign(fn(Sand $v) => self::$_mSAND = $v, $values["sand"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSANDSTONE = $v, $values["sandstone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mSANDSTONE_SLAB = $v, $values["sandstone_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mSANDSTONE_STAIRS = $v, $values["sandstone_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mSANDSTONE_WALL = $v, $values["sandstone_wall"]);
		self::unsafeAssign(fn(Sculk $v) => self::$_mSCULK = $v, $values["sculk"]);
		self::unsafeAssign(fn(SeaLantern $v) => self::$_mSEA_LANTERN = $v, $values["sea_lantern"]);
		self::unsafeAssign(fn(SeaPickle $v) => self::$_mSEA_PICKLE = $v, $values["sea_pickle"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSHROOMLIGHT = $v, $values["shroomlight"]);
		self::unsafeAssign(fn(ShulkerBox $v) => self::$_mSHULKER_BOX = $v, $values["shulker_box"]);
		self::unsafeAssign(fn(Slime $v) => self::$_mSLIME = $v, $values["slime"]);
		self::unsafeAssign(fn(SmallDripleaf $v) => self::$_mSMALL_DRIPLEAF = $v, $values["small_dripleaf"]);
		self::unsafeAssign(fn(SmithingTable $v) => self::$_mSMITHING_TABLE = $v, $values["smithing_table"]);
		self::unsafeAssign(fn(Furnace $v) => self::$_mSMOKER = $v, $values["smoker"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSMOOTH_BASALT = $v, $values["smooth_basalt"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSMOOTH_QUARTZ = $v, $values["smooth_quartz"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mSMOOTH_QUARTZ_SLAB = $v, $values["smooth_quartz_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mSMOOTH_QUARTZ_STAIRS = $v, $values["smooth_quartz_stairs"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSMOOTH_RED_SANDSTONE = $v, $values["smooth_red_sandstone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mSMOOTH_RED_SANDSTONE_SLAB = $v, $values["smooth_red_sandstone_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mSMOOTH_RED_SANDSTONE_STAIRS = $v, $values["smooth_red_sandstone_stairs"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSMOOTH_SANDSTONE = $v, $values["smooth_sandstone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mSMOOTH_SANDSTONE_SLAB = $v, $values["smooth_sandstone_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mSMOOTH_SANDSTONE_STAIRS = $v, $values["smooth_sandstone_stairs"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSMOOTH_STONE = $v, $values["smooth_stone"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mSMOOTH_STONE_SLAB = $v, $values["smooth_stone_slab"]);
		self::unsafeAssign(fn(Snow $v) => self::$_mSNOW = $v, $values["snow"]);
		self::unsafeAssign(fn(SnowLayer $v) => self::$_mSNOW_LAYER = $v, $values["snow_layer"]);
		self::unsafeAssign(fn(SoulCampfire $v) => self::$_mSOUL_CAMPFIRE = $v, $values["soul_campfire"]);
		self::unsafeAssign(fn(SoulFire $v) => self::$_mSOUL_FIRE = $v, $values["soul_fire"]);
		self::unsafeAssign(fn(Lantern $v) => self::$_mSOUL_LANTERN = $v, $values["soul_lantern"]);
		self::unsafeAssign(fn(SoulSand $v) => self::$_mSOUL_SAND = $v, $values["soul_sand"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSOUL_SOIL = $v, $values["soul_soil"]);
		self::unsafeAssign(fn(Torch $v) => self::$_mSOUL_TORCH = $v, $values["soul_torch"]);
		self::unsafeAssign(fn(Sponge $v) => self::$_mSPONGE = $v, $values["sponge"]);
		self::unsafeAssign(fn(SporeBlossom $v) => self::$_mSPORE_BLOSSOM = $v, $values["spore_blossom"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mSPRUCE_BUTTON = $v, $values["spruce_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mSPRUCE_CEILING_CENTER_HANGING_SIGN = $v, $values["spruce_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mSPRUCE_CEILING_EDGES_HANGING_SIGN = $v, $values["spruce_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mSPRUCE_DOOR = $v, $values["spruce_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mSPRUCE_FENCE = $v, $values["spruce_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mSPRUCE_FENCE_GATE = $v, $values["spruce_fence_gate"]);
		self::unsafeAssign(fn(Leaves $v) => self::$_mSPRUCE_LEAVES = $v, $values["spruce_leaves"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mSPRUCE_LOG = $v, $values["spruce_log"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mSPRUCE_PLANKS = $v, $values["spruce_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mSPRUCE_PRESSURE_PLATE = $v, $values["spruce_pressure_plate"]);
		self::unsafeAssign(fn(Sapling $v) => self::$_mSPRUCE_SAPLING = $v, $values["spruce_sapling"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mSPRUCE_SIGN = $v, $values["spruce_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mSPRUCE_SLAB = $v, $values["spruce_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mSPRUCE_STAIRS = $v, $values["spruce_stairs"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mSPRUCE_TRAPDOOR = $v, $values["spruce_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mSPRUCE_WALL_HANGING_SIGN = $v, $values["spruce_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mSPRUCE_WALL_SIGN = $v, $values["spruce_wall_sign"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mSPRUCE_WOOD = $v, $values["spruce_wood"]);
		self::unsafeAssign(fn(StainedHardenedClay $v) => self::$_mSTAINED_CLAY = $v, $values["stained_clay"]);
		self::unsafeAssign(fn(StainedGlass $v) => self::$_mSTAINED_GLASS = $v, $values["stained_glass"]);
		self::unsafeAssign(fn(StainedGlassPane $v) => self::$_mSTAINED_GLASS_PANE = $v, $values["stained_glass_pane"]);
		self::unsafeAssign(fn(StainedHardenedGlass $v) => self::$_mSTAINED_HARDENED_GLASS = $v, $values["stained_hardened_glass"]);
		self::unsafeAssign(fn(StainedHardenedGlassPane $v) => self::$_mSTAINED_HARDENED_GLASS_PANE = $v, $values["stained_hardened_glass_pane"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSTONE = $v, $values["stone"]);
		self::unsafeAssign(fn(Stonecutter $v) => self::$_mSTONECUTTER = $v, $values["stonecutter"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mSTONE_BRICKS = $v, $values["stone_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mSTONE_BRICK_SLAB = $v, $values["stone_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mSTONE_BRICK_STAIRS = $v, $values["stone_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mSTONE_BRICK_WALL = $v, $values["stone_brick_wall"]);
		self::unsafeAssign(fn(StoneButton $v) => self::$_mSTONE_BUTTON = $v, $values["stone_button"]);
		self::unsafeAssign(fn(StonePressurePlate $v) => self::$_mSTONE_PRESSURE_PLATE = $v, $values["stone_pressure_plate"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mSTONE_SLAB = $v, $values["stone_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mSTONE_STAIRS = $v, $values["stone_stairs"]);
		self::unsafeAssign(fn(StructureVoid $v) => self::$_mSTRUCTURE_VOID = $v, $values["structure_void"]);
		self::unsafeAssign(fn(Sugarcane $v) => self::$_mSUGARCANE = $v, $values["sugarcane"]);
		self::unsafeAssign(fn(DoublePlant $v) => self::$_mSUNFLOWER = $v, $values["sunflower"]);
		self::unsafeAssign(fn(SweetBerryBush $v) => self::$_mSWEET_BERRY_BUSH = $v, $values["sweet_berry_bush"]);
		self::unsafeAssign(fn(TallGrass $v) => self::$_mTALL_GRASS = $v, $values["tall_grass"]);
		self::unsafeAssign(fn(TintedGlass $v) => self::$_mTINTED_GLASS = $v, $values["tinted_glass"]);
		self::unsafeAssign(fn(TNT $v) => self::$_mTNT = $v, $values["tnt"]);
		self::unsafeAssign(fn(Torch $v) => self::$_mTORCH = $v, $values["torch"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mTORCHFLOWER = $v, $values["torchflower"]);
		self::unsafeAssign(fn(TorchflowerCrop $v) => self::$_mTORCHFLOWER_CROP = $v, $values["torchflower_crop"]);
		self::unsafeAssign(fn(TrappedChest $v) => self::$_mTRAPPED_CHEST = $v, $values["trapped_chest"]);
		self::unsafeAssign(fn(Tripwire $v) => self::$_mTRIPWIRE = $v, $values["tripwire"]);
		self::unsafeAssign(fn(TripwireHook $v) => self::$_mTRIPWIRE_HOOK = $v, $values["tripwire_hook"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mTUFF = $v, $values["tuff"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mTUFF_BRICKS = $v, $values["tuff_bricks"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mTUFF_BRICK_SLAB = $v, $values["tuff_brick_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mTUFF_BRICK_STAIRS = $v, $values["tuff_brick_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mTUFF_BRICK_WALL = $v, $values["tuff_brick_wall"]);
		self::unsafeAssign(fn(Slab $v) => self::$_mTUFF_SLAB = $v, $values["tuff_slab"]);
		self::unsafeAssign(fn(Stair $v) => self::$_mTUFF_STAIRS = $v, $values["tuff_stairs"]);
		self::unsafeAssign(fn(Wall $v) => self::$_mTUFF_WALL = $v, $values["tuff_wall"]);
		self::unsafeAssign(fn(NetherVines $v) => self::$_mTWISTING_VINES = $v, $values["twisting_vines"]);
		self::unsafeAssign(fn(UnderwaterTorch $v) => self::$_mUNDERWATER_TORCH = $v, $values["underwater_torch"]);
		self::unsafeAssign(fn(Vine $v) => self::$_mVINES = $v, $values["vines"]);
		self::unsafeAssign(fn(WallBanner $v) => self::$_mWALL_BANNER = $v, $values["wall_banner"]);
		self::unsafeAssign(fn(WallCoralFan $v) => self::$_mWALL_CORAL_FAN = $v, $values["wall_coral_fan"]);
		self::unsafeAssign(fn(WoodenButton $v) => self::$_mWARPED_BUTTON = $v, $values["warped_button"]);
		self::unsafeAssign(fn(CeilingCenterHangingSign $v) => self::$_mWARPED_CEILING_CENTER_HANGING_SIGN = $v, $values["warped_ceiling_center_hanging_sign"]);
		self::unsafeAssign(fn(CeilingEdgesHangingSign $v) => self::$_mWARPED_CEILING_EDGES_HANGING_SIGN = $v, $values["warped_ceiling_edges_hanging_sign"]);
		self::unsafeAssign(fn(WoodenDoor $v) => self::$_mWARPED_DOOR = $v, $values["warped_door"]);
		self::unsafeAssign(fn(WoodenFence $v) => self::$_mWARPED_FENCE = $v, $values["warped_fence"]);
		self::unsafeAssign(fn(FenceGate $v) => self::$_mWARPED_FENCE_GATE = $v, $values["warped_fence_gate"]);
		self::unsafeAssign(fn(NetherFungus $v) => self::$_mWARPED_FUNGUS = $v, $values["warped_fungus"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mWARPED_HYPHAE = $v, $values["warped_hyphae"]);
		self::unsafeAssign(fn(Nylium $v) => self::$_mWARPED_NYLIUM = $v, $values["warped_nylium"]);
		self::unsafeAssign(fn(Planks $v) => self::$_mWARPED_PLANKS = $v, $values["warped_planks"]);
		self::unsafeAssign(fn(WoodenPressurePlate $v) => self::$_mWARPED_PRESSURE_PLATE = $v, $values["warped_pressure_plate"]);
		self::unsafeAssign(fn(NetherRoots $v) => self::$_mWARPED_ROOTS = $v, $values["warped_roots"]);
		self::unsafeAssign(fn(FloorSign $v) => self::$_mWARPED_SIGN = $v, $values["warped_sign"]);
		self::unsafeAssign(fn(WoodenSlab $v) => self::$_mWARPED_SLAB = $v, $values["warped_slab"]);
		self::unsafeAssign(fn(WoodenStairs $v) => self::$_mWARPED_STAIRS = $v, $values["warped_stairs"]);
		self::unsafeAssign(fn(Wood $v) => self::$_mWARPED_STEM = $v, $values["warped_stem"]);
		self::unsafeAssign(fn(WoodenTrapdoor $v) => self::$_mWARPED_TRAPDOOR = $v, $values["warped_trapdoor"]);
		self::unsafeAssign(fn(WallHangingSign $v) => self::$_mWARPED_WALL_HANGING_SIGN = $v, $values["warped_wall_hanging_sign"]);
		self::unsafeAssign(fn(WallSign $v) => self::$_mWARPED_WALL_SIGN = $v, $values["warped_wall_sign"]);
		self::unsafeAssign(fn(Opaque $v) => self::$_mWARPED_WART_BLOCK = $v, $values["warped_wart_block"]);
		self::unsafeAssign(fn(Water $v) => self::$_mWATER = $v, $values["water"]);
		self::unsafeAssign(fn(WaterCauldron $v) => self::$_mWATER_CAULDRON = $v, $values["water_cauldron"]);
		self::unsafeAssign(fn(NetherVines $v) => self::$_mWEEPING_VINES = $v, $values["weeping_vines"]);
		self::unsafeAssign(fn(WeightedPressurePlateHeavy $v) => self::$_mWEIGHTED_PRESSURE_PLATE_HEAVY = $v, $values["weighted_pressure_plate_heavy"]);
		self::unsafeAssign(fn(WeightedPressurePlateLight $v) => self::$_mWEIGHTED_PRESSURE_PLATE_LIGHT = $v, $values["weighted_pressure_plate_light"]);
		self::unsafeAssign(fn(Wheat $v) => self::$_mWHEAT = $v, $values["wheat"]);
		self::unsafeAssign(fn(Flower $v) => self::$_mWHITE_TULIP = $v, $values["white_tulip"]);
		self::unsafeAssign(fn(WitherRose $v) => self::$_mWITHER_ROSE = $v, $values["wither_rose"]);
		self::unsafeAssign(fn(Wool $v) => self::$_mWOOL = $v, $values["wool"]);

	}

	/**
	 * @return Block[]
	 * @phpstan-return array<string, Block>
	 */
	public static function getAll() : array{
		if(!isset(self::$members)){ self::init(); }
		return array_map(VanillaBlocksInputs::cloneMember(...), self::$members);
	}

	public static function ACACIA_BUTTON() : WoodenButton{
		if(!isset(self::$_mACACIA_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_BUTTON);
	}

	public static function ACACIA_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mACACIA_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_CEILING_CENTER_HANGING_SIGN);
	}

	public static function ACACIA_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mACACIA_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_CEILING_EDGES_HANGING_SIGN);
	}

	public static function ACACIA_DOOR() : WoodenDoor{
		if(!isset(self::$_mACACIA_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_DOOR);
	}

	public static function ACACIA_FENCE() : WoodenFence{
		if(!isset(self::$_mACACIA_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_FENCE);
	}

	public static function ACACIA_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mACACIA_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_FENCE_GATE);
	}

	public static function ACACIA_LEAVES() : Leaves{
		if(!isset(self::$_mACACIA_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_LEAVES);
	}

	public static function ACACIA_LOG() : Wood{
		if(!isset(self::$_mACACIA_LOG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_LOG);
	}

	public static function ACACIA_PLANKS() : Planks{
		if(!isset(self::$_mACACIA_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_PLANKS);
	}

	public static function ACACIA_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mACACIA_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_PRESSURE_PLATE);
	}

	public static function ACACIA_SAPLING() : Sapling{
		if(!isset(self::$_mACACIA_SAPLING)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_SAPLING);
	}

	public static function ACACIA_SIGN() : FloorSign{
		if(!isset(self::$_mACACIA_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_SIGN);
	}

	public static function ACACIA_SLAB() : WoodenSlab{
		if(!isset(self::$_mACACIA_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_SLAB);
	}

	public static function ACACIA_STAIRS() : WoodenStairs{
		if(!isset(self::$_mACACIA_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_STAIRS);
	}

	public static function ACACIA_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mACACIA_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_TRAPDOOR);
	}

	public static function ACACIA_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mACACIA_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_WALL_HANGING_SIGN);
	}

	public static function ACACIA_WALL_SIGN() : WallSign{
		if(!isset(self::$_mACACIA_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_WALL_SIGN);
	}

	public static function ACACIA_WOOD() : Wood{
		if(!isset(self::$_mACACIA_WOOD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACACIA_WOOD);
	}

	public static function ACTIVATOR_RAIL() : ActivatorRail{
		if(!isset(self::$_mACTIVATOR_RAIL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mACTIVATOR_RAIL);
	}

	public static function AIR() : Air{
		if(!isset(self::$_mAIR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mAIR);
	}

	public static function ALLIUM() : Flower{
		if(!isset(self::$_mALLIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mALLIUM);
	}

	public static function ALL_SIDED_MUSHROOM_STEM() : MushroomStem{
		if(!isset(self::$_mALL_SIDED_MUSHROOM_STEM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mALL_SIDED_MUSHROOM_STEM);
	}

	public static function AMETHYST() : Opaque{
		if(!isset(self::$_mAMETHYST)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mAMETHYST);
	}

	public static function AMETHYST_CLUSTER() : AmethystCluster{
		if(!isset(self::$_mAMETHYST_CLUSTER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mAMETHYST_CLUSTER);
	}

	public static function ANCIENT_DEBRIS() : Opaque{
		if(!isset(self::$_mANCIENT_DEBRIS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mANCIENT_DEBRIS);
	}

	public static function ANDESITE() : Opaque{
		if(!isset(self::$_mANDESITE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mANDESITE);
	}

	public static function ANDESITE_SLAB() : Slab{
		if(!isset(self::$_mANDESITE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mANDESITE_SLAB);
	}

	public static function ANDESITE_STAIRS() : Stair{
		if(!isset(self::$_mANDESITE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mANDESITE_STAIRS);
	}

	public static function ANDESITE_WALL() : Wall{
		if(!isset(self::$_mANDESITE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mANDESITE_WALL);
	}

	public static function ANVIL() : Anvil{
		if(!isset(self::$_mANVIL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mANVIL);
	}

	public static function AZALEA_LEAVES() : Leaves{
		if(!isset(self::$_mAZALEA_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mAZALEA_LEAVES);
	}

	public static function AZURE_BLUET() : Flower{
		if(!isset(self::$_mAZURE_BLUET)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mAZURE_BLUET);
	}

	public static function BAMBOO() : Bamboo{
		if(!isset(self::$_mBAMBOO)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBAMBOO);
	}

	public static function BAMBOO_SAPLING() : BambooSapling{
		if(!isset(self::$_mBAMBOO_SAPLING)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBAMBOO_SAPLING);
	}

	public static function BANNER() : FloorBanner{
		if(!isset(self::$_mBANNER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBANNER);
	}

	public static function BARREL() : Barrel{
		if(!isset(self::$_mBARREL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBARREL);
	}

	public static function BARRIER() : Transparent{
		if(!isset(self::$_mBARRIER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBARRIER);
	}

	public static function BASALT() : SimplePillar{
		if(!isset(self::$_mBASALT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBASALT);
	}

	public static function BEACON() : Beacon{
		if(!isset(self::$_mBEACON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBEACON);
	}

	public static function BED() : Bed{
		if(!isset(self::$_mBED)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBED);
	}

	public static function BEDROCK() : Bedrock{
		if(!isset(self::$_mBEDROCK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBEDROCK);
	}

	public static function BEETROOTS() : Beetroot{
		if(!isset(self::$_mBEETROOTS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBEETROOTS);
	}

	public static function BELL() : Bell{
		if(!isset(self::$_mBELL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBELL);
	}

	public static function BIG_DRIPLEAF_HEAD() : BigDripleafHead{
		if(!isset(self::$_mBIG_DRIPLEAF_HEAD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIG_DRIPLEAF_HEAD);
	}

	public static function BIG_DRIPLEAF_STEM() : BigDripleafStem{
		if(!isset(self::$_mBIG_DRIPLEAF_STEM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIG_DRIPLEAF_STEM);
	}

	public static function BIRCH_BUTTON() : WoodenButton{
		if(!isset(self::$_mBIRCH_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_BUTTON);
	}

	public static function BIRCH_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mBIRCH_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_CEILING_CENTER_HANGING_SIGN);
	}

	public static function BIRCH_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mBIRCH_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_CEILING_EDGES_HANGING_SIGN);
	}

	public static function BIRCH_DOOR() : WoodenDoor{
		if(!isset(self::$_mBIRCH_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_DOOR);
	}

	public static function BIRCH_FENCE() : WoodenFence{
		if(!isset(self::$_mBIRCH_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_FENCE);
	}

	public static function BIRCH_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mBIRCH_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_FENCE_GATE);
	}

	public static function BIRCH_LEAVES() : Leaves{
		if(!isset(self::$_mBIRCH_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_LEAVES);
	}

	public static function BIRCH_LOG() : Wood{
		if(!isset(self::$_mBIRCH_LOG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_LOG);
	}

	public static function BIRCH_PLANKS() : Planks{
		if(!isset(self::$_mBIRCH_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_PLANKS);
	}

	public static function BIRCH_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mBIRCH_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_PRESSURE_PLATE);
	}

	public static function BIRCH_SAPLING() : Sapling{
		if(!isset(self::$_mBIRCH_SAPLING)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_SAPLING);
	}

	public static function BIRCH_SIGN() : FloorSign{
		if(!isset(self::$_mBIRCH_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_SIGN);
	}

	public static function BIRCH_SLAB() : WoodenSlab{
		if(!isset(self::$_mBIRCH_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_SLAB);
	}

	public static function BIRCH_STAIRS() : WoodenStairs{
		if(!isset(self::$_mBIRCH_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_STAIRS);
	}

	public static function BIRCH_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mBIRCH_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_TRAPDOOR);
	}

	public static function BIRCH_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mBIRCH_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_WALL_HANGING_SIGN);
	}

	public static function BIRCH_WALL_SIGN() : WallSign{
		if(!isset(self::$_mBIRCH_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_WALL_SIGN);
	}

	public static function BIRCH_WOOD() : Wood{
		if(!isset(self::$_mBIRCH_WOOD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBIRCH_WOOD);
	}

	public static function BLACKSTONE() : Opaque{
		if(!isset(self::$_mBLACKSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBLACKSTONE);
	}

	public static function BLACKSTONE_SLAB() : Slab{
		if(!isset(self::$_mBLACKSTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBLACKSTONE_SLAB);
	}

	public static function BLACKSTONE_STAIRS() : Stair{
		if(!isset(self::$_mBLACKSTONE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBLACKSTONE_STAIRS);
	}

	public static function BLACKSTONE_WALL() : Wall{
		if(!isset(self::$_mBLACKSTONE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBLACKSTONE_WALL);
	}

	public static function BLAST_FURNACE() : Furnace{
		if(!isset(self::$_mBLAST_FURNACE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBLAST_FURNACE);
	}

	public static function BLUE_ICE() : BlueIce{
		if(!isset(self::$_mBLUE_ICE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBLUE_ICE);
	}

	public static function BLUE_ORCHID() : Flower{
		if(!isset(self::$_mBLUE_ORCHID)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBLUE_ORCHID);
	}

	public static function BLUE_TORCH() : Torch{
		if(!isset(self::$_mBLUE_TORCH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBLUE_TORCH);
	}

	public static function BONE_BLOCK() : BoneBlock{
		if(!isset(self::$_mBONE_BLOCK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBONE_BLOCK);
	}

	public static function BOOKSHELF() : Bookshelf{
		if(!isset(self::$_mBOOKSHELF)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBOOKSHELF);
	}

	public static function BREWING_STAND() : BrewingStand{
		if(!isset(self::$_mBREWING_STAND)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBREWING_STAND);
	}

	public static function BRICKS() : Opaque{
		if(!isset(self::$_mBRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBRICKS);
	}

	public static function BRICK_SLAB() : Slab{
		if(!isset(self::$_mBRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBRICK_SLAB);
	}

	public static function BRICK_STAIRS() : Stair{
		if(!isset(self::$_mBRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBRICK_STAIRS);
	}

	public static function BRICK_WALL() : Wall{
		if(!isset(self::$_mBRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBRICK_WALL);
	}

	public static function BROWN_MUSHROOM() : BrownMushroom{
		if(!isset(self::$_mBROWN_MUSHROOM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBROWN_MUSHROOM);
	}

	public static function BROWN_MUSHROOM_BLOCK() : BrownMushroomBlock{
		if(!isset(self::$_mBROWN_MUSHROOM_BLOCK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBROWN_MUSHROOM_BLOCK);
	}

	public static function BUDDING_AMETHYST() : BuddingAmethyst{
		if(!isset(self::$_mBUDDING_AMETHYST)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mBUDDING_AMETHYST);
	}

	public static function CACTUS() : Cactus{
		if(!isset(self::$_mCACTUS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCACTUS);
	}

	public static function CACTUS_FLOWER() : CactusFlower{
		if(!isset(self::$_mCACTUS_FLOWER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCACTUS_FLOWER);
	}

	public static function CAKE() : Cake{
		if(!isset(self::$_mCAKE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCAKE);
	}

	public static function CAKE_WITH_CANDLE() : CakeWithCandle{
		if(!isset(self::$_mCAKE_WITH_CANDLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCAKE_WITH_CANDLE);
	}

	public static function CAKE_WITH_DYED_CANDLE() : CakeWithDyedCandle{
		if(!isset(self::$_mCAKE_WITH_DYED_CANDLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCAKE_WITH_DYED_CANDLE);
	}

	public static function CALCITE() : Opaque{
		if(!isset(self::$_mCALCITE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCALCITE);
	}

	public static function CAMPFIRE() : Campfire{
		if(!isset(self::$_mCAMPFIRE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCAMPFIRE);
	}

	public static function CANDLE() : Candle{
		if(!isset(self::$_mCANDLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCANDLE);
	}

	public static function CARPET() : Carpet{
		if(!isset(self::$_mCARPET)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCARPET);
	}

	public static function CARROTS() : Carrot{
		if(!isset(self::$_mCARROTS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCARROTS);
	}

	public static function CARTOGRAPHY_TABLE() : CartographyTable{
		if(!isset(self::$_mCARTOGRAPHY_TABLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCARTOGRAPHY_TABLE);
	}

	public static function CARVED_PUMPKIN() : CarvedPumpkin{
		if(!isset(self::$_mCARVED_PUMPKIN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCARVED_PUMPKIN);
	}

	public static function CAULDRON() : Cauldron{
		if(!isset(self::$_mCAULDRON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCAULDRON);
	}

	public static function CAVE_VINES() : CaveVines{
		if(!isset(self::$_mCAVE_VINES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCAVE_VINES);
	}

	public static function CHAIN() : Chain{
		if(!isset(self::$_mCHAIN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHAIN);
	}

	public static function CHEMICAL_HEAT() : ChemicalHeat{
		if(!isset(self::$_mCHEMICAL_HEAT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHEMICAL_HEAT);
	}

	public static function CHERRY_BUTTON() : WoodenButton{
		if(!isset(self::$_mCHERRY_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_BUTTON);
	}

	public static function CHERRY_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mCHERRY_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_CEILING_CENTER_HANGING_SIGN);
	}

	public static function CHERRY_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mCHERRY_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_CEILING_EDGES_HANGING_SIGN);
	}

	public static function CHERRY_DOOR() : WoodenDoor{
		if(!isset(self::$_mCHERRY_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_DOOR);
	}

	public static function CHERRY_FENCE() : WoodenFence{
		if(!isset(self::$_mCHERRY_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_FENCE);
	}

	public static function CHERRY_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mCHERRY_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_FENCE_GATE);
	}

	public static function CHERRY_LEAVES() : Leaves{
		if(!isset(self::$_mCHERRY_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_LEAVES);
	}

	public static function CHERRY_LOG() : Wood{
		if(!isset(self::$_mCHERRY_LOG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_LOG);
	}

	public static function CHERRY_PLANKS() : Planks{
		if(!isset(self::$_mCHERRY_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_PLANKS);
	}

	public static function CHERRY_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mCHERRY_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_PRESSURE_PLATE);
	}

	public static function CHERRY_SIGN() : FloorSign{
		if(!isset(self::$_mCHERRY_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_SIGN);
	}

	public static function CHERRY_SLAB() : WoodenSlab{
		if(!isset(self::$_mCHERRY_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_SLAB);
	}

	public static function CHERRY_STAIRS() : WoodenStairs{
		if(!isset(self::$_mCHERRY_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_STAIRS);
	}

	public static function CHERRY_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mCHERRY_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_TRAPDOOR);
	}

	public static function CHERRY_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mCHERRY_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_WALL_HANGING_SIGN);
	}

	public static function CHERRY_WALL_SIGN() : WallSign{
		if(!isset(self::$_mCHERRY_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_WALL_SIGN);
	}

	public static function CHERRY_WOOD() : Wood{
		if(!isset(self::$_mCHERRY_WOOD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHERRY_WOOD);
	}

	public static function CHEST() : Chest{
		if(!isset(self::$_mCHEST)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHEST);
	}

	public static function CHISELED_BOOKSHELF() : ChiseledBookshelf{
		if(!isset(self::$_mCHISELED_BOOKSHELF)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_BOOKSHELF);
	}

	public static function CHISELED_COPPER() : Copper{
		if(!isset(self::$_mCHISELED_COPPER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_COPPER);
	}

	public static function CHISELED_DEEPSLATE() : Opaque{
		if(!isset(self::$_mCHISELED_DEEPSLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_DEEPSLATE);
	}

	public static function CHISELED_NETHER_BRICKS() : Opaque{
		if(!isset(self::$_mCHISELED_NETHER_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_NETHER_BRICKS);
	}

	public static function CHISELED_POLISHED_BLACKSTONE() : Opaque{
		if(!isset(self::$_mCHISELED_POLISHED_BLACKSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_POLISHED_BLACKSTONE);
	}

	public static function CHISELED_QUARTZ() : SimplePillar{
		if(!isset(self::$_mCHISELED_QUARTZ)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_QUARTZ);
	}

	public static function CHISELED_RED_SANDSTONE() : Opaque{
		if(!isset(self::$_mCHISELED_RED_SANDSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_RED_SANDSTONE);
	}

	public static function CHISELED_RESIN_BRICKS() : Opaque{
		if(!isset(self::$_mCHISELED_RESIN_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_RESIN_BRICKS);
	}

	public static function CHISELED_SANDSTONE() : Opaque{
		if(!isset(self::$_mCHISELED_SANDSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_SANDSTONE);
	}

	public static function CHISELED_STONE_BRICKS() : Opaque{
		if(!isset(self::$_mCHISELED_STONE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_STONE_BRICKS);
	}

	public static function CHISELED_TUFF() : Opaque{
		if(!isset(self::$_mCHISELED_TUFF)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_TUFF);
	}

	public static function CHISELED_TUFF_BRICKS() : Opaque{
		if(!isset(self::$_mCHISELED_TUFF_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHISELED_TUFF_BRICKS);
	}

	public static function CHORUS_FLOWER() : ChorusFlower{
		if(!isset(self::$_mCHORUS_FLOWER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHORUS_FLOWER);
	}

	public static function CHORUS_PLANT() : ChorusPlant{
		if(!isset(self::$_mCHORUS_PLANT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCHORUS_PLANT);
	}

	public static function CLAY() : Clay{
		if(!isset(self::$_mCLAY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCLAY);
	}

	public static function COAL() : Coal{
		if(!isset(self::$_mCOAL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOAL);
	}

	public static function COAL_ORE() : CoalOre{
		if(!isset(self::$_mCOAL_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOAL_ORE);
	}

	public static function COBBLED_DEEPSLATE() : Opaque{
		if(!isset(self::$_mCOBBLED_DEEPSLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOBBLED_DEEPSLATE);
	}

	public static function COBBLED_DEEPSLATE_SLAB() : Slab{
		if(!isset(self::$_mCOBBLED_DEEPSLATE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOBBLED_DEEPSLATE_SLAB);
	}

	public static function COBBLED_DEEPSLATE_STAIRS() : Stair{
		if(!isset(self::$_mCOBBLED_DEEPSLATE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOBBLED_DEEPSLATE_STAIRS);
	}

	public static function COBBLED_DEEPSLATE_WALL() : Wall{
		if(!isset(self::$_mCOBBLED_DEEPSLATE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOBBLED_DEEPSLATE_WALL);
	}

	public static function COBBLESTONE() : Opaque{
		if(!isset(self::$_mCOBBLESTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOBBLESTONE);
	}

	public static function COBBLESTONE_SLAB() : Slab{
		if(!isset(self::$_mCOBBLESTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOBBLESTONE_SLAB);
	}

	public static function COBBLESTONE_STAIRS() : Stair{
		if(!isset(self::$_mCOBBLESTONE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOBBLESTONE_STAIRS);
	}

	public static function COBBLESTONE_WALL() : Wall{
		if(!isset(self::$_mCOBBLESTONE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOBBLESTONE_WALL);
	}

	public static function COBWEB() : Cobweb{
		if(!isset(self::$_mCOBWEB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOBWEB);
	}

	public static function COCOA_POD() : CocoaBlock{
		if(!isset(self::$_mCOCOA_POD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOCOA_POD);
	}

	public static function COMPOUND_CREATOR() : ChemistryTable{
		if(!isset(self::$_mCOMPOUND_CREATOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOMPOUND_CREATOR);
	}

	public static function CONCRETE() : Concrete{
		if(!isset(self::$_mCONCRETE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCONCRETE);
	}

	public static function CONCRETE_POWDER() : ConcretePowder{
		if(!isset(self::$_mCONCRETE_POWDER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCONCRETE_POWDER);
	}

	public static function COPPER() : Copper{
		if(!isset(self::$_mCOPPER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER);
	}

	public static function COPPER_BARS() : CopperBars{
		if(!isset(self::$_mCOPPER_BARS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER_BARS);
	}

	public static function COPPER_BULB() : CopperBulb{
		if(!isset(self::$_mCOPPER_BULB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER_BULB);
	}

	public static function COPPER_CHAIN() : CopperChain{
		if(!isset(self::$_mCOPPER_CHAIN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER_CHAIN);
	}

	public static function COPPER_DOOR() : CopperDoor{
		if(!isset(self::$_mCOPPER_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER_DOOR);
	}

	public static function COPPER_GRATE() : CopperGrate{
		if(!isset(self::$_mCOPPER_GRATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER_GRATE);
	}

	public static function COPPER_LANTERN() : CopperLantern{
		if(!isset(self::$_mCOPPER_LANTERN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER_LANTERN);
	}

	public static function COPPER_ORE() : CopperOre{
		if(!isset(self::$_mCOPPER_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER_ORE);
	}

	public static function COPPER_TORCH() : Torch{
		if(!isset(self::$_mCOPPER_TORCH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER_TORCH);
	}

	public static function COPPER_TRAPDOOR() : CopperTrapdoor{
		if(!isset(self::$_mCOPPER_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCOPPER_TRAPDOOR);
	}

	public static function CORAL() : Coral{
		if(!isset(self::$_mCORAL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCORAL);
	}

	public static function CORAL_BLOCK() : CoralBlock{
		if(!isset(self::$_mCORAL_BLOCK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCORAL_BLOCK);
	}

	public static function CORAL_FAN() : FloorCoralFan{
		if(!isset(self::$_mCORAL_FAN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCORAL_FAN);
	}

	public static function CORNFLOWER() : Flower{
		if(!isset(self::$_mCORNFLOWER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCORNFLOWER);
	}

	public static function CRACKED_DEEPSLATE_BRICKS() : Opaque{
		if(!isset(self::$_mCRACKED_DEEPSLATE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRACKED_DEEPSLATE_BRICKS);
	}

	public static function CRACKED_DEEPSLATE_TILES() : Opaque{
		if(!isset(self::$_mCRACKED_DEEPSLATE_TILES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRACKED_DEEPSLATE_TILES);
	}

	public static function CRACKED_NETHER_BRICKS() : Opaque{
		if(!isset(self::$_mCRACKED_NETHER_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRACKED_NETHER_BRICKS);
	}

	public static function CRACKED_POLISHED_BLACKSTONE_BRICKS() : Opaque{
		if(!isset(self::$_mCRACKED_POLISHED_BLACKSTONE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRACKED_POLISHED_BLACKSTONE_BRICKS);
	}

	public static function CRACKED_STONE_BRICKS() : Opaque{
		if(!isset(self::$_mCRACKED_STONE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRACKED_STONE_BRICKS);
	}

	public static function CRAFTING_TABLE() : CraftingTable{
		if(!isset(self::$_mCRAFTING_TABLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRAFTING_TABLE);
	}

	public static function CRIMSON_BUTTON() : WoodenButton{
		if(!isset(self::$_mCRIMSON_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_BUTTON);
	}

	public static function CRIMSON_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mCRIMSON_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_CEILING_CENTER_HANGING_SIGN);
	}

	public static function CRIMSON_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mCRIMSON_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_CEILING_EDGES_HANGING_SIGN);
	}

	public static function CRIMSON_DOOR() : WoodenDoor{
		if(!isset(self::$_mCRIMSON_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_DOOR);
	}

	public static function CRIMSON_FENCE() : WoodenFence{
		if(!isset(self::$_mCRIMSON_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_FENCE);
	}

	public static function CRIMSON_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mCRIMSON_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_FENCE_GATE);
	}

	public static function CRIMSON_FUNGUS() : NetherFungus{
		if(!isset(self::$_mCRIMSON_FUNGUS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_FUNGUS);
	}

	public static function CRIMSON_HYPHAE() : Wood{
		if(!isset(self::$_mCRIMSON_HYPHAE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_HYPHAE);
	}

	public static function CRIMSON_NYLIUM() : Nylium{
		if(!isset(self::$_mCRIMSON_NYLIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_NYLIUM);
	}

	public static function CRIMSON_PLANKS() : Planks{
		if(!isset(self::$_mCRIMSON_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_PLANKS);
	}

	public static function CRIMSON_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mCRIMSON_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_PRESSURE_PLATE);
	}

	public static function CRIMSON_ROOTS() : NetherRoots{
		if(!isset(self::$_mCRIMSON_ROOTS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_ROOTS);
	}

	public static function CRIMSON_SIGN() : FloorSign{
		if(!isset(self::$_mCRIMSON_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_SIGN);
	}

	public static function CRIMSON_SLAB() : WoodenSlab{
		if(!isset(self::$_mCRIMSON_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_SLAB);
	}

	public static function CRIMSON_STAIRS() : WoodenStairs{
		if(!isset(self::$_mCRIMSON_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_STAIRS);
	}

	public static function CRIMSON_STEM() : Wood{
		if(!isset(self::$_mCRIMSON_STEM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_STEM);
	}

	public static function CRIMSON_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mCRIMSON_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_TRAPDOOR);
	}

	public static function CRIMSON_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mCRIMSON_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_WALL_HANGING_SIGN);
	}

	public static function CRIMSON_WALL_SIGN() : WallSign{
		if(!isset(self::$_mCRIMSON_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRIMSON_WALL_SIGN);
	}

	public static function CRYING_OBSIDIAN() : Opaque{
		if(!isset(self::$_mCRYING_OBSIDIAN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCRYING_OBSIDIAN);
	}

	public static function CUT_COPPER() : Copper{
		if(!isset(self::$_mCUT_COPPER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCUT_COPPER);
	}

	public static function CUT_COPPER_SLAB() : CopperSlab{
		if(!isset(self::$_mCUT_COPPER_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCUT_COPPER_SLAB);
	}

	public static function CUT_COPPER_STAIRS() : CopperStairs{
		if(!isset(self::$_mCUT_COPPER_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCUT_COPPER_STAIRS);
	}

	public static function CUT_RED_SANDSTONE() : Opaque{
		if(!isset(self::$_mCUT_RED_SANDSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCUT_RED_SANDSTONE);
	}

	public static function CUT_RED_SANDSTONE_SLAB() : Slab{
		if(!isset(self::$_mCUT_RED_SANDSTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCUT_RED_SANDSTONE_SLAB);
	}

	public static function CUT_SANDSTONE() : Opaque{
		if(!isset(self::$_mCUT_SANDSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCUT_SANDSTONE);
	}

	public static function CUT_SANDSTONE_SLAB() : Slab{
		if(!isset(self::$_mCUT_SANDSTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mCUT_SANDSTONE_SLAB);
	}

	public static function DANDELION() : Flower{
		if(!isset(self::$_mDANDELION)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDANDELION);
	}

	public static function DARK_OAK_BUTTON() : WoodenButton{
		if(!isset(self::$_mDARK_OAK_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_BUTTON);
	}

	public static function DARK_OAK_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mDARK_OAK_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_CEILING_CENTER_HANGING_SIGN);
	}

	public static function DARK_OAK_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mDARK_OAK_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_CEILING_EDGES_HANGING_SIGN);
	}

	public static function DARK_OAK_DOOR() : WoodenDoor{
		if(!isset(self::$_mDARK_OAK_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_DOOR);
	}

	public static function DARK_OAK_FENCE() : WoodenFence{
		if(!isset(self::$_mDARK_OAK_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_FENCE);
	}

	public static function DARK_OAK_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mDARK_OAK_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_FENCE_GATE);
	}

	public static function DARK_OAK_LEAVES() : Leaves{
		if(!isset(self::$_mDARK_OAK_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_LEAVES);
	}

	public static function DARK_OAK_LOG() : Wood{
		if(!isset(self::$_mDARK_OAK_LOG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_LOG);
	}

	public static function DARK_OAK_PLANKS() : Planks{
		if(!isset(self::$_mDARK_OAK_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_PLANKS);
	}

	public static function DARK_OAK_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mDARK_OAK_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_PRESSURE_PLATE);
	}

	public static function DARK_OAK_SAPLING() : Sapling{
		if(!isset(self::$_mDARK_OAK_SAPLING)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_SAPLING);
	}

	public static function DARK_OAK_SIGN() : FloorSign{
		if(!isset(self::$_mDARK_OAK_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_SIGN);
	}

	public static function DARK_OAK_SLAB() : WoodenSlab{
		if(!isset(self::$_mDARK_OAK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_SLAB);
	}

	public static function DARK_OAK_STAIRS() : WoodenStairs{
		if(!isset(self::$_mDARK_OAK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_STAIRS);
	}

	public static function DARK_OAK_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mDARK_OAK_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_TRAPDOOR);
	}

	public static function DARK_OAK_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mDARK_OAK_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_WALL_HANGING_SIGN);
	}

	public static function DARK_OAK_WALL_SIGN() : WallSign{
		if(!isset(self::$_mDARK_OAK_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_WALL_SIGN);
	}

	public static function DARK_OAK_WOOD() : Wood{
		if(!isset(self::$_mDARK_OAK_WOOD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_OAK_WOOD);
	}

	public static function DARK_PRISMARINE() : Opaque{
		if(!isset(self::$_mDARK_PRISMARINE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_PRISMARINE);
	}

	public static function DARK_PRISMARINE_SLAB() : Slab{
		if(!isset(self::$_mDARK_PRISMARINE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_PRISMARINE_SLAB);
	}

	public static function DARK_PRISMARINE_STAIRS() : Stair{
		if(!isset(self::$_mDARK_PRISMARINE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDARK_PRISMARINE_STAIRS);
	}

	public static function DAYLIGHT_SENSOR() : DaylightSensor{
		if(!isset(self::$_mDAYLIGHT_SENSOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDAYLIGHT_SENSOR);
	}

	public static function DEAD_BUSH() : DeadBush{
		if(!isset(self::$_mDEAD_BUSH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEAD_BUSH);
	}

	public static function DEEPSLATE() : SimplePillar{
		if(!isset(self::$_mDEEPSLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE);
	}

	public static function DEEPSLATE_BRICKS() : Opaque{
		if(!isset(self::$_mDEEPSLATE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_BRICKS);
	}

	public static function DEEPSLATE_BRICK_SLAB() : Slab{
		if(!isset(self::$_mDEEPSLATE_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_BRICK_SLAB);
	}

	public static function DEEPSLATE_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mDEEPSLATE_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_BRICK_STAIRS);
	}

	public static function DEEPSLATE_BRICK_WALL() : Wall{
		if(!isset(self::$_mDEEPSLATE_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_BRICK_WALL);
	}

	public static function DEEPSLATE_COAL_ORE() : CoalOre{
		if(!isset(self::$_mDEEPSLATE_COAL_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_COAL_ORE);
	}

	public static function DEEPSLATE_COPPER_ORE() : CopperOre{
		if(!isset(self::$_mDEEPSLATE_COPPER_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_COPPER_ORE);
	}

	public static function DEEPSLATE_DIAMOND_ORE() : DiamondOre{
		if(!isset(self::$_mDEEPSLATE_DIAMOND_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_DIAMOND_ORE);
	}

	public static function DEEPSLATE_EMERALD_ORE() : EmeraldOre{
		if(!isset(self::$_mDEEPSLATE_EMERALD_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_EMERALD_ORE);
	}

	public static function DEEPSLATE_GOLD_ORE() : GoldOre{
		if(!isset(self::$_mDEEPSLATE_GOLD_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_GOLD_ORE);
	}

	public static function DEEPSLATE_IRON_ORE() : IronOre{
		if(!isset(self::$_mDEEPSLATE_IRON_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_IRON_ORE);
	}

	public static function DEEPSLATE_LAPIS_LAZULI_ORE() : LapisOre{
		if(!isset(self::$_mDEEPSLATE_LAPIS_LAZULI_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_LAPIS_LAZULI_ORE);
	}

	public static function DEEPSLATE_REDSTONE_ORE() : RedstoneOre{
		if(!isset(self::$_mDEEPSLATE_REDSTONE_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_REDSTONE_ORE);
	}

	public static function DEEPSLATE_TILES() : Opaque{
		if(!isset(self::$_mDEEPSLATE_TILES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_TILES);
	}

	public static function DEEPSLATE_TILE_SLAB() : Slab{
		if(!isset(self::$_mDEEPSLATE_TILE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_TILE_SLAB);
	}

	public static function DEEPSLATE_TILE_STAIRS() : Stair{
		if(!isset(self::$_mDEEPSLATE_TILE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_TILE_STAIRS);
	}

	public static function DEEPSLATE_TILE_WALL() : Wall{
		if(!isset(self::$_mDEEPSLATE_TILE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDEEPSLATE_TILE_WALL);
	}

	public static function DETECTOR_RAIL() : DetectorRail{
		if(!isset(self::$_mDETECTOR_RAIL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDETECTOR_RAIL);
	}

	public static function DIAMOND() : Opaque{
		if(!isset(self::$_mDIAMOND)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDIAMOND);
	}

	public static function DIAMOND_ORE() : DiamondOre{
		if(!isset(self::$_mDIAMOND_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDIAMOND_ORE);
	}

	public static function DIORITE() : Opaque{
		if(!isset(self::$_mDIORITE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDIORITE);
	}

	public static function DIORITE_SLAB() : Slab{
		if(!isset(self::$_mDIORITE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDIORITE_SLAB);
	}

	public static function DIORITE_STAIRS() : Stair{
		if(!isset(self::$_mDIORITE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDIORITE_STAIRS);
	}

	public static function DIORITE_WALL() : Wall{
		if(!isset(self::$_mDIORITE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDIORITE_WALL);
	}

	public static function DIRT() : Dirt{
		if(!isset(self::$_mDIRT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDIRT);
	}

	public static function DOUBLE_PITCHER_CROP() : DoublePitcherCrop{
		if(!isset(self::$_mDOUBLE_PITCHER_CROP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDOUBLE_PITCHER_CROP);
	}

	public static function DOUBLE_TALLGRASS() : DoubleTallGrass{
		if(!isset(self::$_mDOUBLE_TALLGRASS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDOUBLE_TALLGRASS);
	}

	public static function DRAGON_EGG() : DragonEgg{
		if(!isset(self::$_mDRAGON_EGG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDRAGON_EGG);
	}

	public static function DRIED_KELP() : DriedKelp{
		if(!isset(self::$_mDRIED_KELP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDRIED_KELP);
	}

	public static function DYED_CANDLE() : DyedCandle{
		if(!isset(self::$_mDYED_CANDLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDYED_CANDLE);
	}

	public static function DYED_SHULKER_BOX() : DyedShulkerBox{
		if(!isset(self::$_mDYED_SHULKER_BOX)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mDYED_SHULKER_BOX);
	}

	public static function ELEMENT_ACTINIUM() : Element{
		if(!isset(self::$_mELEMENT_ACTINIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ACTINIUM);
	}

	public static function ELEMENT_ALUMINUM() : Element{
		if(!isset(self::$_mELEMENT_ALUMINUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ALUMINUM);
	}

	public static function ELEMENT_AMERICIUM() : Element{
		if(!isset(self::$_mELEMENT_AMERICIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_AMERICIUM);
	}

	public static function ELEMENT_ANTIMONY() : Element{
		if(!isset(self::$_mELEMENT_ANTIMONY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ANTIMONY);
	}

	public static function ELEMENT_ARGON() : Element{
		if(!isset(self::$_mELEMENT_ARGON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ARGON);
	}

	public static function ELEMENT_ARSENIC() : Element{
		if(!isset(self::$_mELEMENT_ARSENIC)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ARSENIC);
	}

	public static function ELEMENT_ASTATINE() : Element{
		if(!isset(self::$_mELEMENT_ASTATINE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ASTATINE);
	}

	public static function ELEMENT_BARIUM() : Element{
		if(!isset(self::$_mELEMENT_BARIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_BARIUM);
	}

	public static function ELEMENT_BERKELIUM() : Element{
		if(!isset(self::$_mELEMENT_BERKELIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_BERKELIUM);
	}

	public static function ELEMENT_BERYLLIUM() : Element{
		if(!isset(self::$_mELEMENT_BERYLLIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_BERYLLIUM);
	}

	public static function ELEMENT_BISMUTH() : Element{
		if(!isset(self::$_mELEMENT_BISMUTH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_BISMUTH);
	}

	public static function ELEMENT_BOHRIUM() : Element{
		if(!isset(self::$_mELEMENT_BOHRIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_BOHRIUM);
	}

	public static function ELEMENT_BORON() : Element{
		if(!isset(self::$_mELEMENT_BORON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_BORON);
	}

	public static function ELEMENT_BROMINE() : Element{
		if(!isset(self::$_mELEMENT_BROMINE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_BROMINE);
	}

	public static function ELEMENT_CADMIUM() : Element{
		if(!isset(self::$_mELEMENT_CADMIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CADMIUM);
	}

	public static function ELEMENT_CALCIUM() : Element{
		if(!isset(self::$_mELEMENT_CALCIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CALCIUM);
	}

	public static function ELEMENT_CALIFORNIUM() : Element{
		if(!isset(self::$_mELEMENT_CALIFORNIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CALIFORNIUM);
	}

	public static function ELEMENT_CARBON() : Element{
		if(!isset(self::$_mELEMENT_CARBON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CARBON);
	}

	public static function ELEMENT_CERIUM() : Element{
		if(!isset(self::$_mELEMENT_CERIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CERIUM);
	}

	public static function ELEMENT_CESIUM() : Element{
		if(!isset(self::$_mELEMENT_CESIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CESIUM);
	}

	public static function ELEMENT_CHLORINE() : Element{
		if(!isset(self::$_mELEMENT_CHLORINE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CHLORINE);
	}

	public static function ELEMENT_CHROMIUM() : Element{
		if(!isset(self::$_mELEMENT_CHROMIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CHROMIUM);
	}

	public static function ELEMENT_COBALT() : Element{
		if(!isset(self::$_mELEMENT_COBALT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_COBALT);
	}

	public static function ELEMENT_CONSTRUCTOR() : ChemistryTable{
		if(!isset(self::$_mELEMENT_CONSTRUCTOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CONSTRUCTOR);
	}

	public static function ELEMENT_COPERNICIUM() : Element{
		if(!isset(self::$_mELEMENT_COPERNICIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_COPERNICIUM);
	}

	public static function ELEMENT_COPPER() : Element{
		if(!isset(self::$_mELEMENT_COPPER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_COPPER);
	}

	public static function ELEMENT_CURIUM() : Element{
		if(!isset(self::$_mELEMENT_CURIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_CURIUM);
	}

	public static function ELEMENT_DARMSTADTIUM() : Element{
		if(!isset(self::$_mELEMENT_DARMSTADTIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_DARMSTADTIUM);
	}

	public static function ELEMENT_DUBNIUM() : Element{
		if(!isset(self::$_mELEMENT_DUBNIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_DUBNIUM);
	}

	public static function ELEMENT_DYSPROSIUM() : Element{
		if(!isset(self::$_mELEMENT_DYSPROSIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_DYSPROSIUM);
	}

	public static function ELEMENT_EINSTEINIUM() : Element{
		if(!isset(self::$_mELEMENT_EINSTEINIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_EINSTEINIUM);
	}

	public static function ELEMENT_ERBIUM() : Element{
		if(!isset(self::$_mELEMENT_ERBIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ERBIUM);
	}

	public static function ELEMENT_EUROPIUM() : Element{
		if(!isset(self::$_mELEMENT_EUROPIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_EUROPIUM);
	}

	public static function ELEMENT_FERMIUM() : Element{
		if(!isset(self::$_mELEMENT_FERMIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_FERMIUM);
	}

	public static function ELEMENT_FLEROVIUM() : Element{
		if(!isset(self::$_mELEMENT_FLEROVIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_FLEROVIUM);
	}

	public static function ELEMENT_FLUORINE() : Element{
		if(!isset(self::$_mELEMENT_FLUORINE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_FLUORINE);
	}

	public static function ELEMENT_FRANCIUM() : Element{
		if(!isset(self::$_mELEMENT_FRANCIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_FRANCIUM);
	}

	public static function ELEMENT_GADOLINIUM() : Element{
		if(!isset(self::$_mELEMENT_GADOLINIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_GADOLINIUM);
	}

	public static function ELEMENT_GALLIUM() : Element{
		if(!isset(self::$_mELEMENT_GALLIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_GALLIUM);
	}

	public static function ELEMENT_GERMANIUM() : Element{
		if(!isset(self::$_mELEMENT_GERMANIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_GERMANIUM);
	}

	public static function ELEMENT_GOLD() : Element{
		if(!isset(self::$_mELEMENT_GOLD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_GOLD);
	}

	public static function ELEMENT_HAFNIUM() : Element{
		if(!isset(self::$_mELEMENT_HAFNIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_HAFNIUM);
	}

	public static function ELEMENT_HASSIUM() : Element{
		if(!isset(self::$_mELEMENT_HASSIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_HASSIUM);
	}

	public static function ELEMENT_HELIUM() : Element{
		if(!isset(self::$_mELEMENT_HELIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_HELIUM);
	}

	public static function ELEMENT_HOLMIUM() : Element{
		if(!isset(self::$_mELEMENT_HOLMIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_HOLMIUM);
	}

	public static function ELEMENT_HYDROGEN() : Element{
		if(!isset(self::$_mELEMENT_HYDROGEN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_HYDROGEN);
	}

	public static function ELEMENT_INDIUM() : Element{
		if(!isset(self::$_mELEMENT_INDIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_INDIUM);
	}

	public static function ELEMENT_IODINE() : Element{
		if(!isset(self::$_mELEMENT_IODINE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_IODINE);
	}

	public static function ELEMENT_IRIDIUM() : Element{
		if(!isset(self::$_mELEMENT_IRIDIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_IRIDIUM);
	}

	public static function ELEMENT_IRON() : Element{
		if(!isset(self::$_mELEMENT_IRON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_IRON);
	}

	public static function ELEMENT_KRYPTON() : Element{
		if(!isset(self::$_mELEMENT_KRYPTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_KRYPTON);
	}

	public static function ELEMENT_LANTHANUM() : Element{
		if(!isset(self::$_mELEMENT_LANTHANUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_LANTHANUM);
	}

	public static function ELEMENT_LAWRENCIUM() : Element{
		if(!isset(self::$_mELEMENT_LAWRENCIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_LAWRENCIUM);
	}

	public static function ELEMENT_LEAD() : Element{
		if(!isset(self::$_mELEMENT_LEAD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_LEAD);
	}

	public static function ELEMENT_LITHIUM() : Element{
		if(!isset(self::$_mELEMENT_LITHIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_LITHIUM);
	}

	public static function ELEMENT_LIVERMORIUM() : Element{
		if(!isset(self::$_mELEMENT_LIVERMORIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_LIVERMORIUM);
	}

	public static function ELEMENT_LUTETIUM() : Element{
		if(!isset(self::$_mELEMENT_LUTETIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_LUTETIUM);
	}

	public static function ELEMENT_MAGNESIUM() : Element{
		if(!isset(self::$_mELEMENT_MAGNESIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_MAGNESIUM);
	}

	public static function ELEMENT_MANGANESE() : Element{
		if(!isset(self::$_mELEMENT_MANGANESE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_MANGANESE);
	}

	public static function ELEMENT_MEITNERIUM() : Element{
		if(!isset(self::$_mELEMENT_MEITNERIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_MEITNERIUM);
	}

	public static function ELEMENT_MENDELEVIUM() : Element{
		if(!isset(self::$_mELEMENT_MENDELEVIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_MENDELEVIUM);
	}

	public static function ELEMENT_MERCURY() : Element{
		if(!isset(self::$_mELEMENT_MERCURY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_MERCURY);
	}

	public static function ELEMENT_MOLYBDENUM() : Element{
		if(!isset(self::$_mELEMENT_MOLYBDENUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_MOLYBDENUM);
	}

	public static function ELEMENT_MOSCOVIUM() : Element{
		if(!isset(self::$_mELEMENT_MOSCOVIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_MOSCOVIUM);
	}

	public static function ELEMENT_NEODYMIUM() : Element{
		if(!isset(self::$_mELEMENT_NEODYMIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_NEODYMIUM);
	}

	public static function ELEMENT_NEON() : Element{
		if(!isset(self::$_mELEMENT_NEON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_NEON);
	}

	public static function ELEMENT_NEPTUNIUM() : Element{
		if(!isset(self::$_mELEMENT_NEPTUNIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_NEPTUNIUM);
	}

	public static function ELEMENT_NICKEL() : Element{
		if(!isset(self::$_mELEMENT_NICKEL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_NICKEL);
	}

	public static function ELEMENT_NIHONIUM() : Element{
		if(!isset(self::$_mELEMENT_NIHONIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_NIHONIUM);
	}

	public static function ELEMENT_NIOBIUM() : Element{
		if(!isset(self::$_mELEMENT_NIOBIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_NIOBIUM);
	}

	public static function ELEMENT_NITROGEN() : Element{
		if(!isset(self::$_mELEMENT_NITROGEN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_NITROGEN);
	}

	public static function ELEMENT_NOBELIUM() : Element{
		if(!isset(self::$_mELEMENT_NOBELIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_NOBELIUM);
	}

	public static function ELEMENT_OGANESSON() : Element{
		if(!isset(self::$_mELEMENT_OGANESSON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_OGANESSON);
	}

	public static function ELEMENT_OSMIUM() : Element{
		if(!isset(self::$_mELEMENT_OSMIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_OSMIUM);
	}

	public static function ELEMENT_OXYGEN() : Element{
		if(!isset(self::$_mELEMENT_OXYGEN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_OXYGEN);
	}

	public static function ELEMENT_PALLADIUM() : Element{
		if(!isset(self::$_mELEMENT_PALLADIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_PALLADIUM);
	}

	public static function ELEMENT_PHOSPHORUS() : Element{
		if(!isset(self::$_mELEMENT_PHOSPHORUS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_PHOSPHORUS);
	}

	public static function ELEMENT_PLATINUM() : Element{
		if(!isset(self::$_mELEMENT_PLATINUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_PLATINUM);
	}

	public static function ELEMENT_PLUTONIUM() : Element{
		if(!isset(self::$_mELEMENT_PLUTONIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_PLUTONIUM);
	}

	public static function ELEMENT_POLONIUM() : Element{
		if(!isset(self::$_mELEMENT_POLONIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_POLONIUM);
	}

	public static function ELEMENT_POTASSIUM() : Element{
		if(!isset(self::$_mELEMENT_POTASSIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_POTASSIUM);
	}

	public static function ELEMENT_PRASEODYMIUM() : Element{
		if(!isset(self::$_mELEMENT_PRASEODYMIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_PRASEODYMIUM);
	}

	public static function ELEMENT_PROMETHIUM() : Element{
		if(!isset(self::$_mELEMENT_PROMETHIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_PROMETHIUM);
	}

	public static function ELEMENT_PROTACTINIUM() : Element{
		if(!isset(self::$_mELEMENT_PROTACTINIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_PROTACTINIUM);
	}

	public static function ELEMENT_RADIUM() : Element{
		if(!isset(self::$_mELEMENT_RADIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_RADIUM);
	}

	public static function ELEMENT_RADON() : Element{
		if(!isset(self::$_mELEMENT_RADON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_RADON);
	}

	public static function ELEMENT_RHENIUM() : Element{
		if(!isset(self::$_mELEMENT_RHENIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_RHENIUM);
	}

	public static function ELEMENT_RHODIUM() : Element{
		if(!isset(self::$_mELEMENT_RHODIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_RHODIUM);
	}

	public static function ELEMENT_ROENTGENIUM() : Element{
		if(!isset(self::$_mELEMENT_ROENTGENIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ROENTGENIUM);
	}

	public static function ELEMENT_RUBIDIUM() : Element{
		if(!isset(self::$_mELEMENT_RUBIDIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_RUBIDIUM);
	}

	public static function ELEMENT_RUTHENIUM() : Element{
		if(!isset(self::$_mELEMENT_RUTHENIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_RUTHENIUM);
	}

	public static function ELEMENT_RUTHERFORDIUM() : Element{
		if(!isset(self::$_mELEMENT_RUTHERFORDIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_RUTHERFORDIUM);
	}

	public static function ELEMENT_SAMARIUM() : Element{
		if(!isset(self::$_mELEMENT_SAMARIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_SAMARIUM);
	}

	public static function ELEMENT_SCANDIUM() : Element{
		if(!isset(self::$_mELEMENT_SCANDIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_SCANDIUM);
	}

	public static function ELEMENT_SEABORGIUM() : Element{
		if(!isset(self::$_mELEMENT_SEABORGIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_SEABORGIUM);
	}

	public static function ELEMENT_SELENIUM() : Element{
		if(!isset(self::$_mELEMENT_SELENIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_SELENIUM);
	}

	public static function ELEMENT_SILICON() : Element{
		if(!isset(self::$_mELEMENT_SILICON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_SILICON);
	}

	public static function ELEMENT_SILVER() : Element{
		if(!isset(self::$_mELEMENT_SILVER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_SILVER);
	}

	public static function ELEMENT_SODIUM() : Element{
		if(!isset(self::$_mELEMENT_SODIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_SODIUM);
	}

	public static function ELEMENT_STRONTIUM() : Element{
		if(!isset(self::$_mELEMENT_STRONTIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_STRONTIUM);
	}

	public static function ELEMENT_SULFUR() : Element{
		if(!isset(self::$_mELEMENT_SULFUR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_SULFUR);
	}

	public static function ELEMENT_TANTALUM() : Element{
		if(!isset(self::$_mELEMENT_TANTALUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_TANTALUM);
	}

	public static function ELEMENT_TECHNETIUM() : Element{
		if(!isset(self::$_mELEMENT_TECHNETIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_TECHNETIUM);
	}

	public static function ELEMENT_TELLURIUM() : Element{
		if(!isset(self::$_mELEMENT_TELLURIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_TELLURIUM);
	}

	public static function ELEMENT_TENNESSINE() : Element{
		if(!isset(self::$_mELEMENT_TENNESSINE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_TENNESSINE);
	}

	public static function ELEMENT_TERBIUM() : Element{
		if(!isset(self::$_mELEMENT_TERBIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_TERBIUM);
	}

	public static function ELEMENT_THALLIUM() : Element{
		if(!isset(self::$_mELEMENT_THALLIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_THALLIUM);
	}

	public static function ELEMENT_THORIUM() : Element{
		if(!isset(self::$_mELEMENT_THORIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_THORIUM);
	}

	public static function ELEMENT_THULIUM() : Element{
		if(!isset(self::$_mELEMENT_THULIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_THULIUM);
	}

	public static function ELEMENT_TIN() : Element{
		if(!isset(self::$_mELEMENT_TIN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_TIN);
	}

	public static function ELEMENT_TITANIUM() : Element{
		if(!isset(self::$_mELEMENT_TITANIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_TITANIUM);
	}

	public static function ELEMENT_TUNGSTEN() : Element{
		if(!isset(self::$_mELEMENT_TUNGSTEN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_TUNGSTEN);
	}

	public static function ELEMENT_URANIUM() : Element{
		if(!isset(self::$_mELEMENT_URANIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_URANIUM);
	}

	public static function ELEMENT_VANADIUM() : Element{
		if(!isset(self::$_mELEMENT_VANADIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_VANADIUM);
	}

	public static function ELEMENT_XENON() : Element{
		if(!isset(self::$_mELEMENT_XENON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_XENON);
	}

	public static function ELEMENT_YTTERBIUM() : Element{
		if(!isset(self::$_mELEMENT_YTTERBIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_YTTERBIUM);
	}

	public static function ELEMENT_YTTRIUM() : Element{
		if(!isset(self::$_mELEMENT_YTTRIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_YTTRIUM);
	}

	public static function ELEMENT_ZERO() : Opaque{
		if(!isset(self::$_mELEMENT_ZERO)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ZERO);
	}

	public static function ELEMENT_ZINC() : Element{
		if(!isset(self::$_mELEMENT_ZINC)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ZINC);
	}

	public static function ELEMENT_ZIRCONIUM() : Element{
		if(!isset(self::$_mELEMENT_ZIRCONIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mELEMENT_ZIRCONIUM);
	}

	public static function EMERALD() : Opaque{
		if(!isset(self::$_mEMERALD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mEMERALD);
	}

	public static function EMERALD_ORE() : EmeraldOre{
		if(!isset(self::$_mEMERALD_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mEMERALD_ORE);
	}

	public static function ENCHANTING_TABLE() : EnchantingTable{
		if(!isset(self::$_mENCHANTING_TABLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mENCHANTING_TABLE);
	}

	public static function ENDER_CHEST() : EnderChest{
		if(!isset(self::$_mENDER_CHEST)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mENDER_CHEST);
	}

	public static function END_PORTAL_FRAME() : EndPortalFrame{
		if(!isset(self::$_mEND_PORTAL_FRAME)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mEND_PORTAL_FRAME);
	}

	public static function END_ROD() : EndRod{
		if(!isset(self::$_mEND_ROD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mEND_ROD);
	}

	public static function END_STONE() : Opaque{
		if(!isset(self::$_mEND_STONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mEND_STONE);
	}

	public static function END_STONE_BRICKS() : Opaque{
		if(!isset(self::$_mEND_STONE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mEND_STONE_BRICKS);
	}

	public static function END_STONE_BRICK_SLAB() : Slab{
		if(!isset(self::$_mEND_STONE_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mEND_STONE_BRICK_SLAB);
	}

	public static function END_STONE_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mEND_STONE_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mEND_STONE_BRICK_STAIRS);
	}

	public static function END_STONE_BRICK_WALL() : Wall{
		if(!isset(self::$_mEND_STONE_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mEND_STONE_BRICK_WALL);
	}

	public static function FAKE_WOODEN_SLAB() : Slab{
		if(!isset(self::$_mFAKE_WOODEN_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFAKE_WOODEN_SLAB);
	}

	public static function FARMLAND() : Farmland{
		if(!isset(self::$_mFARMLAND)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFARMLAND);
	}

	public static function FERN() : TallGrass{
		if(!isset(self::$_mFERN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFERN);
	}

	public static function FIRE() : Fire{
		if(!isset(self::$_mFIRE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFIRE);
	}

	public static function FLETCHING_TABLE() : FletchingTable{
		if(!isset(self::$_mFLETCHING_TABLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFLETCHING_TABLE);
	}

	public static function FLOWERING_AZALEA_LEAVES() : Leaves{
		if(!isset(self::$_mFLOWERING_AZALEA_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFLOWERING_AZALEA_LEAVES);
	}

	public static function FLOWER_POT() : FlowerPot{
		if(!isset(self::$_mFLOWER_POT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFLOWER_POT);
	}

	public static function FROGLIGHT() : Froglight{
		if(!isset(self::$_mFROGLIGHT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFROGLIGHT);
	}

	public static function FROSTED_ICE() : FrostedIce{
		if(!isset(self::$_mFROSTED_ICE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFROSTED_ICE);
	}

	public static function FURNACE() : Furnace{
		if(!isset(self::$_mFURNACE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mFURNACE);
	}

	public static function GILDED_BLACKSTONE() : GildedBlackstone{
		if(!isset(self::$_mGILDED_BLACKSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGILDED_BLACKSTONE);
	}

	public static function GLASS() : Glass{
		if(!isset(self::$_mGLASS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGLASS);
	}

	public static function GLASS_PANE() : GlassPane{
		if(!isset(self::$_mGLASS_PANE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGLASS_PANE);
	}

	public static function GLAZED_TERRACOTTA() : GlazedTerracotta{
		if(!isset(self::$_mGLAZED_TERRACOTTA)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGLAZED_TERRACOTTA);
	}

	public static function GLOWING_ITEM_FRAME() : ItemFrame{
		if(!isset(self::$_mGLOWING_ITEM_FRAME)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGLOWING_ITEM_FRAME);
	}

	public static function GLOWING_OBSIDIAN() : GlowingObsidian{
		if(!isset(self::$_mGLOWING_OBSIDIAN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGLOWING_OBSIDIAN);
	}

	public static function GLOWSTONE() : Glowstone{
		if(!isset(self::$_mGLOWSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGLOWSTONE);
	}

	public static function GLOW_LICHEN() : GlowLichen{
		if(!isset(self::$_mGLOW_LICHEN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGLOW_LICHEN);
	}

	public static function GOLD() : Opaque{
		if(!isset(self::$_mGOLD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGOLD);
	}

	public static function GOLD_ORE() : GoldOre{
		if(!isset(self::$_mGOLD_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGOLD_ORE);
	}

	public static function GRANITE() : Opaque{
		if(!isset(self::$_mGRANITE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGRANITE);
	}

	public static function GRANITE_SLAB() : Slab{
		if(!isset(self::$_mGRANITE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGRANITE_SLAB);
	}

	public static function GRANITE_STAIRS() : Stair{
		if(!isset(self::$_mGRANITE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGRANITE_STAIRS);
	}

	public static function GRANITE_WALL() : Wall{
		if(!isset(self::$_mGRANITE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGRANITE_WALL);
	}

	public static function GRASS() : Grass{
		if(!isset(self::$_mGRASS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGRASS);
	}

	public static function GRASS_PATH() : GrassPath{
		if(!isset(self::$_mGRASS_PATH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGRASS_PATH);
	}

	public static function GRAVEL() : Gravel{
		if(!isset(self::$_mGRAVEL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGRAVEL);
	}

	public static function GREEN_TORCH() : Torch{
		if(!isset(self::$_mGREEN_TORCH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mGREEN_TORCH);
	}

	public static function HANGING_ROOTS() : HangingRoots{
		if(!isset(self::$_mHANGING_ROOTS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mHANGING_ROOTS);
	}

	public static function HARDENED_CLAY() : HardenedClay{
		if(!isset(self::$_mHARDENED_CLAY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mHARDENED_CLAY);
	}

	public static function HARDENED_GLASS() : HardenedGlass{
		if(!isset(self::$_mHARDENED_GLASS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mHARDENED_GLASS);
	}

	public static function HARDENED_GLASS_PANE() : HardenedGlassPane{
		if(!isset(self::$_mHARDENED_GLASS_PANE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mHARDENED_GLASS_PANE);
	}

	public static function HAY_BALE() : HayBale{
		if(!isset(self::$_mHAY_BALE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mHAY_BALE);
	}

	public static function HONEYCOMB() : Opaque{
		if(!isset(self::$_mHONEYCOMB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mHONEYCOMB);
	}

	public static function HOPPER() : Hopper{
		if(!isset(self::$_mHOPPER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mHOPPER);
	}

	public static function ICE() : Ice{
		if(!isset(self::$_mICE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mICE);
	}

	public static function INFESTED_CHISELED_STONE_BRICK() : InfestedStone{
		if(!isset(self::$_mINFESTED_CHISELED_STONE_BRICK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINFESTED_CHISELED_STONE_BRICK);
	}

	public static function INFESTED_COBBLESTONE() : InfestedStone{
		if(!isset(self::$_mINFESTED_COBBLESTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINFESTED_COBBLESTONE);
	}

	public static function INFESTED_CRACKED_STONE_BRICK() : InfestedStone{
		if(!isset(self::$_mINFESTED_CRACKED_STONE_BRICK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINFESTED_CRACKED_STONE_BRICK);
	}

	public static function INFESTED_DEEPSLATE() : InfestedPillar{
		if(!isset(self::$_mINFESTED_DEEPSLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINFESTED_DEEPSLATE);
	}

	public static function INFESTED_MOSSY_STONE_BRICK() : InfestedStone{
		if(!isset(self::$_mINFESTED_MOSSY_STONE_BRICK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINFESTED_MOSSY_STONE_BRICK);
	}

	public static function INFESTED_STONE() : InfestedStone{
		if(!isset(self::$_mINFESTED_STONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINFESTED_STONE);
	}

	public static function INFESTED_STONE_BRICK() : InfestedStone{
		if(!isset(self::$_mINFESTED_STONE_BRICK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINFESTED_STONE_BRICK);
	}

	public static function INFO_UPDATE() : Opaque{
		if(!isset(self::$_mINFO_UPDATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINFO_UPDATE);
	}

	public static function INFO_UPDATE2() : Opaque{
		if(!isset(self::$_mINFO_UPDATE2)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINFO_UPDATE2);
	}

	public static function INVISIBLE_BEDROCK() : Transparent{
		if(!isset(self::$_mINVISIBLE_BEDROCK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mINVISIBLE_BEDROCK);
	}

	public static function IRON() : Opaque{
		if(!isset(self::$_mIRON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mIRON);
	}

	public static function IRON_BARS() : Thin{
		if(!isset(self::$_mIRON_BARS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mIRON_BARS);
	}

	public static function IRON_DOOR() : Door{
		if(!isset(self::$_mIRON_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mIRON_DOOR);
	}

	public static function IRON_ORE() : IronOre{
		if(!isset(self::$_mIRON_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mIRON_ORE);
	}

	public static function IRON_TRAPDOOR() : Trapdoor{
		if(!isset(self::$_mIRON_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mIRON_TRAPDOOR);
	}

	public static function ITEM_FRAME() : ItemFrame{
		if(!isset(self::$_mITEM_FRAME)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mITEM_FRAME);
	}

	public static function JUKEBOX() : Jukebox{
		if(!isset(self::$_mJUKEBOX)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUKEBOX);
	}

	public static function JUNGLE_BUTTON() : WoodenButton{
		if(!isset(self::$_mJUNGLE_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_BUTTON);
	}

	public static function JUNGLE_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mJUNGLE_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_CEILING_CENTER_HANGING_SIGN);
	}

	public static function JUNGLE_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mJUNGLE_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_CEILING_EDGES_HANGING_SIGN);
	}

	public static function JUNGLE_DOOR() : WoodenDoor{
		if(!isset(self::$_mJUNGLE_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_DOOR);
	}

	public static function JUNGLE_FENCE() : WoodenFence{
		if(!isset(self::$_mJUNGLE_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_FENCE);
	}

	public static function JUNGLE_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mJUNGLE_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_FENCE_GATE);
	}

	public static function JUNGLE_LEAVES() : Leaves{
		if(!isset(self::$_mJUNGLE_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_LEAVES);
	}

	public static function JUNGLE_LOG() : Wood{
		if(!isset(self::$_mJUNGLE_LOG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_LOG);
	}

	public static function JUNGLE_PLANKS() : Planks{
		if(!isset(self::$_mJUNGLE_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_PLANKS);
	}

	public static function JUNGLE_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mJUNGLE_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_PRESSURE_PLATE);
	}

	public static function JUNGLE_SAPLING() : Sapling{
		if(!isset(self::$_mJUNGLE_SAPLING)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_SAPLING);
	}

	public static function JUNGLE_SIGN() : FloorSign{
		if(!isset(self::$_mJUNGLE_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_SIGN);
	}

	public static function JUNGLE_SLAB() : WoodenSlab{
		if(!isset(self::$_mJUNGLE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_SLAB);
	}

	public static function JUNGLE_STAIRS() : WoodenStairs{
		if(!isset(self::$_mJUNGLE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_STAIRS);
	}

	public static function JUNGLE_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mJUNGLE_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_TRAPDOOR);
	}

	public static function JUNGLE_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mJUNGLE_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_WALL_HANGING_SIGN);
	}

	public static function JUNGLE_WALL_SIGN() : WallSign{
		if(!isset(self::$_mJUNGLE_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_WALL_SIGN);
	}

	public static function JUNGLE_WOOD() : Wood{
		if(!isset(self::$_mJUNGLE_WOOD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mJUNGLE_WOOD);
	}

	public static function LAB_TABLE() : ChemistryTable{
		if(!isset(self::$_mLAB_TABLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLAB_TABLE);
	}

	public static function LADDER() : Ladder{
		if(!isset(self::$_mLADDER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLADDER);
	}

	public static function LANTERN() : Lantern{
		if(!isset(self::$_mLANTERN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLANTERN);
	}

	public static function LAPIS_LAZULI() : Opaque{
		if(!isset(self::$_mLAPIS_LAZULI)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLAPIS_LAZULI);
	}

	public static function LAPIS_LAZULI_ORE() : LapisOre{
		if(!isset(self::$_mLAPIS_LAZULI_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLAPIS_LAZULI_ORE);
	}

	public static function LARGE_FERN() : DoubleTallGrass{
		if(!isset(self::$_mLARGE_FERN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLARGE_FERN);
	}

	public static function LAVA() : Lava{
		if(!isset(self::$_mLAVA)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLAVA);
	}

	public static function LAVA_CAULDRON() : LavaCauldron{
		if(!isset(self::$_mLAVA_CAULDRON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLAVA_CAULDRON);
	}

	public static function LECTERN() : Lectern{
		if(!isset(self::$_mLECTERN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLECTERN);
	}

	public static function LEGACY_STONECUTTER() : Opaque{
		if(!isset(self::$_mLEGACY_STONECUTTER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLEGACY_STONECUTTER);
	}

	public static function LEVER() : Lever{
		if(!isset(self::$_mLEVER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLEVER);
	}

	public static function LIGHT() : Light{
		if(!isset(self::$_mLIGHT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLIGHT);
	}

	public static function LIGHTNING_ROD() : LightningRod{
		if(!isset(self::$_mLIGHTNING_ROD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLIGHTNING_ROD);
	}

	public static function LILAC() : DoublePlant{
		if(!isset(self::$_mLILAC)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLILAC);
	}

	public static function LILY_OF_THE_VALLEY() : Flower{
		if(!isset(self::$_mLILY_OF_THE_VALLEY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLILY_OF_THE_VALLEY);
	}

	public static function LILY_PAD() : WaterLily{
		if(!isset(self::$_mLILY_PAD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLILY_PAD);
	}

	public static function LIT_PUMPKIN() : LitPumpkin{
		if(!isset(self::$_mLIT_PUMPKIN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLIT_PUMPKIN);
	}

	public static function LOOM() : Loom{
		if(!isset(self::$_mLOOM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mLOOM);
	}

	public static function MAGMA() : Magma{
		if(!isset(self::$_mMAGMA)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMAGMA);
	}

	public static function MANGROVE_BUTTON() : WoodenButton{
		if(!isset(self::$_mMANGROVE_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_BUTTON);
	}

	public static function MANGROVE_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mMANGROVE_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_CEILING_CENTER_HANGING_SIGN);
	}

	public static function MANGROVE_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mMANGROVE_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_CEILING_EDGES_HANGING_SIGN);
	}

	public static function MANGROVE_DOOR() : WoodenDoor{
		if(!isset(self::$_mMANGROVE_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_DOOR);
	}

	public static function MANGROVE_FENCE() : WoodenFence{
		if(!isset(self::$_mMANGROVE_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_FENCE);
	}

	public static function MANGROVE_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mMANGROVE_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_FENCE_GATE);
	}

	public static function MANGROVE_LEAVES() : Leaves{
		if(!isset(self::$_mMANGROVE_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_LEAVES);
	}

	public static function MANGROVE_LOG() : Wood{
		if(!isset(self::$_mMANGROVE_LOG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_LOG);
	}

	public static function MANGROVE_PLANKS() : Planks{
		if(!isset(self::$_mMANGROVE_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_PLANKS);
	}

	public static function MANGROVE_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mMANGROVE_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_PRESSURE_PLATE);
	}

	public static function MANGROVE_ROOTS() : MangroveRoots{
		if(!isset(self::$_mMANGROVE_ROOTS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_ROOTS);
	}

	public static function MANGROVE_SIGN() : FloorSign{
		if(!isset(self::$_mMANGROVE_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_SIGN);
	}

	public static function MANGROVE_SLAB() : WoodenSlab{
		if(!isset(self::$_mMANGROVE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_SLAB);
	}

	public static function MANGROVE_STAIRS() : WoodenStairs{
		if(!isset(self::$_mMANGROVE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_STAIRS);
	}

	public static function MANGROVE_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mMANGROVE_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_TRAPDOOR);
	}

	public static function MANGROVE_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mMANGROVE_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_WALL_HANGING_SIGN);
	}

	public static function MANGROVE_WALL_SIGN() : WallSign{
		if(!isset(self::$_mMANGROVE_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_WALL_SIGN);
	}

	public static function MANGROVE_WOOD() : Wood{
		if(!isset(self::$_mMANGROVE_WOOD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMANGROVE_WOOD);
	}

	public static function MATERIAL_REDUCER() : ChemistryTable{
		if(!isset(self::$_mMATERIAL_REDUCER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMATERIAL_REDUCER);
	}

	public static function MELON() : Melon{
		if(!isset(self::$_mMELON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMELON);
	}

	public static function MELON_STEM() : MelonStem{
		if(!isset(self::$_mMELON_STEM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMELON_STEM);
	}

	public static function MOB_HEAD() : MobHead{
		if(!isset(self::$_mMOB_HEAD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMOB_HEAD);
	}

	public static function MONSTER_SPAWNER() : MonsterSpawner{
		if(!isset(self::$_mMONSTER_SPAWNER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMONSTER_SPAWNER);
	}

	public static function MOSSY_COBBLESTONE() : Opaque{
		if(!isset(self::$_mMOSSY_COBBLESTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMOSSY_COBBLESTONE);
	}

	public static function MOSSY_COBBLESTONE_SLAB() : Slab{
		if(!isset(self::$_mMOSSY_COBBLESTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMOSSY_COBBLESTONE_SLAB);
	}

	public static function MOSSY_COBBLESTONE_STAIRS() : Stair{
		if(!isset(self::$_mMOSSY_COBBLESTONE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMOSSY_COBBLESTONE_STAIRS);
	}

	public static function MOSSY_COBBLESTONE_WALL() : Wall{
		if(!isset(self::$_mMOSSY_COBBLESTONE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMOSSY_COBBLESTONE_WALL);
	}

	public static function MOSSY_STONE_BRICKS() : Opaque{
		if(!isset(self::$_mMOSSY_STONE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMOSSY_STONE_BRICKS);
	}

	public static function MOSSY_STONE_BRICK_SLAB() : Slab{
		if(!isset(self::$_mMOSSY_STONE_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMOSSY_STONE_BRICK_SLAB);
	}

	public static function MOSSY_STONE_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mMOSSY_STONE_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMOSSY_STONE_BRICK_STAIRS);
	}

	public static function MOSSY_STONE_BRICK_WALL() : Wall{
		if(!isset(self::$_mMOSSY_STONE_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMOSSY_STONE_BRICK_WALL);
	}

	public static function MUD() : Opaque{
		if(!isset(self::$_mMUD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMUD);
	}

	public static function MUDDY_MANGROVE_ROOTS() : SimplePillar{
		if(!isset(self::$_mMUDDY_MANGROVE_ROOTS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMUDDY_MANGROVE_ROOTS);
	}

	public static function MUD_BRICKS() : Opaque{
		if(!isset(self::$_mMUD_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMUD_BRICKS);
	}

	public static function MUD_BRICK_SLAB() : Slab{
		if(!isset(self::$_mMUD_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMUD_BRICK_SLAB);
	}

	public static function MUD_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mMUD_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMUD_BRICK_STAIRS);
	}

	public static function MUD_BRICK_WALL() : Wall{
		if(!isset(self::$_mMUD_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMUD_BRICK_WALL);
	}

	public static function MUSHROOM_STEM() : MushroomStem{
		if(!isset(self::$_mMUSHROOM_STEM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMUSHROOM_STEM);
	}

	public static function MYCELIUM() : Mycelium{
		if(!isset(self::$_mMYCELIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mMYCELIUM);
	}

	public static function NETHERITE() : Opaque{
		if(!isset(self::$_mNETHERITE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHERITE);
	}

	public static function NETHERRACK() : Netherrack{
		if(!isset(self::$_mNETHERRACK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHERRACK);
	}

	public static function NETHER_BRICKS() : Opaque{
		if(!isset(self::$_mNETHER_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_BRICKS);
	}

	public static function NETHER_BRICK_FENCE() : Fence{
		if(!isset(self::$_mNETHER_BRICK_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_BRICK_FENCE);
	}

	public static function NETHER_BRICK_SLAB() : Slab{
		if(!isset(self::$_mNETHER_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_BRICK_SLAB);
	}

	public static function NETHER_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mNETHER_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_BRICK_STAIRS);
	}

	public static function NETHER_BRICK_WALL() : Wall{
		if(!isset(self::$_mNETHER_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_BRICK_WALL);
	}

	public static function NETHER_GOLD_ORE() : NetherGoldOre{
		if(!isset(self::$_mNETHER_GOLD_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_GOLD_ORE);
	}

	public static function NETHER_PORTAL() : NetherPortal{
		if(!isset(self::$_mNETHER_PORTAL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_PORTAL);
	}

	public static function NETHER_QUARTZ_ORE() : NetherQuartzOre{
		if(!isset(self::$_mNETHER_QUARTZ_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_QUARTZ_ORE);
	}

	public static function NETHER_REACTOR_CORE() : NetherReactor{
		if(!isset(self::$_mNETHER_REACTOR_CORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_REACTOR_CORE);
	}

	public static function NETHER_SPROUTS() : NetherSprouts{
		if(!isset(self::$_mNETHER_SPROUTS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_SPROUTS);
	}

	public static function NETHER_WART() : NetherWartPlant{
		if(!isset(self::$_mNETHER_WART)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_WART);
	}

	public static function NETHER_WART_BLOCK() : Opaque{
		if(!isset(self::$_mNETHER_WART_BLOCK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNETHER_WART_BLOCK);
	}

	public static function NOTE_BLOCK() : Note{
		if(!isset(self::$_mNOTE_BLOCK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mNOTE_BLOCK);
	}

	public static function OAK_BUTTON() : WoodenButton{
		if(!isset(self::$_mOAK_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_BUTTON);
	}

	public static function OAK_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mOAK_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_CEILING_CENTER_HANGING_SIGN);
	}

	public static function OAK_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mOAK_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_CEILING_EDGES_HANGING_SIGN);
	}

	public static function OAK_DOOR() : WoodenDoor{
		if(!isset(self::$_mOAK_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_DOOR);
	}

	public static function OAK_FENCE() : WoodenFence{
		if(!isset(self::$_mOAK_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_FENCE);
	}

	public static function OAK_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mOAK_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_FENCE_GATE);
	}

	public static function OAK_LEAVES() : Leaves{
		if(!isset(self::$_mOAK_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_LEAVES);
	}

	public static function OAK_LOG() : Wood{
		if(!isset(self::$_mOAK_LOG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_LOG);
	}

	public static function OAK_PLANKS() : Planks{
		if(!isset(self::$_mOAK_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_PLANKS);
	}

	public static function OAK_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mOAK_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_PRESSURE_PLATE);
	}

	public static function OAK_SAPLING() : Sapling{
		if(!isset(self::$_mOAK_SAPLING)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_SAPLING);
	}

	public static function OAK_SIGN() : FloorSign{
		if(!isset(self::$_mOAK_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_SIGN);
	}

	public static function OAK_SLAB() : WoodenSlab{
		if(!isset(self::$_mOAK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_SLAB);
	}

	public static function OAK_STAIRS() : WoodenStairs{
		if(!isset(self::$_mOAK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_STAIRS);
	}

	public static function OAK_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mOAK_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_TRAPDOOR);
	}

	public static function OAK_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mOAK_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_WALL_HANGING_SIGN);
	}

	public static function OAK_WALL_SIGN() : WallSign{
		if(!isset(self::$_mOAK_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_WALL_SIGN);
	}

	public static function OAK_WOOD() : Wood{
		if(!isset(self::$_mOAK_WOOD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOAK_WOOD);
	}

	public static function OBSIDIAN() : Opaque{
		if(!isset(self::$_mOBSIDIAN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOBSIDIAN);
	}

	public static function OMINOUS_BANNER() : OminousFloorBanner{
		if(!isset(self::$_mOMINOUS_BANNER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOMINOUS_BANNER);
	}

	public static function OMINOUS_WALL_BANNER() : OminousWallBanner{
		if(!isset(self::$_mOMINOUS_WALL_BANNER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOMINOUS_WALL_BANNER);
	}

	public static function ORANGE_TULIP() : Flower{
		if(!isset(self::$_mORANGE_TULIP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mORANGE_TULIP);
	}

	public static function OXEYE_DAISY() : Flower{
		if(!isset(self::$_mOXEYE_DAISY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mOXEYE_DAISY);
	}

	public static function PACKED_ICE() : PackedIce{
		if(!isset(self::$_mPACKED_ICE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPACKED_ICE);
	}

	public static function PACKED_MUD() : Opaque{
		if(!isset(self::$_mPACKED_MUD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPACKED_MUD);
	}

	public static function PALE_OAK_BUTTON() : WoodenButton{
		if(!isset(self::$_mPALE_OAK_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_BUTTON);
	}

	public static function PALE_OAK_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mPALE_OAK_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_CEILING_CENTER_HANGING_SIGN);
	}

	public static function PALE_OAK_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mPALE_OAK_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_CEILING_EDGES_HANGING_SIGN);
	}

	public static function PALE_OAK_DOOR() : WoodenDoor{
		if(!isset(self::$_mPALE_OAK_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_DOOR);
	}

	public static function PALE_OAK_FENCE() : WoodenFence{
		if(!isset(self::$_mPALE_OAK_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_FENCE);
	}

	public static function PALE_OAK_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mPALE_OAK_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_FENCE_GATE);
	}

	public static function PALE_OAK_LEAVES() : Leaves{
		if(!isset(self::$_mPALE_OAK_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_LEAVES);
	}

	public static function PALE_OAK_LOG() : Wood{
		if(!isset(self::$_mPALE_OAK_LOG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_LOG);
	}

	public static function PALE_OAK_PLANKS() : Planks{
		if(!isset(self::$_mPALE_OAK_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_PLANKS);
	}

	public static function PALE_OAK_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mPALE_OAK_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_PRESSURE_PLATE);
	}

	public static function PALE_OAK_SIGN() : FloorSign{
		if(!isset(self::$_mPALE_OAK_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_SIGN);
	}

	public static function PALE_OAK_SLAB() : WoodenSlab{
		if(!isset(self::$_mPALE_OAK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_SLAB);
	}

	public static function PALE_OAK_STAIRS() : WoodenStairs{
		if(!isset(self::$_mPALE_OAK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_STAIRS);
	}

	public static function PALE_OAK_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mPALE_OAK_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_TRAPDOOR);
	}

	public static function PALE_OAK_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mPALE_OAK_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_WALL_HANGING_SIGN);
	}

	public static function PALE_OAK_WALL_SIGN() : WallSign{
		if(!isset(self::$_mPALE_OAK_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_WALL_SIGN);
	}

	public static function PALE_OAK_WOOD() : Wood{
		if(!isset(self::$_mPALE_OAK_WOOD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPALE_OAK_WOOD);
	}

	public static function PEONY() : DoublePlant{
		if(!isset(self::$_mPEONY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPEONY);
	}

	public static function PINK_PETALS() : PinkPetals{
		if(!isset(self::$_mPINK_PETALS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPINK_PETALS);
	}

	public static function PINK_TULIP() : Flower{
		if(!isset(self::$_mPINK_TULIP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPINK_TULIP);
	}

	public static function PITCHER_CROP() : PitcherCrop{
		if(!isset(self::$_mPITCHER_CROP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPITCHER_CROP);
	}

	public static function PITCHER_PLANT() : DoublePlant{
		if(!isset(self::$_mPITCHER_PLANT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPITCHER_PLANT);
	}

	public static function PODZOL() : Podzol{
		if(!isset(self::$_mPODZOL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPODZOL);
	}

	public static function POLISHED_ANDESITE() : Opaque{
		if(!isset(self::$_mPOLISHED_ANDESITE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_ANDESITE);
	}

	public static function POLISHED_ANDESITE_SLAB() : Slab{
		if(!isset(self::$_mPOLISHED_ANDESITE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_ANDESITE_SLAB);
	}

	public static function POLISHED_ANDESITE_STAIRS() : Stair{
		if(!isset(self::$_mPOLISHED_ANDESITE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_ANDESITE_STAIRS);
	}

	public static function POLISHED_BASALT() : SimplePillar{
		if(!isset(self::$_mPOLISHED_BASALT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BASALT);
	}

	public static function POLISHED_BLACKSTONE() : Opaque{
		if(!isset(self::$_mPOLISHED_BLACKSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE);
	}

	public static function POLISHED_BLACKSTONE_BRICKS() : Opaque{
		if(!isset(self::$_mPOLISHED_BLACKSTONE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE_BRICKS);
	}

	public static function POLISHED_BLACKSTONE_BRICK_SLAB() : Slab{
		if(!isset(self::$_mPOLISHED_BLACKSTONE_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE_BRICK_SLAB);
	}

	public static function POLISHED_BLACKSTONE_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mPOLISHED_BLACKSTONE_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE_BRICK_STAIRS);
	}

	public static function POLISHED_BLACKSTONE_BRICK_WALL() : Wall{
		if(!isset(self::$_mPOLISHED_BLACKSTONE_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE_BRICK_WALL);
	}

	public static function POLISHED_BLACKSTONE_BUTTON() : StoneButton{
		if(!isset(self::$_mPOLISHED_BLACKSTONE_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE_BUTTON);
	}

	public static function POLISHED_BLACKSTONE_PRESSURE_PLATE() : StonePressurePlate{
		if(!isset(self::$_mPOLISHED_BLACKSTONE_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE_PRESSURE_PLATE);
	}

	public static function POLISHED_BLACKSTONE_SLAB() : Slab{
		if(!isset(self::$_mPOLISHED_BLACKSTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE_SLAB);
	}

	public static function POLISHED_BLACKSTONE_STAIRS() : Stair{
		if(!isset(self::$_mPOLISHED_BLACKSTONE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE_STAIRS);
	}

	public static function POLISHED_BLACKSTONE_WALL() : Wall{
		if(!isset(self::$_mPOLISHED_BLACKSTONE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_BLACKSTONE_WALL);
	}

	public static function POLISHED_DEEPSLATE() : Opaque{
		if(!isset(self::$_mPOLISHED_DEEPSLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_DEEPSLATE);
	}

	public static function POLISHED_DEEPSLATE_SLAB() : Slab{
		if(!isset(self::$_mPOLISHED_DEEPSLATE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_DEEPSLATE_SLAB);
	}

	public static function POLISHED_DEEPSLATE_STAIRS() : Stair{
		if(!isset(self::$_mPOLISHED_DEEPSLATE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_DEEPSLATE_STAIRS);
	}

	public static function POLISHED_DEEPSLATE_WALL() : Wall{
		if(!isset(self::$_mPOLISHED_DEEPSLATE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_DEEPSLATE_WALL);
	}

	public static function POLISHED_DIORITE() : Opaque{
		if(!isset(self::$_mPOLISHED_DIORITE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_DIORITE);
	}

	public static function POLISHED_DIORITE_SLAB() : Slab{
		if(!isset(self::$_mPOLISHED_DIORITE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_DIORITE_SLAB);
	}

	public static function POLISHED_DIORITE_STAIRS() : Stair{
		if(!isset(self::$_mPOLISHED_DIORITE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_DIORITE_STAIRS);
	}

	public static function POLISHED_GRANITE() : Opaque{
		if(!isset(self::$_mPOLISHED_GRANITE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_GRANITE);
	}

	public static function POLISHED_GRANITE_SLAB() : Slab{
		if(!isset(self::$_mPOLISHED_GRANITE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_GRANITE_SLAB);
	}

	public static function POLISHED_GRANITE_STAIRS() : Stair{
		if(!isset(self::$_mPOLISHED_GRANITE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_GRANITE_STAIRS);
	}

	public static function POLISHED_TUFF() : Opaque{
		if(!isset(self::$_mPOLISHED_TUFF)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_TUFF);
	}

	public static function POLISHED_TUFF_SLAB() : Slab{
		if(!isset(self::$_mPOLISHED_TUFF_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_TUFF_SLAB);
	}

	public static function POLISHED_TUFF_STAIRS() : Stair{
		if(!isset(self::$_mPOLISHED_TUFF_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_TUFF_STAIRS);
	}

	public static function POLISHED_TUFF_WALL() : Wall{
		if(!isset(self::$_mPOLISHED_TUFF_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOLISHED_TUFF_WALL);
	}

	public static function POPPY() : Flower{
		if(!isset(self::$_mPOPPY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOPPY);
	}

	public static function POTATOES() : Potato{
		if(!isset(self::$_mPOTATOES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOTATOES);
	}

	public static function POTION_CAULDRON() : PotionCauldron{
		if(!isset(self::$_mPOTION_CAULDRON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOTION_CAULDRON);
	}

	public static function POWERED_RAIL() : PoweredRail{
		if(!isset(self::$_mPOWERED_RAIL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPOWERED_RAIL);
	}

	public static function PRISMARINE() : Opaque{
		if(!isset(self::$_mPRISMARINE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPRISMARINE);
	}

	public static function PRISMARINE_BRICKS() : Opaque{
		if(!isset(self::$_mPRISMARINE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPRISMARINE_BRICKS);
	}

	public static function PRISMARINE_BRICKS_SLAB() : Slab{
		if(!isset(self::$_mPRISMARINE_BRICKS_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPRISMARINE_BRICKS_SLAB);
	}

	public static function PRISMARINE_BRICKS_STAIRS() : Stair{
		if(!isset(self::$_mPRISMARINE_BRICKS_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPRISMARINE_BRICKS_STAIRS);
	}

	public static function PRISMARINE_SLAB() : Slab{
		if(!isset(self::$_mPRISMARINE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPRISMARINE_SLAB);
	}

	public static function PRISMARINE_STAIRS() : Stair{
		if(!isset(self::$_mPRISMARINE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPRISMARINE_STAIRS);
	}

	public static function PRISMARINE_WALL() : Wall{
		if(!isset(self::$_mPRISMARINE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPRISMARINE_WALL);
	}

	public static function PUMPKIN() : Pumpkin{
		if(!isset(self::$_mPUMPKIN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPUMPKIN);
	}

	public static function PUMPKIN_STEM() : PumpkinStem{
		if(!isset(self::$_mPUMPKIN_STEM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPUMPKIN_STEM);
	}

	public static function PURPLE_TORCH() : Torch{
		if(!isset(self::$_mPURPLE_TORCH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPURPLE_TORCH);
	}

	public static function PURPUR() : Opaque{
		if(!isset(self::$_mPURPUR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPURPUR);
	}

	public static function PURPUR_PILLAR() : SimplePillar{
		if(!isset(self::$_mPURPUR_PILLAR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPURPUR_PILLAR);
	}

	public static function PURPUR_SLAB() : Slab{
		if(!isset(self::$_mPURPUR_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPURPUR_SLAB);
	}

	public static function PURPUR_STAIRS() : Stair{
		if(!isset(self::$_mPURPUR_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mPURPUR_STAIRS);
	}

	public static function QUARTZ() : Opaque{
		if(!isset(self::$_mQUARTZ)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mQUARTZ);
	}

	public static function QUARTZ_BRICKS() : Opaque{
		if(!isset(self::$_mQUARTZ_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mQUARTZ_BRICKS);
	}

	public static function QUARTZ_PILLAR() : SimplePillar{
		if(!isset(self::$_mQUARTZ_PILLAR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mQUARTZ_PILLAR);
	}

	public static function QUARTZ_SLAB() : Slab{
		if(!isset(self::$_mQUARTZ_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mQUARTZ_SLAB);
	}

	public static function QUARTZ_STAIRS() : Stair{
		if(!isset(self::$_mQUARTZ_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mQUARTZ_STAIRS);
	}

	public static function RAIL() : Rail{
		if(!isset(self::$_mRAIL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRAIL);
	}

	public static function RAW_COPPER() : Opaque{
		if(!isset(self::$_mRAW_COPPER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRAW_COPPER);
	}

	public static function RAW_GOLD() : Opaque{
		if(!isset(self::$_mRAW_GOLD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRAW_GOLD);
	}

	public static function RAW_IRON() : Opaque{
		if(!isset(self::$_mRAW_IRON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRAW_IRON);
	}

	public static function REDSTONE() : Redstone{
		if(!isset(self::$_mREDSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mREDSTONE);
	}

	public static function REDSTONE_COMPARATOR() : RedstoneComparator{
		if(!isset(self::$_mREDSTONE_COMPARATOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mREDSTONE_COMPARATOR);
	}

	public static function REDSTONE_LAMP() : RedstoneLamp{
		if(!isset(self::$_mREDSTONE_LAMP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mREDSTONE_LAMP);
	}

	public static function REDSTONE_ORE() : RedstoneOre{
		if(!isset(self::$_mREDSTONE_ORE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mREDSTONE_ORE);
	}

	public static function REDSTONE_REPEATER() : RedstoneRepeater{
		if(!isset(self::$_mREDSTONE_REPEATER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mREDSTONE_REPEATER);
	}

	public static function REDSTONE_TORCH() : RedstoneTorch{
		if(!isset(self::$_mREDSTONE_TORCH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mREDSTONE_TORCH);
	}

	public static function REDSTONE_WIRE() : RedstoneWire{
		if(!isset(self::$_mREDSTONE_WIRE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mREDSTONE_WIRE);
	}

	public static function RED_MUSHROOM() : RedMushroom{
		if(!isset(self::$_mRED_MUSHROOM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_MUSHROOM);
	}

	public static function RED_MUSHROOM_BLOCK() : RedMushroomBlock{
		if(!isset(self::$_mRED_MUSHROOM_BLOCK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_MUSHROOM_BLOCK);
	}

	public static function RED_NETHER_BRICKS() : Opaque{
		if(!isset(self::$_mRED_NETHER_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_NETHER_BRICKS);
	}

	public static function RED_NETHER_BRICK_SLAB() : Slab{
		if(!isset(self::$_mRED_NETHER_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_NETHER_BRICK_SLAB);
	}

	public static function RED_NETHER_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mRED_NETHER_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_NETHER_BRICK_STAIRS);
	}

	public static function RED_NETHER_BRICK_WALL() : Wall{
		if(!isset(self::$_mRED_NETHER_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_NETHER_BRICK_WALL);
	}

	public static function RED_SAND() : Sand{
		if(!isset(self::$_mRED_SAND)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_SAND);
	}

	public static function RED_SANDSTONE() : Opaque{
		if(!isset(self::$_mRED_SANDSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_SANDSTONE);
	}

	public static function RED_SANDSTONE_SLAB() : Slab{
		if(!isset(self::$_mRED_SANDSTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_SANDSTONE_SLAB);
	}

	public static function RED_SANDSTONE_STAIRS() : Stair{
		if(!isset(self::$_mRED_SANDSTONE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_SANDSTONE_STAIRS);
	}

	public static function RED_SANDSTONE_WALL() : Wall{
		if(!isset(self::$_mRED_SANDSTONE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_SANDSTONE_WALL);
	}

	public static function RED_TORCH() : Torch{
		if(!isset(self::$_mRED_TORCH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_TORCH);
	}

	public static function RED_TULIP() : Flower{
		if(!isset(self::$_mRED_TULIP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRED_TULIP);
	}

	public static function REINFORCED_DEEPSLATE() : Opaque{
		if(!isset(self::$_mREINFORCED_DEEPSLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mREINFORCED_DEEPSLATE);
	}

	public static function RESERVED6() : Reserved6{
		if(!isset(self::$_mRESERVED6)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRESERVED6);
	}

	public static function RESIN() : Opaque{
		if(!isset(self::$_mRESIN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRESIN);
	}

	public static function RESIN_BRICKS() : Opaque{
		if(!isset(self::$_mRESIN_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRESIN_BRICKS);
	}

	public static function RESIN_BRICK_SLAB() : Slab{
		if(!isset(self::$_mRESIN_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRESIN_BRICK_SLAB);
	}

	public static function RESIN_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mRESIN_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRESIN_BRICK_STAIRS);
	}

	public static function RESIN_BRICK_WALL() : Wall{
		if(!isset(self::$_mRESIN_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRESIN_BRICK_WALL);
	}

	public static function RESIN_CLUMP() : ResinClump{
		if(!isset(self::$_mRESIN_CLUMP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRESIN_CLUMP);
	}

	public static function RESPAWN_ANCHOR() : RespawnAnchor{
		if(!isset(self::$_mRESPAWN_ANCHOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mRESPAWN_ANCHOR);
	}

	public static function ROSE_BUSH() : DoublePlant{
		if(!isset(self::$_mROSE_BUSH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mROSE_BUSH);
	}

	public static function SAND() : Sand{
		if(!isset(self::$_mSAND)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSAND);
	}

	public static function SANDSTONE() : Opaque{
		if(!isset(self::$_mSANDSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSANDSTONE);
	}

	public static function SANDSTONE_SLAB() : Slab{
		if(!isset(self::$_mSANDSTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSANDSTONE_SLAB);
	}

	public static function SANDSTONE_STAIRS() : Stair{
		if(!isset(self::$_mSANDSTONE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSANDSTONE_STAIRS);
	}

	public static function SANDSTONE_WALL() : Wall{
		if(!isset(self::$_mSANDSTONE_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSANDSTONE_WALL);
	}

	public static function SCULK() : Sculk{
		if(!isset(self::$_mSCULK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSCULK);
	}

	public static function SEA_LANTERN() : SeaLantern{
		if(!isset(self::$_mSEA_LANTERN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSEA_LANTERN);
	}

	public static function SEA_PICKLE() : SeaPickle{
		if(!isset(self::$_mSEA_PICKLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSEA_PICKLE);
	}

	public static function SHROOMLIGHT() : Opaque{
		if(!isset(self::$_mSHROOMLIGHT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSHROOMLIGHT);
	}

	public static function SHULKER_BOX() : ShulkerBox{
		if(!isset(self::$_mSHULKER_BOX)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSHULKER_BOX);
	}

	public static function SLIME() : Slime{
		if(!isset(self::$_mSLIME)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSLIME);
	}

	public static function SMALL_DRIPLEAF() : SmallDripleaf{
		if(!isset(self::$_mSMALL_DRIPLEAF)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMALL_DRIPLEAF);
	}

	public static function SMITHING_TABLE() : SmithingTable{
		if(!isset(self::$_mSMITHING_TABLE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMITHING_TABLE);
	}

	public static function SMOKER() : Furnace{
		if(!isset(self::$_mSMOKER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOKER);
	}

	public static function SMOOTH_BASALT() : Opaque{
		if(!isset(self::$_mSMOOTH_BASALT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_BASALT);
	}

	public static function SMOOTH_QUARTZ() : Opaque{
		if(!isset(self::$_mSMOOTH_QUARTZ)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_QUARTZ);
	}

	public static function SMOOTH_QUARTZ_SLAB() : Slab{
		if(!isset(self::$_mSMOOTH_QUARTZ_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_QUARTZ_SLAB);
	}

	public static function SMOOTH_QUARTZ_STAIRS() : Stair{
		if(!isset(self::$_mSMOOTH_QUARTZ_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_QUARTZ_STAIRS);
	}

	public static function SMOOTH_RED_SANDSTONE() : Opaque{
		if(!isset(self::$_mSMOOTH_RED_SANDSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_RED_SANDSTONE);
	}

	public static function SMOOTH_RED_SANDSTONE_SLAB() : Slab{
		if(!isset(self::$_mSMOOTH_RED_SANDSTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_RED_SANDSTONE_SLAB);
	}

	public static function SMOOTH_RED_SANDSTONE_STAIRS() : Stair{
		if(!isset(self::$_mSMOOTH_RED_SANDSTONE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_RED_SANDSTONE_STAIRS);
	}

	public static function SMOOTH_SANDSTONE() : Opaque{
		if(!isset(self::$_mSMOOTH_SANDSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_SANDSTONE);
	}

	public static function SMOOTH_SANDSTONE_SLAB() : Slab{
		if(!isset(self::$_mSMOOTH_SANDSTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_SANDSTONE_SLAB);
	}

	public static function SMOOTH_SANDSTONE_STAIRS() : Stair{
		if(!isset(self::$_mSMOOTH_SANDSTONE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_SANDSTONE_STAIRS);
	}

	public static function SMOOTH_STONE() : Opaque{
		if(!isset(self::$_mSMOOTH_STONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_STONE);
	}

	public static function SMOOTH_STONE_SLAB() : Slab{
		if(!isset(self::$_mSMOOTH_STONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSMOOTH_STONE_SLAB);
	}

	public static function SNOW() : Snow{
		if(!isset(self::$_mSNOW)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSNOW);
	}

	public static function SNOW_LAYER() : SnowLayer{
		if(!isset(self::$_mSNOW_LAYER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSNOW_LAYER);
	}

	public static function SOUL_CAMPFIRE() : SoulCampfire{
		if(!isset(self::$_mSOUL_CAMPFIRE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSOUL_CAMPFIRE);
	}

	public static function SOUL_FIRE() : SoulFire{
		if(!isset(self::$_mSOUL_FIRE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSOUL_FIRE);
	}

	public static function SOUL_LANTERN() : Lantern{
		if(!isset(self::$_mSOUL_LANTERN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSOUL_LANTERN);
	}

	public static function SOUL_SAND() : SoulSand{
		if(!isset(self::$_mSOUL_SAND)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSOUL_SAND);
	}

	public static function SOUL_SOIL() : Opaque{
		if(!isset(self::$_mSOUL_SOIL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSOUL_SOIL);
	}

	public static function SOUL_TORCH() : Torch{
		if(!isset(self::$_mSOUL_TORCH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSOUL_TORCH);
	}

	public static function SPONGE() : Sponge{
		if(!isset(self::$_mSPONGE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPONGE);
	}

	public static function SPORE_BLOSSOM() : SporeBlossom{
		if(!isset(self::$_mSPORE_BLOSSOM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPORE_BLOSSOM);
	}

	public static function SPRUCE_BUTTON() : WoodenButton{
		if(!isset(self::$_mSPRUCE_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_BUTTON);
	}

	public static function SPRUCE_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mSPRUCE_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_CEILING_CENTER_HANGING_SIGN);
	}

	public static function SPRUCE_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mSPRUCE_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_CEILING_EDGES_HANGING_SIGN);
	}

	public static function SPRUCE_DOOR() : WoodenDoor{
		if(!isset(self::$_mSPRUCE_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_DOOR);
	}

	public static function SPRUCE_FENCE() : WoodenFence{
		if(!isset(self::$_mSPRUCE_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_FENCE);
	}

	public static function SPRUCE_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mSPRUCE_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_FENCE_GATE);
	}

	public static function SPRUCE_LEAVES() : Leaves{
		if(!isset(self::$_mSPRUCE_LEAVES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_LEAVES);
	}

	public static function SPRUCE_LOG() : Wood{
		if(!isset(self::$_mSPRUCE_LOG)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_LOG);
	}

	public static function SPRUCE_PLANKS() : Planks{
		if(!isset(self::$_mSPRUCE_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_PLANKS);
	}

	public static function SPRUCE_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mSPRUCE_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_PRESSURE_PLATE);
	}

	public static function SPRUCE_SAPLING() : Sapling{
		if(!isset(self::$_mSPRUCE_SAPLING)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_SAPLING);
	}

	public static function SPRUCE_SIGN() : FloorSign{
		if(!isset(self::$_mSPRUCE_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_SIGN);
	}

	public static function SPRUCE_SLAB() : WoodenSlab{
		if(!isset(self::$_mSPRUCE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_SLAB);
	}

	public static function SPRUCE_STAIRS() : WoodenStairs{
		if(!isset(self::$_mSPRUCE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_STAIRS);
	}

	public static function SPRUCE_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mSPRUCE_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_TRAPDOOR);
	}

	public static function SPRUCE_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mSPRUCE_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_WALL_HANGING_SIGN);
	}

	public static function SPRUCE_WALL_SIGN() : WallSign{
		if(!isset(self::$_mSPRUCE_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_WALL_SIGN);
	}

	public static function SPRUCE_WOOD() : Wood{
		if(!isset(self::$_mSPRUCE_WOOD)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSPRUCE_WOOD);
	}

	public static function STAINED_CLAY() : StainedHardenedClay{
		if(!isset(self::$_mSTAINED_CLAY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTAINED_CLAY);
	}

	public static function STAINED_GLASS() : StainedGlass{
		if(!isset(self::$_mSTAINED_GLASS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTAINED_GLASS);
	}

	public static function STAINED_GLASS_PANE() : StainedGlassPane{
		if(!isset(self::$_mSTAINED_GLASS_PANE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTAINED_GLASS_PANE);
	}

	public static function STAINED_HARDENED_GLASS() : StainedHardenedGlass{
		if(!isset(self::$_mSTAINED_HARDENED_GLASS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTAINED_HARDENED_GLASS);
	}

	public static function STAINED_HARDENED_GLASS_PANE() : StainedHardenedGlassPane{
		if(!isset(self::$_mSTAINED_HARDENED_GLASS_PANE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTAINED_HARDENED_GLASS_PANE);
	}

	public static function STONE() : Opaque{
		if(!isset(self::$_mSTONE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONE);
	}

	public static function STONECUTTER() : Stonecutter{
		if(!isset(self::$_mSTONECUTTER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONECUTTER);
	}

	public static function STONE_BRICKS() : Opaque{
		if(!isset(self::$_mSTONE_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONE_BRICKS);
	}

	public static function STONE_BRICK_SLAB() : Slab{
		if(!isset(self::$_mSTONE_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONE_BRICK_SLAB);
	}

	public static function STONE_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mSTONE_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONE_BRICK_STAIRS);
	}

	public static function STONE_BRICK_WALL() : Wall{
		if(!isset(self::$_mSTONE_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONE_BRICK_WALL);
	}

	public static function STONE_BUTTON() : StoneButton{
		if(!isset(self::$_mSTONE_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONE_BUTTON);
	}

	public static function STONE_PRESSURE_PLATE() : StonePressurePlate{
		if(!isset(self::$_mSTONE_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONE_PRESSURE_PLATE);
	}

	public static function STONE_SLAB() : Slab{
		if(!isset(self::$_mSTONE_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONE_SLAB);
	}

	public static function STONE_STAIRS() : Stair{
		if(!isset(self::$_mSTONE_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTONE_STAIRS);
	}

	public static function STRUCTURE_VOID() : StructureVoid{
		if(!isset(self::$_mSTRUCTURE_VOID)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSTRUCTURE_VOID);
	}

	public static function SUGARCANE() : Sugarcane{
		if(!isset(self::$_mSUGARCANE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSUGARCANE);
	}

	public static function SUNFLOWER() : DoublePlant{
		if(!isset(self::$_mSUNFLOWER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSUNFLOWER);
	}

	public static function SWEET_BERRY_BUSH() : SweetBerryBush{
		if(!isset(self::$_mSWEET_BERRY_BUSH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mSWEET_BERRY_BUSH);
	}

	public static function TALL_GRASS() : TallGrass{
		if(!isset(self::$_mTALL_GRASS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTALL_GRASS);
	}

	public static function TINTED_GLASS() : TintedGlass{
		if(!isset(self::$_mTINTED_GLASS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTINTED_GLASS);
	}

	public static function TNT() : TNT{
		if(!isset(self::$_mTNT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTNT);
	}

	public static function TORCH() : Torch{
		if(!isset(self::$_mTORCH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTORCH);
	}

	public static function TORCHFLOWER() : Flower{
		if(!isset(self::$_mTORCHFLOWER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTORCHFLOWER);
	}

	public static function TORCHFLOWER_CROP() : TorchflowerCrop{
		if(!isset(self::$_mTORCHFLOWER_CROP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTORCHFLOWER_CROP);
	}

	public static function TRAPPED_CHEST() : TrappedChest{
		if(!isset(self::$_mTRAPPED_CHEST)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTRAPPED_CHEST);
	}

	public static function TRIPWIRE() : Tripwire{
		if(!isset(self::$_mTRIPWIRE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTRIPWIRE);
	}

	public static function TRIPWIRE_HOOK() : TripwireHook{
		if(!isset(self::$_mTRIPWIRE_HOOK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTRIPWIRE_HOOK);
	}

	public static function TUFF() : Opaque{
		if(!isset(self::$_mTUFF)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTUFF);
	}

	public static function TUFF_BRICKS() : Opaque{
		if(!isset(self::$_mTUFF_BRICKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTUFF_BRICKS);
	}

	public static function TUFF_BRICK_SLAB() : Slab{
		if(!isset(self::$_mTUFF_BRICK_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTUFF_BRICK_SLAB);
	}

	public static function TUFF_BRICK_STAIRS() : Stair{
		if(!isset(self::$_mTUFF_BRICK_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTUFF_BRICK_STAIRS);
	}

	public static function TUFF_BRICK_WALL() : Wall{
		if(!isset(self::$_mTUFF_BRICK_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTUFF_BRICK_WALL);
	}

	public static function TUFF_SLAB() : Slab{
		if(!isset(self::$_mTUFF_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTUFF_SLAB);
	}

	public static function TUFF_STAIRS() : Stair{
		if(!isset(self::$_mTUFF_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTUFF_STAIRS);
	}

	public static function TUFF_WALL() : Wall{
		if(!isset(self::$_mTUFF_WALL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTUFF_WALL);
	}

	public static function TWISTING_VINES() : NetherVines{
		if(!isset(self::$_mTWISTING_VINES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mTWISTING_VINES);
	}

	public static function UNDERWATER_TORCH() : UnderwaterTorch{
		if(!isset(self::$_mUNDERWATER_TORCH)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mUNDERWATER_TORCH);
	}

	public static function VINES() : Vine{
		if(!isset(self::$_mVINES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mVINES);
	}

	public static function WALL_BANNER() : WallBanner{
		if(!isset(self::$_mWALL_BANNER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWALL_BANNER);
	}

	public static function WALL_CORAL_FAN() : WallCoralFan{
		if(!isset(self::$_mWALL_CORAL_FAN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWALL_CORAL_FAN);
	}

	public static function WARPED_BUTTON() : WoodenButton{
		if(!isset(self::$_mWARPED_BUTTON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_BUTTON);
	}

	public static function WARPED_CEILING_CENTER_HANGING_SIGN() : CeilingCenterHangingSign{
		if(!isset(self::$_mWARPED_CEILING_CENTER_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_CEILING_CENTER_HANGING_SIGN);
	}

	public static function WARPED_CEILING_EDGES_HANGING_SIGN() : CeilingEdgesHangingSign{
		if(!isset(self::$_mWARPED_CEILING_EDGES_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_CEILING_EDGES_HANGING_SIGN);
	}

	public static function WARPED_DOOR() : WoodenDoor{
		if(!isset(self::$_mWARPED_DOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_DOOR);
	}

	public static function WARPED_FENCE() : WoodenFence{
		if(!isset(self::$_mWARPED_FENCE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_FENCE);
	}

	public static function WARPED_FENCE_GATE() : FenceGate{
		if(!isset(self::$_mWARPED_FENCE_GATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_FENCE_GATE);
	}

	public static function WARPED_FUNGUS() : NetherFungus{
		if(!isset(self::$_mWARPED_FUNGUS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_FUNGUS);
	}

	public static function WARPED_HYPHAE() : Wood{
		if(!isset(self::$_mWARPED_HYPHAE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_HYPHAE);
	}

	public static function WARPED_NYLIUM() : Nylium{
		if(!isset(self::$_mWARPED_NYLIUM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_NYLIUM);
	}

	public static function WARPED_PLANKS() : Planks{
		if(!isset(self::$_mWARPED_PLANKS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_PLANKS);
	}

	public static function WARPED_PRESSURE_PLATE() : WoodenPressurePlate{
		if(!isset(self::$_mWARPED_PRESSURE_PLATE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_PRESSURE_PLATE);
	}

	public static function WARPED_ROOTS() : NetherRoots{
		if(!isset(self::$_mWARPED_ROOTS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_ROOTS);
	}

	public static function WARPED_SIGN() : FloorSign{
		if(!isset(self::$_mWARPED_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_SIGN);
	}

	public static function WARPED_SLAB() : WoodenSlab{
		if(!isset(self::$_mWARPED_SLAB)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_SLAB);
	}

	public static function WARPED_STAIRS() : WoodenStairs{
		if(!isset(self::$_mWARPED_STAIRS)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_STAIRS);
	}

	public static function WARPED_STEM() : Wood{
		if(!isset(self::$_mWARPED_STEM)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_STEM);
	}

	public static function WARPED_TRAPDOOR() : WoodenTrapdoor{
		if(!isset(self::$_mWARPED_TRAPDOOR)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_TRAPDOOR);
	}

	public static function WARPED_WALL_HANGING_SIGN() : WallHangingSign{
		if(!isset(self::$_mWARPED_WALL_HANGING_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_WALL_HANGING_SIGN);
	}

	public static function WARPED_WALL_SIGN() : WallSign{
		if(!isset(self::$_mWARPED_WALL_SIGN)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_WALL_SIGN);
	}

	public static function WARPED_WART_BLOCK() : Opaque{
		if(!isset(self::$_mWARPED_WART_BLOCK)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWARPED_WART_BLOCK);
	}

	public static function WATER() : Water{
		if(!isset(self::$_mWATER)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWATER);
	}

	public static function WATER_CAULDRON() : WaterCauldron{
		if(!isset(self::$_mWATER_CAULDRON)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWATER_CAULDRON);
	}

	public static function WEEPING_VINES() : NetherVines{
		if(!isset(self::$_mWEEPING_VINES)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWEEPING_VINES);
	}

	public static function WEIGHTED_PRESSURE_PLATE_HEAVY() : WeightedPressurePlateHeavy{
		if(!isset(self::$_mWEIGHTED_PRESSURE_PLATE_HEAVY)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWEIGHTED_PRESSURE_PLATE_HEAVY);
	}

	public static function WEIGHTED_PRESSURE_PLATE_LIGHT() : WeightedPressurePlateLight{
		if(!isset(self::$_mWEIGHTED_PRESSURE_PLATE_LIGHT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWEIGHTED_PRESSURE_PLATE_LIGHT);
	}

	public static function WHEAT() : Wheat{
		if(!isset(self::$_mWHEAT)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWHEAT);
	}

	public static function WHITE_TULIP() : Flower{
		if(!isset(self::$_mWHITE_TULIP)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWHITE_TULIP);
	}

	public static function WITHER_ROSE() : WitherRose{
		if(!isset(self::$_mWITHER_ROSE)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWITHER_ROSE);
	}

	public static function WOOL() : Wool{
		if(!isset(self::$_mWOOL)){ self::init(); }
		return VanillaBlocksInputs::cloneMember(self::$_mWOOL);
	}
}
