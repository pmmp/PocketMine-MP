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

namespace pocketmine\item;

use pocketmine\utils\Utils;
use function array_keys;
use function count;
use function implode;
use function mb_strtoupper;

/**
 * Allows getting a new instance of any item implemented by PocketMine-MP
 * Every item here also has a constant of the same name in {@link ItemTypeIds} to enable items to be identified
 *
 * This class is generated automatically from source class {@link VanillaItemsInputs}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/codegen/registry-interface.php
 */
final class VanillaItems{
	private static Boat $_mACACIA_BOAT;
	private static HangingSign $_mACACIA_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mACACIA_SIGN;
	private static Item $_mAIR;
	private static Item $_mAMETHYST_SHARD;
	private static Apple $_mAPPLE;
	private static Arrow $_mARROW;
	private static BakedPotato $_mBAKED_POTATO;
	private static Bamboo $_mBAMBOO;
	private static HangingSign $_mBAMBOO_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mBAMBOO_SIGN;
	private static Banner $_mBANNER;
	private static Beetroot $_mBEETROOT;
	private static BeetrootSeeds $_mBEETROOT_SEEDS;
	private static BeetrootSoup $_mBEETROOT_SOUP;
	private static Boat $_mBIRCH_BOAT;
	private static HangingSign $_mBIRCH_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mBIRCH_SIGN;
	private static Item $_mBLAZE_POWDER;
	private static BlazeRod $_mBLAZE_ROD;
	private static Item $_mBLEACH;
	private static Item $_mBONE;
	private static Fertilizer $_mBONE_MEAL;
	private static Book $_mBOOK;
	private static Bow $_mBOW;
	private static Bowl $_mBOWL;
	private static Bread $_mBREAD;
	private static Item $_mBRICK;
	private static Bucket $_mBUCKET;
	private static Carrot $_mCARROT;
	private static Armor $_mCHAINMAIL_BOOTS;
	private static Armor $_mCHAINMAIL_CHESTPLATE;
	private static Armor $_mCHAINMAIL_HELMET;
	private static Armor $_mCHAINMAIL_LEGGINGS;
	private static Coal $_mCHARCOAL;
	private static Item $_mCHEMICAL_ALUMINIUM_OXIDE;
	private static Item $_mCHEMICAL_AMMONIA;
	private static Item $_mCHEMICAL_BARIUM_SULPHATE;
	private static Item $_mCHEMICAL_BENZENE;
	private static Item $_mCHEMICAL_BORON_TRIOXIDE;
	private static Item $_mCHEMICAL_CALCIUM_BROMIDE;
	private static Item $_mCHEMICAL_CALCIUM_CHLORIDE;
	private static Item $_mCHEMICAL_CERIUM_CHLORIDE;
	private static Item $_mCHEMICAL_CHARCOAL;
	private static Item $_mCHEMICAL_CRUDE_OIL;
	private static Item $_mCHEMICAL_GLUE;
	private static Item $_mCHEMICAL_HYDROGEN_PEROXIDE;
	private static Item $_mCHEMICAL_HYPOCHLORITE;
	private static Item $_mCHEMICAL_INK;
	private static Item $_mCHEMICAL_IRON_SULPHIDE;
	private static Item $_mCHEMICAL_LATEX;
	private static Item $_mCHEMICAL_LITHIUM_HYDRIDE;
	private static Item $_mCHEMICAL_LUMINOL;
	private static Item $_mCHEMICAL_MAGNESIUM_NITRATE;
	private static Item $_mCHEMICAL_MAGNESIUM_OXIDE;
	private static Item $_mCHEMICAL_MAGNESIUM_SALTS;
	private static Item $_mCHEMICAL_MERCURIC_CHLORIDE;
	private static Item $_mCHEMICAL_POLYETHYLENE;
	private static Item $_mCHEMICAL_POTASSIUM_CHLORIDE;
	private static Item $_mCHEMICAL_POTASSIUM_IODIDE;
	private static Item $_mCHEMICAL_RUBBISH;
	private static Item $_mCHEMICAL_SALT;
	private static Item $_mCHEMICAL_SOAP;
	private static Item $_mCHEMICAL_SODIUM_ACETATE;
	private static Item $_mCHEMICAL_SODIUM_FLUORIDE;
	private static Item $_mCHEMICAL_SODIUM_HYDRIDE;
	private static Item $_mCHEMICAL_SODIUM_HYDROXIDE;
	private static Item $_mCHEMICAL_SODIUM_HYPOCHLORITE;
	private static Item $_mCHEMICAL_SODIUM_OXIDE;
	private static Item $_mCHEMICAL_SUGAR;
	private static Item $_mCHEMICAL_SULPHATE;
	private static Item $_mCHEMICAL_TUNGSTEN_CHLORIDE;
	private static Item $_mCHEMICAL_WATER;
	private static HangingSign $_mCHERRY_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mCHERRY_SIGN;
	private static ChorusFruit $_mCHORUS_FRUIT;
	private static Item $_mCLAY;
	private static Clock $_mCLOCK;
	private static Clownfish $_mCLOWNFISH;
	private static Coal $_mCOAL;
	private static Item $_mCOAST_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static CocoaBeans $_mCOCOA_BEANS;
	private static Compass $_mCOMPASS;
	private static CookedChicken $_mCOOKED_CHICKEN;
	private static CookedFish $_mCOOKED_FISH;
	private static CookedMutton $_mCOOKED_MUTTON;
	private static CookedPorkchop $_mCOOKED_PORKCHOP;
	private static CookedRabbit $_mCOOKED_RABBIT;
	private static CookedSalmon $_mCOOKED_SALMON;
	private static Cookie $_mCOOKIE;
	private static Axe $_mCOPPER_AXE;
	private static Armor $_mCOPPER_BOOTS;
	private static Armor $_mCOPPER_CHESTPLATE;
	private static Armor $_mCOPPER_HELMET;
	private static Hoe $_mCOPPER_HOE;
	private static Item $_mCOPPER_INGOT;
	private static Armor $_mCOPPER_LEGGINGS;
	private static Item $_mCOPPER_NUGGET;
	private static Pickaxe $_mCOPPER_PICKAXE;
	private static Shovel $_mCOPPER_SHOVEL;
	private static Sword $_mCOPPER_SWORD;
	private static CoralFan $_mCORAL_FAN;
	private static HangingSign $_mCRIMSON_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mCRIMSON_SIGN;
	private static Boat $_mDARK_OAK_BOAT;
	private static HangingSign $_mDARK_OAK_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mDARK_OAK_SIGN;
	private static Item $_mDIAMOND;
	private static Axe $_mDIAMOND_AXE;
	private static Armor $_mDIAMOND_BOOTS;
	private static Armor $_mDIAMOND_CHESTPLATE;
	private static Armor $_mDIAMOND_HELMET;
	private static Hoe $_mDIAMOND_HOE;
	private static Armor $_mDIAMOND_LEGGINGS;
	private static Pickaxe $_mDIAMOND_PICKAXE;
	private static Shovel $_mDIAMOND_SHOVEL;
	private static Sword $_mDIAMOND_SWORD;
	private static Item $_mDISC_FRAGMENT_5;
	private static Item $_mDRAGON_BREATH;
	private static DriedKelp $_mDRIED_KELP;
	private static Item $_mDUNE_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static Dye $_mDYE;
	private static Item $_mECHO_SHARD;
	private static Egg $_mEGG;
	private static Item $_mEMERALD;
	private static EnchantedBook $_mENCHANTED_BOOK;
	private static GoldenAppleEnchanted $_mENCHANTED_GOLDEN_APPLE;
	private static EnderPearl $_mENDER_PEARL;
	private static EndCrystal $_mEND_CRYSTAL;
	private static ExperienceBottle $_mEXPERIENCE_BOTTLE;
	private static Item $_mEYE_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static Item $_mFEATHER;
	private static Item $_mFERMENTED_SPIDER_EYE;
	private static FireworkRocket $_mFIREWORK_ROCKET;
	private static FireworkStar $_mFIREWORK_STAR;
	private static FireCharge $_mFIRE_CHARGE;
	private static FishingRod $_mFISHING_ROD;
	private static Item $_mFLINT;
	private static FlintSteel $_mFLINT_AND_STEEL;
	private static Item $_mGHAST_TEAR;
	private static GlassBottle $_mGLASS_BOTTLE;
	private static Item $_mGLISTERING_MELON;
	private static Item $_mGLOWSTONE_DUST;
	private static GlowBerries $_mGLOW_BERRIES;
	private static Item $_mGLOW_INK_SAC;
	private static GoatHorn $_mGOAT_HORN;
	private static GoldenApple $_mGOLDEN_APPLE;
	private static Axe $_mGOLDEN_AXE;
	private static Armor $_mGOLDEN_BOOTS;
	private static GoldenCarrot $_mGOLDEN_CARROT;
	private static Armor $_mGOLDEN_CHESTPLATE;
	private static Armor $_mGOLDEN_HELMET;
	private static Hoe $_mGOLDEN_HOE;
	private static Armor $_mGOLDEN_LEGGINGS;
	private static Pickaxe $_mGOLDEN_PICKAXE;
	private static Shovel $_mGOLDEN_SHOVEL;
	private static Sword $_mGOLDEN_SWORD;
	private static Item $_mGOLD_INGOT;
	private static Item $_mGOLD_NUGGET;
	private static Item $_mGUNPOWDER;
	private static Item $_mHEART_OF_THE_SEA;
	private static Item $_mHONEYCOMB;
	private static HoneyBottle $_mHONEY_BOTTLE;
	private static Item $_mHOST_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static IceBomb $_mICE_BOMB;
	private static Item $_mINK_SAC;
	private static Axe $_mIRON_AXE;
	private static Armor $_mIRON_BOOTS;
	private static Armor $_mIRON_CHESTPLATE;
	private static Armor $_mIRON_HELMET;
	private static Hoe $_mIRON_HOE;
	private static Item $_mIRON_INGOT;
	private static Armor $_mIRON_LEGGINGS;
	private static Item $_mIRON_NUGGET;
	private static Pickaxe $_mIRON_PICKAXE;
	private static Shovel $_mIRON_SHOVEL;
	private static Sword $_mIRON_SWORD;
	private static Boat $_mJUNGLE_BOAT;
	private static HangingSign $_mJUNGLE_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mJUNGLE_SIGN;
	private static Item $_mLAPIS_LAZULI;
	private static LiquidBucket $_mLAVA_BUCKET;
	private static Item $_mLEATHER;
	private static Armor $_mLEATHER_BOOTS;
	private static Armor $_mLEATHER_CAP;
	private static Armor $_mLEATHER_PANTS;
	private static Armor $_mLEATHER_TUNIC;
	private static SplashPotion $_mLINGERING_POTION;
	private static Item $_mMAGMA_CREAM;
	private static Boat $_mMANGROVE_BOAT;
	private static HangingSign $_mMANGROVE_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mMANGROVE_SIGN;
	private static Medicine $_mMEDICINE;
	private static Melon $_mMELON;
	private static MelonSeeds $_mMELON_SEEDS;
	private static MilkBucket $_mMILK_BUCKET;
	private static Minecart $_mMINECART;
	private static MushroomStew $_mMUSHROOM_STEW;
	private static NameTag $_mNAME_TAG;
	private static Item $_mNAUTILUS_SHELL;
	private static Axe $_mNETHERITE_AXE;
	private static Armor $_mNETHERITE_BOOTS;
	private static Armor $_mNETHERITE_CHESTPLATE;
	private static Armor $_mNETHERITE_HELMET;
	private static Hoe $_mNETHERITE_HOE;
	private static Item $_mNETHERITE_INGOT;
	private static Armor $_mNETHERITE_LEGGINGS;
	private static Pickaxe $_mNETHERITE_PICKAXE;
	private static Item $_mNETHERITE_SCRAP;
	private static Shovel $_mNETHERITE_SHOVEL;
	private static Sword $_mNETHERITE_SWORD;
	private static Item $_mNETHERITE_UPGRADE_SMITHING_TEMPLATE;
	private static Item $_mNETHER_BRICK;
	private static Item $_mNETHER_QUARTZ;
	private static Item $_mNETHER_STAR;
	private static Boat $_mOAK_BOAT;
	private static HangingSign $_mOAK_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mOAK_SIGN;
	private static ItemBlockWallOrFloor $_mOMINOUS_BANNER;
	private static PaintingItem $_mPAINTING;
	private static HangingSign $_mPALE_OAK_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mPALE_OAK_SIGN;
	private static Item $_mPAPER;
	private static Item $_mPHANTOM_MEMBRANE;
	private static PitcherPod $_mPITCHER_POD;
	private static PoisonousPotato $_mPOISONOUS_POTATO;
	private static Item $_mPOPPED_CHORUS_FRUIT;
	private static Potato $_mPOTATO;
	private static Potion $_mPOTION;
	private static Item $_mPRISMARINE_CRYSTALS;
	private static Item $_mPRISMARINE_SHARD;
	private static Pufferfish $_mPUFFERFISH;
	private static PumpkinPie $_mPUMPKIN_PIE;
	private static PumpkinSeeds $_mPUMPKIN_SEEDS;
	private static Item $_mRABBIT_FOOT;
	private static Item $_mRABBIT_HIDE;
	private static RabbitStew $_mRABBIT_STEW;
	private static Item $_mRAISER_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static RawBeef $_mRAW_BEEF;
	private static RawChicken $_mRAW_CHICKEN;
	private static Item $_mRAW_COPPER;
	private static RawFish $_mRAW_FISH;
	private static Item $_mRAW_GOLD;
	private static Item $_mRAW_IRON;
	private static RawMutton $_mRAW_MUTTON;
	private static RawPorkchop $_mRAW_PORKCHOP;
	private static RawRabbit $_mRAW_RABBIT;
	private static RawSalmon $_mRAW_SALMON;
	private static Record $_mRECORD_11;
	private static Record $_mRECORD_13;
	private static Record $_mRECORD_5;
	private static Record $_mRECORD_BLOCKS;
	private static Record $_mRECORD_CAT;
	private static Record $_mRECORD_CHIRP;
	private static Record $_mRECORD_CREATOR;
	private static Record $_mRECORD_CREATOR_MUSIC_BOX;
	private static Record $_mRECORD_FAR;
	private static Record $_mRECORD_LAVA_CHICKEN;
	private static Record $_mRECORD_MALL;
	private static Record $_mRECORD_MELLOHI;
	private static Record $_mRECORD_OTHERSIDE;
	private static Record $_mRECORD_PIGSTEP;
	private static Record $_mRECORD_PRECIPICE;
	private static Record $_mRECORD_RELIC;
	private static Record $_mRECORD_STAL;
	private static Record $_mRECORD_STRAD;
	private static Record $_mRECORD_WAIT;
	private static Record $_mRECORD_WARD;
	private static Item $_mRECOVERY_COMPASS;
	private static Redstone $_mREDSTONE_DUST;
	private static Item $_mRESIN_BRICK;
	private static Item $_mRIB_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static RottenFlesh $_mROTTEN_FLESH;
	private static Item $_mSCUTE;
	private static Item $_mSENTRY_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static Item $_mSHAPER_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static Shears $_mSHEARS;
	private static Item $_mSHULKER_SHELL;
	private static Item $_mSILENCE_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static Item $_mSLIMEBALL;
	private static Item $_mSNOUT_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static Snowball $_mSNOWBALL;
	private static SpiderEye $_mSPIDER_EYE;
	private static Item $_mSPIRE_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static SplashPotion $_mSPLASH_POTION;
	private static Boat $_mSPRUCE_BOAT;
	private static HangingSign $_mSPRUCE_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mSPRUCE_SIGN;
	private static Spyglass $_mSPYGLASS;
	private static SpawnEgg $_mSQUID_SPAWN_EGG;
	private static Steak $_mSTEAK;
	private static Stick $_mSTICK;
	private static Axe $_mSTONE_AXE;
	private static Hoe $_mSTONE_HOE;
	private static Pickaxe $_mSTONE_PICKAXE;
	private static Shovel $_mSTONE_SHOVEL;
	private static Sword $_mSTONE_SWORD;
	private static StringItem $_mSTRING;
	private static Item $_mSUGAR;
	private static SuspiciousStew $_mSUSPICIOUS_STEW;
	private static SweetBerries $_mSWEET_BERRIES;
	private static Item $_mTIDE_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static TorchflowerSeeds $_mTORCHFLOWER_SEEDS;
	private static Totem $_mTOTEM;
	private static Trident $_mTRIDENT;
	private static TurtleHelmet $_mTURTLE_HELMET;
	private static Item $_mVEX_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static SpawnEgg $_mVILLAGER_SPAWN_EGG;
	private static Item $_mWARD_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static HangingSign $_mWARPED_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mWARPED_SIGN;
	private static LiquidBucket $_mWATER_BUCKET;
	private static Item $_mWAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static Item $_mWHEAT;
	private static WheatSeeds $_mWHEAT_SEEDS;
	private static Item $_mWILD_ARMOR_TRIM_SMITHING_TEMPLATE;
	private static Axe $_mWOODEN_AXE;
	private static Hoe $_mWOODEN_HOE;
	private static Pickaxe $_mWOODEN_PICKAXE;
	private static Shovel $_mWOODEN_SHOVEL;
	private static Sword $_mWOODEN_SWORD;
	private static WritableBook $_mWRITABLE_BOOK;
	private static WrittenBook $_mWRITTEN_BOOK;
	private static SpawnEgg $_mZOMBIE_SPAWN_EGG;

	/**
	 * @var Item[]
	 * @phpstan-var array<string, Item>
	 */
	private static array $members;

	private static bool $initialized = false;

	private function __construct(){
		//NOOP
	}

	/**
	 * Hack to allow ignoring PHPStan wrong type assignment error in one place instead of hundreds or thousands
	 * Assumes that the input value already matches the expected type. If not, a TypeError will be thrown on assignment.
	 *
	 * @phpstan-param \Closure(never) : Item $closure
	 */
	private static function unsafeAssign(\Closure $closure, Item $memberValue) : void{
		/**
		 * This type is not correct either (the param is actually a subtype of Item) but it's called
		 * unsafeAssign for a reason :)
		 * @phpstan-var \Closure(Item) : Item $closure
		 */
		$closure($memberValue);
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return array<string, \Closure(never) : Item>
	 */
	private static function getInitAssigners() : array{
		return [
			"acacia_boat" => fn(Boat $v) => self::$_mACACIA_BOAT = $v,
			"acacia_hanging_sign" => fn(HangingSign $v) => self::$_mACACIA_HANGING_SIGN = $v,
			"acacia_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mACACIA_SIGN = $v,
			"air" => fn(Item $v) => self::$_mAIR = $v,
			"amethyst_shard" => fn(Item $v) => self::$_mAMETHYST_SHARD = $v,
			"apple" => fn(Apple $v) => self::$_mAPPLE = $v,
			"arrow" => fn(Arrow $v) => self::$_mARROW = $v,
			"baked_potato" => fn(BakedPotato $v) => self::$_mBAKED_POTATO = $v,
			"bamboo" => fn(Bamboo $v) => self::$_mBAMBOO = $v,
			"bamboo_hanging_sign" => fn(HangingSign $v) => self::$_mBAMBOO_HANGING_SIGN = $v,
			"bamboo_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mBAMBOO_SIGN = $v,
			"banner" => fn(Banner $v) => self::$_mBANNER = $v,
			"beetroot" => fn(Beetroot $v) => self::$_mBEETROOT = $v,
			"beetroot_seeds" => fn(BeetrootSeeds $v) => self::$_mBEETROOT_SEEDS = $v,
			"beetroot_soup" => fn(BeetrootSoup $v) => self::$_mBEETROOT_SOUP = $v,
			"birch_boat" => fn(Boat $v) => self::$_mBIRCH_BOAT = $v,
			"birch_hanging_sign" => fn(HangingSign $v) => self::$_mBIRCH_HANGING_SIGN = $v,
			"birch_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mBIRCH_SIGN = $v,
			"blaze_powder" => fn(Item $v) => self::$_mBLAZE_POWDER = $v,
			"blaze_rod" => fn(BlazeRod $v) => self::$_mBLAZE_ROD = $v,
			"bleach" => fn(Item $v) => self::$_mBLEACH = $v,
			"bone" => fn(Item $v) => self::$_mBONE = $v,
			"bone_meal" => fn(Fertilizer $v) => self::$_mBONE_MEAL = $v,
			"book" => fn(Book $v) => self::$_mBOOK = $v,
			"bow" => fn(Bow $v) => self::$_mBOW = $v,
			"bowl" => fn(Bowl $v) => self::$_mBOWL = $v,
			"bread" => fn(Bread $v) => self::$_mBREAD = $v,
			"brick" => fn(Item $v) => self::$_mBRICK = $v,
			"bucket" => fn(Bucket $v) => self::$_mBUCKET = $v,
			"carrot" => fn(Carrot $v) => self::$_mCARROT = $v,
			"chainmail_boots" => fn(Armor $v) => self::$_mCHAINMAIL_BOOTS = $v,
			"chainmail_chestplate" => fn(Armor $v) => self::$_mCHAINMAIL_CHESTPLATE = $v,
			"chainmail_helmet" => fn(Armor $v) => self::$_mCHAINMAIL_HELMET = $v,
			"chainmail_leggings" => fn(Armor $v) => self::$_mCHAINMAIL_LEGGINGS = $v,
			"charcoal" => fn(Coal $v) => self::$_mCHARCOAL = $v,
			"chemical_aluminium_oxide" => fn(Item $v) => self::$_mCHEMICAL_ALUMINIUM_OXIDE = $v,
			"chemical_ammonia" => fn(Item $v) => self::$_mCHEMICAL_AMMONIA = $v,
			"chemical_barium_sulphate" => fn(Item $v) => self::$_mCHEMICAL_BARIUM_SULPHATE = $v,
			"chemical_benzene" => fn(Item $v) => self::$_mCHEMICAL_BENZENE = $v,
			"chemical_boron_trioxide" => fn(Item $v) => self::$_mCHEMICAL_BORON_TRIOXIDE = $v,
			"chemical_calcium_bromide" => fn(Item $v) => self::$_mCHEMICAL_CALCIUM_BROMIDE = $v,
			"chemical_calcium_chloride" => fn(Item $v) => self::$_mCHEMICAL_CALCIUM_CHLORIDE = $v,
			"chemical_cerium_chloride" => fn(Item $v) => self::$_mCHEMICAL_CERIUM_CHLORIDE = $v,
			"chemical_charcoal" => fn(Item $v) => self::$_mCHEMICAL_CHARCOAL = $v,
			"chemical_crude_oil" => fn(Item $v) => self::$_mCHEMICAL_CRUDE_OIL = $v,
			"chemical_glue" => fn(Item $v) => self::$_mCHEMICAL_GLUE = $v,
			"chemical_hydrogen_peroxide" => fn(Item $v) => self::$_mCHEMICAL_HYDROGEN_PEROXIDE = $v,
			"chemical_hypochlorite" => fn(Item $v) => self::$_mCHEMICAL_HYPOCHLORITE = $v,
			"chemical_ink" => fn(Item $v) => self::$_mCHEMICAL_INK = $v,
			"chemical_iron_sulphide" => fn(Item $v) => self::$_mCHEMICAL_IRON_SULPHIDE = $v,
			"chemical_latex" => fn(Item $v) => self::$_mCHEMICAL_LATEX = $v,
			"chemical_lithium_hydride" => fn(Item $v) => self::$_mCHEMICAL_LITHIUM_HYDRIDE = $v,
			"chemical_luminol" => fn(Item $v) => self::$_mCHEMICAL_LUMINOL = $v,
			"chemical_magnesium_nitrate" => fn(Item $v) => self::$_mCHEMICAL_MAGNESIUM_NITRATE = $v,
			"chemical_magnesium_oxide" => fn(Item $v) => self::$_mCHEMICAL_MAGNESIUM_OXIDE = $v,
			"chemical_magnesium_salts" => fn(Item $v) => self::$_mCHEMICAL_MAGNESIUM_SALTS = $v,
			"chemical_mercuric_chloride" => fn(Item $v) => self::$_mCHEMICAL_MERCURIC_CHLORIDE = $v,
			"chemical_polyethylene" => fn(Item $v) => self::$_mCHEMICAL_POLYETHYLENE = $v,
			"chemical_potassium_chloride" => fn(Item $v) => self::$_mCHEMICAL_POTASSIUM_CHLORIDE = $v,
			"chemical_potassium_iodide" => fn(Item $v) => self::$_mCHEMICAL_POTASSIUM_IODIDE = $v,
			"chemical_rubbish" => fn(Item $v) => self::$_mCHEMICAL_RUBBISH = $v,
			"chemical_salt" => fn(Item $v) => self::$_mCHEMICAL_SALT = $v,
			"chemical_soap" => fn(Item $v) => self::$_mCHEMICAL_SOAP = $v,
			"chemical_sodium_acetate" => fn(Item $v) => self::$_mCHEMICAL_SODIUM_ACETATE = $v,
			"chemical_sodium_fluoride" => fn(Item $v) => self::$_mCHEMICAL_SODIUM_FLUORIDE = $v,
			"chemical_sodium_hydride" => fn(Item $v) => self::$_mCHEMICAL_SODIUM_HYDRIDE = $v,
			"chemical_sodium_hydroxide" => fn(Item $v) => self::$_mCHEMICAL_SODIUM_HYDROXIDE = $v,
			"chemical_sodium_hypochlorite" => fn(Item $v) => self::$_mCHEMICAL_SODIUM_HYPOCHLORITE = $v,
			"chemical_sodium_oxide" => fn(Item $v) => self::$_mCHEMICAL_SODIUM_OXIDE = $v,
			"chemical_sugar" => fn(Item $v) => self::$_mCHEMICAL_SUGAR = $v,
			"chemical_sulphate" => fn(Item $v) => self::$_mCHEMICAL_SULPHATE = $v,
			"chemical_tungsten_chloride" => fn(Item $v) => self::$_mCHEMICAL_TUNGSTEN_CHLORIDE = $v,
			"chemical_water" => fn(Item $v) => self::$_mCHEMICAL_WATER = $v,
			"cherry_hanging_sign" => fn(HangingSign $v) => self::$_mCHERRY_HANGING_SIGN = $v,
			"cherry_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mCHERRY_SIGN = $v,
			"chorus_fruit" => fn(ChorusFruit $v) => self::$_mCHORUS_FRUIT = $v,
			"clay" => fn(Item $v) => self::$_mCLAY = $v,
			"clock" => fn(Clock $v) => self::$_mCLOCK = $v,
			"clownfish" => fn(Clownfish $v) => self::$_mCLOWNFISH = $v,
			"coal" => fn(Coal $v) => self::$_mCOAL = $v,
			"coast_armor_trim_smithing_template" => fn(Item $v) => self::$_mCOAST_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"cocoa_beans" => fn(CocoaBeans $v) => self::$_mCOCOA_BEANS = $v,
			"compass" => fn(Compass $v) => self::$_mCOMPASS = $v,
			"cooked_chicken" => fn(CookedChicken $v) => self::$_mCOOKED_CHICKEN = $v,
			"cooked_fish" => fn(CookedFish $v) => self::$_mCOOKED_FISH = $v,
			"cooked_mutton" => fn(CookedMutton $v) => self::$_mCOOKED_MUTTON = $v,
			"cooked_porkchop" => fn(CookedPorkchop $v) => self::$_mCOOKED_PORKCHOP = $v,
			"cooked_rabbit" => fn(CookedRabbit $v) => self::$_mCOOKED_RABBIT = $v,
			"cooked_salmon" => fn(CookedSalmon $v) => self::$_mCOOKED_SALMON = $v,
			"cookie" => fn(Cookie $v) => self::$_mCOOKIE = $v,
			"copper_axe" => fn(Axe $v) => self::$_mCOPPER_AXE = $v,
			"copper_boots" => fn(Armor $v) => self::$_mCOPPER_BOOTS = $v,
			"copper_chestplate" => fn(Armor $v) => self::$_mCOPPER_CHESTPLATE = $v,
			"copper_helmet" => fn(Armor $v) => self::$_mCOPPER_HELMET = $v,
			"copper_hoe" => fn(Hoe $v) => self::$_mCOPPER_HOE = $v,
			"copper_ingot" => fn(Item $v) => self::$_mCOPPER_INGOT = $v,
			"copper_leggings" => fn(Armor $v) => self::$_mCOPPER_LEGGINGS = $v,
			"copper_nugget" => fn(Item $v) => self::$_mCOPPER_NUGGET = $v,
			"copper_pickaxe" => fn(Pickaxe $v) => self::$_mCOPPER_PICKAXE = $v,
			"copper_shovel" => fn(Shovel $v) => self::$_mCOPPER_SHOVEL = $v,
			"copper_sword" => fn(Sword $v) => self::$_mCOPPER_SWORD = $v,
			"coral_fan" => fn(CoralFan $v) => self::$_mCORAL_FAN = $v,
			"crimson_hanging_sign" => fn(HangingSign $v) => self::$_mCRIMSON_HANGING_SIGN = $v,
			"crimson_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mCRIMSON_SIGN = $v,
			"dark_oak_boat" => fn(Boat $v) => self::$_mDARK_OAK_BOAT = $v,
			"dark_oak_hanging_sign" => fn(HangingSign $v) => self::$_mDARK_OAK_HANGING_SIGN = $v,
			"dark_oak_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mDARK_OAK_SIGN = $v,
			"diamond" => fn(Item $v) => self::$_mDIAMOND = $v,
			"diamond_axe" => fn(Axe $v) => self::$_mDIAMOND_AXE = $v,
			"diamond_boots" => fn(Armor $v) => self::$_mDIAMOND_BOOTS = $v,
			"diamond_chestplate" => fn(Armor $v) => self::$_mDIAMOND_CHESTPLATE = $v,
			"diamond_helmet" => fn(Armor $v) => self::$_mDIAMOND_HELMET = $v,
			"diamond_hoe" => fn(Hoe $v) => self::$_mDIAMOND_HOE = $v,
			"diamond_leggings" => fn(Armor $v) => self::$_mDIAMOND_LEGGINGS = $v,
			"diamond_pickaxe" => fn(Pickaxe $v) => self::$_mDIAMOND_PICKAXE = $v,
			"diamond_shovel" => fn(Shovel $v) => self::$_mDIAMOND_SHOVEL = $v,
			"diamond_sword" => fn(Sword $v) => self::$_mDIAMOND_SWORD = $v,
			"disc_fragment_5" => fn(Item $v) => self::$_mDISC_FRAGMENT_5 = $v,
			"dragon_breath" => fn(Item $v) => self::$_mDRAGON_BREATH = $v,
			"dried_kelp" => fn(DriedKelp $v) => self::$_mDRIED_KELP = $v,
			"dune_armor_trim_smithing_template" => fn(Item $v) => self::$_mDUNE_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"dye" => fn(Dye $v) => self::$_mDYE = $v,
			"echo_shard" => fn(Item $v) => self::$_mECHO_SHARD = $v,
			"egg" => fn(Egg $v) => self::$_mEGG = $v,
			"emerald" => fn(Item $v) => self::$_mEMERALD = $v,
			"enchanted_book" => fn(EnchantedBook $v) => self::$_mENCHANTED_BOOK = $v,
			"enchanted_golden_apple" => fn(GoldenAppleEnchanted $v) => self::$_mENCHANTED_GOLDEN_APPLE = $v,
			"ender_pearl" => fn(EnderPearl $v) => self::$_mENDER_PEARL = $v,
			"end_crystal" => fn(EndCrystal $v) => self::$_mEND_CRYSTAL = $v,
			"experience_bottle" => fn(ExperienceBottle $v) => self::$_mEXPERIENCE_BOTTLE = $v,
			"eye_armor_trim_smithing_template" => fn(Item $v) => self::$_mEYE_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"feather" => fn(Item $v) => self::$_mFEATHER = $v,
			"fermented_spider_eye" => fn(Item $v) => self::$_mFERMENTED_SPIDER_EYE = $v,
			"firework_rocket" => fn(FireworkRocket $v) => self::$_mFIREWORK_ROCKET = $v,
			"firework_star" => fn(FireworkStar $v) => self::$_mFIREWORK_STAR = $v,
			"fire_charge" => fn(FireCharge $v) => self::$_mFIRE_CHARGE = $v,
			"fishing_rod" => fn(FishingRod $v) => self::$_mFISHING_ROD = $v,
			"flint" => fn(Item $v) => self::$_mFLINT = $v,
			"flint_and_steel" => fn(FlintSteel $v) => self::$_mFLINT_AND_STEEL = $v,
			"ghast_tear" => fn(Item $v) => self::$_mGHAST_TEAR = $v,
			"glass_bottle" => fn(GlassBottle $v) => self::$_mGLASS_BOTTLE = $v,
			"glistering_melon" => fn(Item $v) => self::$_mGLISTERING_MELON = $v,
			"glowstone_dust" => fn(Item $v) => self::$_mGLOWSTONE_DUST = $v,
			"glow_berries" => fn(GlowBerries $v) => self::$_mGLOW_BERRIES = $v,
			"glow_ink_sac" => fn(Item $v) => self::$_mGLOW_INK_SAC = $v,
			"goat_horn" => fn(GoatHorn $v) => self::$_mGOAT_HORN = $v,
			"golden_apple" => fn(GoldenApple $v) => self::$_mGOLDEN_APPLE = $v,
			"golden_axe" => fn(Axe $v) => self::$_mGOLDEN_AXE = $v,
			"golden_boots" => fn(Armor $v) => self::$_mGOLDEN_BOOTS = $v,
			"golden_carrot" => fn(GoldenCarrot $v) => self::$_mGOLDEN_CARROT = $v,
			"golden_chestplate" => fn(Armor $v) => self::$_mGOLDEN_CHESTPLATE = $v,
			"golden_helmet" => fn(Armor $v) => self::$_mGOLDEN_HELMET = $v,
			"golden_hoe" => fn(Hoe $v) => self::$_mGOLDEN_HOE = $v,
			"golden_leggings" => fn(Armor $v) => self::$_mGOLDEN_LEGGINGS = $v,
			"golden_pickaxe" => fn(Pickaxe $v) => self::$_mGOLDEN_PICKAXE = $v,
			"golden_shovel" => fn(Shovel $v) => self::$_mGOLDEN_SHOVEL = $v,
			"golden_sword" => fn(Sword $v) => self::$_mGOLDEN_SWORD = $v,
			"gold_ingot" => fn(Item $v) => self::$_mGOLD_INGOT = $v,
			"gold_nugget" => fn(Item $v) => self::$_mGOLD_NUGGET = $v,
			"gunpowder" => fn(Item $v) => self::$_mGUNPOWDER = $v,
			"heart_of_the_sea" => fn(Item $v) => self::$_mHEART_OF_THE_SEA = $v,
			"honeycomb" => fn(Item $v) => self::$_mHONEYCOMB = $v,
			"honey_bottle" => fn(HoneyBottle $v) => self::$_mHONEY_BOTTLE = $v,
			"host_armor_trim_smithing_template" => fn(Item $v) => self::$_mHOST_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"ice_bomb" => fn(IceBomb $v) => self::$_mICE_BOMB = $v,
			"ink_sac" => fn(Item $v) => self::$_mINK_SAC = $v,
			"iron_axe" => fn(Axe $v) => self::$_mIRON_AXE = $v,
			"iron_boots" => fn(Armor $v) => self::$_mIRON_BOOTS = $v,
			"iron_chestplate" => fn(Armor $v) => self::$_mIRON_CHESTPLATE = $v,
			"iron_helmet" => fn(Armor $v) => self::$_mIRON_HELMET = $v,
			"iron_hoe" => fn(Hoe $v) => self::$_mIRON_HOE = $v,
			"iron_ingot" => fn(Item $v) => self::$_mIRON_INGOT = $v,
			"iron_leggings" => fn(Armor $v) => self::$_mIRON_LEGGINGS = $v,
			"iron_nugget" => fn(Item $v) => self::$_mIRON_NUGGET = $v,
			"iron_pickaxe" => fn(Pickaxe $v) => self::$_mIRON_PICKAXE = $v,
			"iron_shovel" => fn(Shovel $v) => self::$_mIRON_SHOVEL = $v,
			"iron_sword" => fn(Sword $v) => self::$_mIRON_SWORD = $v,
			"jungle_boat" => fn(Boat $v) => self::$_mJUNGLE_BOAT = $v,
			"jungle_hanging_sign" => fn(HangingSign $v) => self::$_mJUNGLE_HANGING_SIGN = $v,
			"jungle_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mJUNGLE_SIGN = $v,
			"lapis_lazuli" => fn(Item $v) => self::$_mLAPIS_LAZULI = $v,
			"lava_bucket" => fn(LiquidBucket $v) => self::$_mLAVA_BUCKET = $v,
			"leather" => fn(Item $v) => self::$_mLEATHER = $v,
			"leather_boots" => fn(Armor $v) => self::$_mLEATHER_BOOTS = $v,
			"leather_cap" => fn(Armor $v) => self::$_mLEATHER_CAP = $v,
			"leather_pants" => fn(Armor $v) => self::$_mLEATHER_PANTS = $v,
			"leather_tunic" => fn(Armor $v) => self::$_mLEATHER_TUNIC = $v,
			"lingering_potion" => fn(SplashPotion $v) => self::$_mLINGERING_POTION = $v,
			"magma_cream" => fn(Item $v) => self::$_mMAGMA_CREAM = $v,
			"mangrove_boat" => fn(Boat $v) => self::$_mMANGROVE_BOAT = $v,
			"mangrove_hanging_sign" => fn(HangingSign $v) => self::$_mMANGROVE_HANGING_SIGN = $v,
			"mangrove_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mMANGROVE_SIGN = $v,
			"medicine" => fn(Medicine $v) => self::$_mMEDICINE = $v,
			"melon" => fn(Melon $v) => self::$_mMELON = $v,
			"melon_seeds" => fn(MelonSeeds $v) => self::$_mMELON_SEEDS = $v,
			"milk_bucket" => fn(MilkBucket $v) => self::$_mMILK_BUCKET = $v,
			"minecart" => fn(Minecart $v) => self::$_mMINECART = $v,
			"mushroom_stew" => fn(MushroomStew $v) => self::$_mMUSHROOM_STEW = $v,
			"name_tag" => fn(NameTag $v) => self::$_mNAME_TAG = $v,
			"nautilus_shell" => fn(Item $v) => self::$_mNAUTILUS_SHELL = $v,
			"netherite_axe" => fn(Axe $v) => self::$_mNETHERITE_AXE = $v,
			"netherite_boots" => fn(Armor $v) => self::$_mNETHERITE_BOOTS = $v,
			"netherite_chestplate" => fn(Armor $v) => self::$_mNETHERITE_CHESTPLATE = $v,
			"netherite_helmet" => fn(Armor $v) => self::$_mNETHERITE_HELMET = $v,
			"netherite_hoe" => fn(Hoe $v) => self::$_mNETHERITE_HOE = $v,
			"netherite_ingot" => fn(Item $v) => self::$_mNETHERITE_INGOT = $v,
			"netherite_leggings" => fn(Armor $v) => self::$_mNETHERITE_LEGGINGS = $v,
			"netherite_pickaxe" => fn(Pickaxe $v) => self::$_mNETHERITE_PICKAXE = $v,
			"netherite_scrap" => fn(Item $v) => self::$_mNETHERITE_SCRAP = $v,
			"netherite_shovel" => fn(Shovel $v) => self::$_mNETHERITE_SHOVEL = $v,
			"netherite_sword" => fn(Sword $v) => self::$_mNETHERITE_SWORD = $v,
			"netherite_upgrade_smithing_template" => fn(Item $v) => self::$_mNETHERITE_UPGRADE_SMITHING_TEMPLATE = $v,
			"nether_brick" => fn(Item $v) => self::$_mNETHER_BRICK = $v,
			"nether_quartz" => fn(Item $v) => self::$_mNETHER_QUARTZ = $v,
			"nether_star" => fn(Item $v) => self::$_mNETHER_STAR = $v,
			"oak_boat" => fn(Boat $v) => self::$_mOAK_BOAT = $v,
			"oak_hanging_sign" => fn(HangingSign $v) => self::$_mOAK_HANGING_SIGN = $v,
			"oak_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mOAK_SIGN = $v,
			"ominous_banner" => fn(ItemBlockWallOrFloor $v) => self::$_mOMINOUS_BANNER = $v,
			"painting" => fn(PaintingItem $v) => self::$_mPAINTING = $v,
			"pale_oak_hanging_sign" => fn(HangingSign $v) => self::$_mPALE_OAK_HANGING_SIGN = $v,
			"pale_oak_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mPALE_OAK_SIGN = $v,
			"paper" => fn(Item $v) => self::$_mPAPER = $v,
			"phantom_membrane" => fn(Item $v) => self::$_mPHANTOM_MEMBRANE = $v,
			"pitcher_pod" => fn(PitcherPod $v) => self::$_mPITCHER_POD = $v,
			"poisonous_potato" => fn(PoisonousPotato $v) => self::$_mPOISONOUS_POTATO = $v,
			"popped_chorus_fruit" => fn(Item $v) => self::$_mPOPPED_CHORUS_FRUIT = $v,
			"potato" => fn(Potato $v) => self::$_mPOTATO = $v,
			"potion" => fn(Potion $v) => self::$_mPOTION = $v,
			"prismarine_crystals" => fn(Item $v) => self::$_mPRISMARINE_CRYSTALS = $v,
			"prismarine_shard" => fn(Item $v) => self::$_mPRISMARINE_SHARD = $v,
			"pufferfish" => fn(Pufferfish $v) => self::$_mPUFFERFISH = $v,
			"pumpkin_pie" => fn(PumpkinPie $v) => self::$_mPUMPKIN_PIE = $v,
			"pumpkin_seeds" => fn(PumpkinSeeds $v) => self::$_mPUMPKIN_SEEDS = $v,
			"rabbit_foot" => fn(Item $v) => self::$_mRABBIT_FOOT = $v,
			"rabbit_hide" => fn(Item $v) => self::$_mRABBIT_HIDE = $v,
			"rabbit_stew" => fn(RabbitStew $v) => self::$_mRABBIT_STEW = $v,
			"raiser_armor_trim_smithing_template" => fn(Item $v) => self::$_mRAISER_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"raw_beef" => fn(RawBeef $v) => self::$_mRAW_BEEF = $v,
			"raw_chicken" => fn(RawChicken $v) => self::$_mRAW_CHICKEN = $v,
			"raw_copper" => fn(Item $v) => self::$_mRAW_COPPER = $v,
			"raw_fish" => fn(RawFish $v) => self::$_mRAW_FISH = $v,
			"raw_gold" => fn(Item $v) => self::$_mRAW_GOLD = $v,
			"raw_iron" => fn(Item $v) => self::$_mRAW_IRON = $v,
			"raw_mutton" => fn(RawMutton $v) => self::$_mRAW_MUTTON = $v,
			"raw_porkchop" => fn(RawPorkchop $v) => self::$_mRAW_PORKCHOP = $v,
			"raw_rabbit" => fn(RawRabbit $v) => self::$_mRAW_RABBIT = $v,
			"raw_salmon" => fn(RawSalmon $v) => self::$_mRAW_SALMON = $v,
			"record_11" => fn(Record $v) => self::$_mRECORD_11 = $v,
			"record_13" => fn(Record $v) => self::$_mRECORD_13 = $v,
			"record_5" => fn(Record $v) => self::$_mRECORD_5 = $v,
			"record_blocks" => fn(Record $v) => self::$_mRECORD_BLOCKS = $v,
			"record_cat" => fn(Record $v) => self::$_mRECORD_CAT = $v,
			"record_chirp" => fn(Record $v) => self::$_mRECORD_CHIRP = $v,
			"record_creator" => fn(Record $v) => self::$_mRECORD_CREATOR = $v,
			"record_creator_music_box" => fn(Record $v) => self::$_mRECORD_CREATOR_MUSIC_BOX = $v,
			"record_far" => fn(Record $v) => self::$_mRECORD_FAR = $v,
			"record_lava_chicken" => fn(Record $v) => self::$_mRECORD_LAVA_CHICKEN = $v,
			"record_mall" => fn(Record $v) => self::$_mRECORD_MALL = $v,
			"record_mellohi" => fn(Record $v) => self::$_mRECORD_MELLOHI = $v,
			"record_otherside" => fn(Record $v) => self::$_mRECORD_OTHERSIDE = $v,
			"record_pigstep" => fn(Record $v) => self::$_mRECORD_PIGSTEP = $v,
			"record_precipice" => fn(Record $v) => self::$_mRECORD_PRECIPICE = $v,
			"record_relic" => fn(Record $v) => self::$_mRECORD_RELIC = $v,
			"record_stal" => fn(Record $v) => self::$_mRECORD_STAL = $v,
			"record_strad" => fn(Record $v) => self::$_mRECORD_STRAD = $v,
			"record_wait" => fn(Record $v) => self::$_mRECORD_WAIT = $v,
			"record_ward" => fn(Record $v) => self::$_mRECORD_WARD = $v,
			"recovery_compass" => fn(Item $v) => self::$_mRECOVERY_COMPASS = $v,
			"redstone_dust" => fn(Redstone $v) => self::$_mREDSTONE_DUST = $v,
			"resin_brick" => fn(Item $v) => self::$_mRESIN_BRICK = $v,
			"rib_armor_trim_smithing_template" => fn(Item $v) => self::$_mRIB_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"rotten_flesh" => fn(RottenFlesh $v) => self::$_mROTTEN_FLESH = $v,
			"scute" => fn(Item $v) => self::$_mSCUTE = $v,
			"sentry_armor_trim_smithing_template" => fn(Item $v) => self::$_mSENTRY_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"shaper_armor_trim_smithing_template" => fn(Item $v) => self::$_mSHAPER_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"shears" => fn(Shears $v) => self::$_mSHEARS = $v,
			"shulker_shell" => fn(Item $v) => self::$_mSHULKER_SHELL = $v,
			"silence_armor_trim_smithing_template" => fn(Item $v) => self::$_mSILENCE_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"slimeball" => fn(Item $v) => self::$_mSLIMEBALL = $v,
			"snout_armor_trim_smithing_template" => fn(Item $v) => self::$_mSNOUT_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"snowball" => fn(Snowball $v) => self::$_mSNOWBALL = $v,
			"spider_eye" => fn(SpiderEye $v) => self::$_mSPIDER_EYE = $v,
			"spire_armor_trim_smithing_template" => fn(Item $v) => self::$_mSPIRE_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"splash_potion" => fn(SplashPotion $v) => self::$_mSPLASH_POTION = $v,
			"spruce_boat" => fn(Boat $v) => self::$_mSPRUCE_BOAT = $v,
			"spruce_hanging_sign" => fn(HangingSign $v) => self::$_mSPRUCE_HANGING_SIGN = $v,
			"spruce_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mSPRUCE_SIGN = $v,
			"spyglass" => fn(Spyglass $v) => self::$_mSPYGLASS = $v,
			"squid_spawn_egg" => fn(SpawnEgg $v) => self::$_mSQUID_SPAWN_EGG = $v,
			"steak" => fn(Steak $v) => self::$_mSTEAK = $v,
			"stick" => fn(Stick $v) => self::$_mSTICK = $v,
			"stone_axe" => fn(Axe $v) => self::$_mSTONE_AXE = $v,
			"stone_hoe" => fn(Hoe $v) => self::$_mSTONE_HOE = $v,
			"stone_pickaxe" => fn(Pickaxe $v) => self::$_mSTONE_PICKAXE = $v,
			"stone_shovel" => fn(Shovel $v) => self::$_mSTONE_SHOVEL = $v,
			"stone_sword" => fn(Sword $v) => self::$_mSTONE_SWORD = $v,
			"string" => fn(StringItem $v) => self::$_mSTRING = $v,
			"sugar" => fn(Item $v) => self::$_mSUGAR = $v,
			"suspicious_stew" => fn(SuspiciousStew $v) => self::$_mSUSPICIOUS_STEW = $v,
			"sweet_berries" => fn(SweetBerries $v) => self::$_mSWEET_BERRIES = $v,
			"tide_armor_trim_smithing_template" => fn(Item $v) => self::$_mTIDE_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"torchflower_seeds" => fn(TorchflowerSeeds $v) => self::$_mTORCHFLOWER_SEEDS = $v,
			"totem" => fn(Totem $v) => self::$_mTOTEM = $v,
			"trident" => fn(Trident $v) => self::$_mTRIDENT = $v,
			"turtle_helmet" => fn(TurtleHelmet $v) => self::$_mTURTLE_HELMET = $v,
			"vex_armor_trim_smithing_template" => fn(Item $v) => self::$_mVEX_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"villager_spawn_egg" => fn(SpawnEgg $v) => self::$_mVILLAGER_SPAWN_EGG = $v,
			"ward_armor_trim_smithing_template" => fn(Item $v) => self::$_mWARD_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"warped_hanging_sign" => fn(HangingSign $v) => self::$_mWARPED_HANGING_SIGN = $v,
			"warped_sign" => fn(ItemBlockWallOrFloor $v) => self::$_mWARPED_SIGN = $v,
			"water_bucket" => fn(LiquidBucket $v) => self::$_mWATER_BUCKET = $v,
			"wayfinder_armor_trim_smithing_template" => fn(Item $v) => self::$_mWAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"wheat" => fn(Item $v) => self::$_mWHEAT = $v,
			"wheat_seeds" => fn(WheatSeeds $v) => self::$_mWHEAT_SEEDS = $v,
			"wild_armor_trim_smithing_template" => fn(Item $v) => self::$_mWILD_ARMOR_TRIM_SMITHING_TEMPLATE = $v,
			"wooden_axe" => fn(Axe $v) => self::$_mWOODEN_AXE = $v,
			"wooden_hoe" => fn(Hoe $v) => self::$_mWOODEN_HOE = $v,
			"wooden_pickaxe" => fn(Pickaxe $v) => self::$_mWOODEN_PICKAXE = $v,
			"wooden_shovel" => fn(Shovel $v) => self::$_mWOODEN_SHOVEL = $v,
			"wooden_sword" => fn(Sword $v) => self::$_mWOODEN_SWORD = $v,
			"writable_book" => fn(WritableBook $v) => self::$_mWRITABLE_BOOK = $v,
			"written_book" => fn(WrittenBook $v) => self::$_mWRITTEN_BOOK = $v,
			"zombie_spawn_egg" => fn(SpawnEgg $v) => self::$_mZOMBIE_SPAWN_EGG = $v,
		];
	}

	private static function init() : void{
		//This nasty mess of closures allows us to suppress PHPStan type assignment errors in one place instead of
		//on every single assignment. This will only run one time on first init, so it's fine for performance.
		if(self::$initialized){
			throw new \LogicException("Circular dependency detected - use RegistrySource->registerDelayed() if the circular dependency can't be avoided");
		}
		self::$initialized = true;
		$assigners = self::getInitAssigners();
		$assigned = [];
		$source = new VanillaItemsInputs();
		foreach($source->getAllValues() as $name => $value){
			$assigner = $assigners[$name] ?? throw new \LogicException("Unexpected source registry member \"$name\" (code probably needs regenerating)");
			if(isset($assigned[$name])){
				//this should be prevented by RegistrySource, but it doesn't hurt to have some redundancy
				throw new \LogicException("Repeated registry source member \"$name\"");
			}
			self::$members[mb_strtoupper($name)] = $value;
			$assigned[$name] = true;
			unset($assigners[$name]);
			self::unsafeAssign($assigner, $value);
		}
		if(count($assigners) > 0){
			throw new \LogicException("Missing values for registry members (code probably needs regenerating): " . implode(", ", array_keys($assigners)));
		}
	}

	/**
	 * @return Item[]
	 * @phpstan-return array<string, Item>
	 */
	public static function getAll() : array{
		if(!isset(self::$members)){ self::init(); }
		return Utils::cloneObjectArray(self::$members);
	}

	public static function ACACIA_BOAT() : Boat{
		if(!isset(self::$_mACACIA_BOAT)){ self::init(); }
		return clone self::$_mACACIA_BOAT;
	}

	public static function ACACIA_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mACACIA_HANGING_SIGN)){ self::init(); }
		return clone self::$_mACACIA_HANGING_SIGN;
	}

	public static function ACACIA_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mACACIA_SIGN)){ self::init(); }
		return clone self::$_mACACIA_SIGN;
	}

	public static function AIR() : Item{
		if(!isset(self::$_mAIR)){ self::init(); }
		return clone self::$_mAIR;
	}

	public static function AMETHYST_SHARD() : Item{
		if(!isset(self::$_mAMETHYST_SHARD)){ self::init(); }
		return clone self::$_mAMETHYST_SHARD;
	}

	public static function APPLE() : Apple{
		if(!isset(self::$_mAPPLE)){ self::init(); }
		return clone self::$_mAPPLE;
	}

	public static function ARROW() : Arrow{
		if(!isset(self::$_mARROW)){ self::init(); }
		return clone self::$_mARROW;
	}

	public static function BAKED_POTATO() : BakedPotato{
		if(!isset(self::$_mBAKED_POTATO)){ self::init(); }
		return clone self::$_mBAKED_POTATO;
	}

	public static function BAMBOO() : Bamboo{
		if(!isset(self::$_mBAMBOO)){ self::init(); }
		return clone self::$_mBAMBOO;
	}

	public static function BAMBOO_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mBAMBOO_HANGING_SIGN)){ self::init(); }
		return clone self::$_mBAMBOO_HANGING_SIGN;
	}

	public static function BAMBOO_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mBAMBOO_SIGN)){ self::init(); }
		return clone self::$_mBAMBOO_SIGN;
	}

	public static function BANNER() : Banner{
		if(!isset(self::$_mBANNER)){ self::init(); }
		return clone self::$_mBANNER;
	}

	public static function BEETROOT() : Beetroot{
		if(!isset(self::$_mBEETROOT)){ self::init(); }
		return clone self::$_mBEETROOT;
	}

	public static function BEETROOT_SEEDS() : BeetrootSeeds{
		if(!isset(self::$_mBEETROOT_SEEDS)){ self::init(); }
		return clone self::$_mBEETROOT_SEEDS;
	}

	public static function BEETROOT_SOUP() : BeetrootSoup{
		if(!isset(self::$_mBEETROOT_SOUP)){ self::init(); }
		return clone self::$_mBEETROOT_SOUP;
	}

	public static function BIRCH_BOAT() : Boat{
		if(!isset(self::$_mBIRCH_BOAT)){ self::init(); }
		return clone self::$_mBIRCH_BOAT;
	}

	public static function BIRCH_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mBIRCH_HANGING_SIGN)){ self::init(); }
		return clone self::$_mBIRCH_HANGING_SIGN;
	}

	public static function BIRCH_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mBIRCH_SIGN)){ self::init(); }
		return clone self::$_mBIRCH_SIGN;
	}

	public static function BLAZE_POWDER() : Item{
		if(!isset(self::$_mBLAZE_POWDER)){ self::init(); }
		return clone self::$_mBLAZE_POWDER;
	}

	public static function BLAZE_ROD() : BlazeRod{
		if(!isset(self::$_mBLAZE_ROD)){ self::init(); }
		return clone self::$_mBLAZE_ROD;
	}

	public static function BLEACH() : Item{
		if(!isset(self::$_mBLEACH)){ self::init(); }
		return clone self::$_mBLEACH;
	}

	public static function BONE() : Item{
		if(!isset(self::$_mBONE)){ self::init(); }
		return clone self::$_mBONE;
	}

	public static function BONE_MEAL() : Fertilizer{
		if(!isset(self::$_mBONE_MEAL)){ self::init(); }
		return clone self::$_mBONE_MEAL;
	}

	public static function BOOK() : Book{
		if(!isset(self::$_mBOOK)){ self::init(); }
		return clone self::$_mBOOK;
	}

	public static function BOW() : Bow{
		if(!isset(self::$_mBOW)){ self::init(); }
		return clone self::$_mBOW;
	}

	public static function BOWL() : Bowl{
		if(!isset(self::$_mBOWL)){ self::init(); }
		return clone self::$_mBOWL;
	}

	public static function BREAD() : Bread{
		if(!isset(self::$_mBREAD)){ self::init(); }
		return clone self::$_mBREAD;
	}

	public static function BRICK() : Item{
		if(!isset(self::$_mBRICK)){ self::init(); }
		return clone self::$_mBRICK;
	}

	public static function BUCKET() : Bucket{
		if(!isset(self::$_mBUCKET)){ self::init(); }
		return clone self::$_mBUCKET;
	}

	public static function CARROT() : Carrot{
		if(!isset(self::$_mCARROT)){ self::init(); }
		return clone self::$_mCARROT;
	}

	public static function CHAINMAIL_BOOTS() : Armor{
		if(!isset(self::$_mCHAINMAIL_BOOTS)){ self::init(); }
		return clone self::$_mCHAINMAIL_BOOTS;
	}

	public static function CHAINMAIL_CHESTPLATE() : Armor{
		if(!isset(self::$_mCHAINMAIL_CHESTPLATE)){ self::init(); }
		return clone self::$_mCHAINMAIL_CHESTPLATE;
	}

	public static function CHAINMAIL_HELMET() : Armor{
		if(!isset(self::$_mCHAINMAIL_HELMET)){ self::init(); }
		return clone self::$_mCHAINMAIL_HELMET;
	}

	public static function CHAINMAIL_LEGGINGS() : Armor{
		if(!isset(self::$_mCHAINMAIL_LEGGINGS)){ self::init(); }
		return clone self::$_mCHAINMAIL_LEGGINGS;
	}

	public static function CHARCOAL() : Coal{
		if(!isset(self::$_mCHARCOAL)){ self::init(); }
		return clone self::$_mCHARCOAL;
	}

	public static function CHEMICAL_ALUMINIUM_OXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_ALUMINIUM_OXIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_ALUMINIUM_OXIDE;
	}

	public static function CHEMICAL_AMMONIA() : Item{
		if(!isset(self::$_mCHEMICAL_AMMONIA)){ self::init(); }
		return clone self::$_mCHEMICAL_AMMONIA;
	}

	public static function CHEMICAL_BARIUM_SULPHATE() : Item{
		if(!isset(self::$_mCHEMICAL_BARIUM_SULPHATE)){ self::init(); }
		return clone self::$_mCHEMICAL_BARIUM_SULPHATE;
	}

	public static function CHEMICAL_BENZENE() : Item{
		if(!isset(self::$_mCHEMICAL_BENZENE)){ self::init(); }
		return clone self::$_mCHEMICAL_BENZENE;
	}

	public static function CHEMICAL_BORON_TRIOXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_BORON_TRIOXIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_BORON_TRIOXIDE;
	}

	public static function CHEMICAL_CALCIUM_BROMIDE() : Item{
		if(!isset(self::$_mCHEMICAL_CALCIUM_BROMIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_CALCIUM_BROMIDE;
	}

	public static function CHEMICAL_CALCIUM_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_CALCIUM_CHLORIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_CALCIUM_CHLORIDE;
	}

	public static function CHEMICAL_CERIUM_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_CERIUM_CHLORIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_CERIUM_CHLORIDE;
	}

	public static function CHEMICAL_CHARCOAL() : Item{
		if(!isset(self::$_mCHEMICAL_CHARCOAL)){ self::init(); }
		return clone self::$_mCHEMICAL_CHARCOAL;
	}

	public static function CHEMICAL_CRUDE_OIL() : Item{
		if(!isset(self::$_mCHEMICAL_CRUDE_OIL)){ self::init(); }
		return clone self::$_mCHEMICAL_CRUDE_OIL;
	}

	public static function CHEMICAL_GLUE() : Item{
		if(!isset(self::$_mCHEMICAL_GLUE)){ self::init(); }
		return clone self::$_mCHEMICAL_GLUE;
	}

	public static function CHEMICAL_HYDROGEN_PEROXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_HYDROGEN_PEROXIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_HYDROGEN_PEROXIDE;
	}

	public static function CHEMICAL_HYPOCHLORITE() : Item{
		if(!isset(self::$_mCHEMICAL_HYPOCHLORITE)){ self::init(); }
		return clone self::$_mCHEMICAL_HYPOCHLORITE;
	}

	public static function CHEMICAL_INK() : Item{
		if(!isset(self::$_mCHEMICAL_INK)){ self::init(); }
		return clone self::$_mCHEMICAL_INK;
	}

	public static function CHEMICAL_IRON_SULPHIDE() : Item{
		if(!isset(self::$_mCHEMICAL_IRON_SULPHIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_IRON_SULPHIDE;
	}

	public static function CHEMICAL_LATEX() : Item{
		if(!isset(self::$_mCHEMICAL_LATEX)){ self::init(); }
		return clone self::$_mCHEMICAL_LATEX;
	}

	public static function CHEMICAL_LITHIUM_HYDRIDE() : Item{
		if(!isset(self::$_mCHEMICAL_LITHIUM_HYDRIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_LITHIUM_HYDRIDE;
	}

	public static function CHEMICAL_LUMINOL() : Item{
		if(!isset(self::$_mCHEMICAL_LUMINOL)){ self::init(); }
		return clone self::$_mCHEMICAL_LUMINOL;
	}

	public static function CHEMICAL_MAGNESIUM_NITRATE() : Item{
		if(!isset(self::$_mCHEMICAL_MAGNESIUM_NITRATE)){ self::init(); }
		return clone self::$_mCHEMICAL_MAGNESIUM_NITRATE;
	}

	public static function CHEMICAL_MAGNESIUM_OXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_MAGNESIUM_OXIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_MAGNESIUM_OXIDE;
	}

	public static function CHEMICAL_MAGNESIUM_SALTS() : Item{
		if(!isset(self::$_mCHEMICAL_MAGNESIUM_SALTS)){ self::init(); }
		return clone self::$_mCHEMICAL_MAGNESIUM_SALTS;
	}

	public static function CHEMICAL_MERCURIC_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_MERCURIC_CHLORIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_MERCURIC_CHLORIDE;
	}

	public static function CHEMICAL_POLYETHYLENE() : Item{
		if(!isset(self::$_mCHEMICAL_POLYETHYLENE)){ self::init(); }
		return clone self::$_mCHEMICAL_POLYETHYLENE;
	}

	public static function CHEMICAL_POTASSIUM_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_POTASSIUM_CHLORIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_POTASSIUM_CHLORIDE;
	}

	public static function CHEMICAL_POTASSIUM_IODIDE() : Item{
		if(!isset(self::$_mCHEMICAL_POTASSIUM_IODIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_POTASSIUM_IODIDE;
	}

	public static function CHEMICAL_RUBBISH() : Item{
		if(!isset(self::$_mCHEMICAL_RUBBISH)){ self::init(); }
		return clone self::$_mCHEMICAL_RUBBISH;
	}

	public static function CHEMICAL_SALT() : Item{
		if(!isset(self::$_mCHEMICAL_SALT)){ self::init(); }
		return clone self::$_mCHEMICAL_SALT;
	}

	public static function CHEMICAL_SOAP() : Item{
		if(!isset(self::$_mCHEMICAL_SOAP)){ self::init(); }
		return clone self::$_mCHEMICAL_SOAP;
	}

	public static function CHEMICAL_SODIUM_ACETATE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_ACETATE)){ self::init(); }
		return clone self::$_mCHEMICAL_SODIUM_ACETATE;
	}

	public static function CHEMICAL_SODIUM_FLUORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_FLUORIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_SODIUM_FLUORIDE;
	}

	public static function CHEMICAL_SODIUM_HYDRIDE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_HYDRIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_SODIUM_HYDRIDE;
	}

	public static function CHEMICAL_SODIUM_HYDROXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_HYDROXIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_SODIUM_HYDROXIDE;
	}

	public static function CHEMICAL_SODIUM_HYPOCHLORITE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_HYPOCHLORITE)){ self::init(); }
		return clone self::$_mCHEMICAL_SODIUM_HYPOCHLORITE;
	}

	public static function CHEMICAL_SODIUM_OXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_OXIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_SODIUM_OXIDE;
	}

	public static function CHEMICAL_SUGAR() : Item{
		if(!isset(self::$_mCHEMICAL_SUGAR)){ self::init(); }
		return clone self::$_mCHEMICAL_SUGAR;
	}

	public static function CHEMICAL_SULPHATE() : Item{
		if(!isset(self::$_mCHEMICAL_SULPHATE)){ self::init(); }
		return clone self::$_mCHEMICAL_SULPHATE;
	}

	public static function CHEMICAL_TUNGSTEN_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_TUNGSTEN_CHLORIDE)){ self::init(); }
		return clone self::$_mCHEMICAL_TUNGSTEN_CHLORIDE;
	}

	public static function CHEMICAL_WATER() : Item{
		if(!isset(self::$_mCHEMICAL_WATER)){ self::init(); }
		return clone self::$_mCHEMICAL_WATER;
	}

	public static function CHERRY_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mCHERRY_HANGING_SIGN)){ self::init(); }
		return clone self::$_mCHERRY_HANGING_SIGN;
	}

	public static function CHERRY_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mCHERRY_SIGN)){ self::init(); }
		return clone self::$_mCHERRY_SIGN;
	}

	public static function CHORUS_FRUIT() : ChorusFruit{
		if(!isset(self::$_mCHORUS_FRUIT)){ self::init(); }
		return clone self::$_mCHORUS_FRUIT;
	}

	public static function CLAY() : Item{
		if(!isset(self::$_mCLAY)){ self::init(); }
		return clone self::$_mCLAY;
	}

	public static function CLOCK() : Clock{
		if(!isset(self::$_mCLOCK)){ self::init(); }
		return clone self::$_mCLOCK;
	}

	public static function CLOWNFISH() : Clownfish{
		if(!isset(self::$_mCLOWNFISH)){ self::init(); }
		return clone self::$_mCLOWNFISH;
	}

	public static function COAL() : Coal{
		if(!isset(self::$_mCOAL)){ self::init(); }
		return clone self::$_mCOAL;
	}

	public static function COAST_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mCOAST_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mCOAST_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function COCOA_BEANS() : CocoaBeans{
		if(!isset(self::$_mCOCOA_BEANS)){ self::init(); }
		return clone self::$_mCOCOA_BEANS;
	}

	public static function COMPASS() : Compass{
		if(!isset(self::$_mCOMPASS)){ self::init(); }
		return clone self::$_mCOMPASS;
	}

	public static function COOKED_CHICKEN() : CookedChicken{
		if(!isset(self::$_mCOOKED_CHICKEN)){ self::init(); }
		return clone self::$_mCOOKED_CHICKEN;
	}

	public static function COOKED_FISH() : CookedFish{
		if(!isset(self::$_mCOOKED_FISH)){ self::init(); }
		return clone self::$_mCOOKED_FISH;
	}

	public static function COOKED_MUTTON() : CookedMutton{
		if(!isset(self::$_mCOOKED_MUTTON)){ self::init(); }
		return clone self::$_mCOOKED_MUTTON;
	}

	public static function COOKED_PORKCHOP() : CookedPorkchop{
		if(!isset(self::$_mCOOKED_PORKCHOP)){ self::init(); }
		return clone self::$_mCOOKED_PORKCHOP;
	}

	public static function COOKED_RABBIT() : CookedRabbit{
		if(!isset(self::$_mCOOKED_RABBIT)){ self::init(); }
		return clone self::$_mCOOKED_RABBIT;
	}

	public static function COOKED_SALMON() : CookedSalmon{
		if(!isset(self::$_mCOOKED_SALMON)){ self::init(); }
		return clone self::$_mCOOKED_SALMON;
	}

	public static function COOKIE() : Cookie{
		if(!isset(self::$_mCOOKIE)){ self::init(); }
		return clone self::$_mCOOKIE;
	}

	public static function COPPER_AXE() : Axe{
		if(!isset(self::$_mCOPPER_AXE)){ self::init(); }
		return clone self::$_mCOPPER_AXE;
	}

	public static function COPPER_BOOTS() : Armor{
		if(!isset(self::$_mCOPPER_BOOTS)){ self::init(); }
		return clone self::$_mCOPPER_BOOTS;
	}

	public static function COPPER_CHESTPLATE() : Armor{
		if(!isset(self::$_mCOPPER_CHESTPLATE)){ self::init(); }
		return clone self::$_mCOPPER_CHESTPLATE;
	}

	public static function COPPER_HELMET() : Armor{
		if(!isset(self::$_mCOPPER_HELMET)){ self::init(); }
		return clone self::$_mCOPPER_HELMET;
	}

	public static function COPPER_HOE() : Hoe{
		if(!isset(self::$_mCOPPER_HOE)){ self::init(); }
		return clone self::$_mCOPPER_HOE;
	}

	public static function COPPER_INGOT() : Item{
		if(!isset(self::$_mCOPPER_INGOT)){ self::init(); }
		return clone self::$_mCOPPER_INGOT;
	}

	public static function COPPER_LEGGINGS() : Armor{
		if(!isset(self::$_mCOPPER_LEGGINGS)){ self::init(); }
		return clone self::$_mCOPPER_LEGGINGS;
	}

	public static function COPPER_NUGGET() : Item{
		if(!isset(self::$_mCOPPER_NUGGET)){ self::init(); }
		return clone self::$_mCOPPER_NUGGET;
	}

	public static function COPPER_PICKAXE() : Pickaxe{
		if(!isset(self::$_mCOPPER_PICKAXE)){ self::init(); }
		return clone self::$_mCOPPER_PICKAXE;
	}

	public static function COPPER_SHOVEL() : Shovel{
		if(!isset(self::$_mCOPPER_SHOVEL)){ self::init(); }
		return clone self::$_mCOPPER_SHOVEL;
	}

	public static function COPPER_SWORD() : Sword{
		if(!isset(self::$_mCOPPER_SWORD)){ self::init(); }
		return clone self::$_mCOPPER_SWORD;
	}

	public static function CORAL_FAN() : CoralFan{
		if(!isset(self::$_mCORAL_FAN)){ self::init(); }
		return clone self::$_mCORAL_FAN;
	}

	public static function CRIMSON_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mCRIMSON_HANGING_SIGN)){ self::init(); }
		return clone self::$_mCRIMSON_HANGING_SIGN;
	}

	public static function CRIMSON_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mCRIMSON_SIGN)){ self::init(); }
		return clone self::$_mCRIMSON_SIGN;
	}

	public static function DARK_OAK_BOAT() : Boat{
		if(!isset(self::$_mDARK_OAK_BOAT)){ self::init(); }
		return clone self::$_mDARK_OAK_BOAT;
	}

	public static function DARK_OAK_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mDARK_OAK_HANGING_SIGN)){ self::init(); }
		return clone self::$_mDARK_OAK_HANGING_SIGN;
	}

	public static function DARK_OAK_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mDARK_OAK_SIGN)){ self::init(); }
		return clone self::$_mDARK_OAK_SIGN;
	}

	public static function DIAMOND() : Item{
		if(!isset(self::$_mDIAMOND)){ self::init(); }
		return clone self::$_mDIAMOND;
	}

	public static function DIAMOND_AXE() : Axe{
		if(!isset(self::$_mDIAMOND_AXE)){ self::init(); }
		return clone self::$_mDIAMOND_AXE;
	}

	public static function DIAMOND_BOOTS() : Armor{
		if(!isset(self::$_mDIAMOND_BOOTS)){ self::init(); }
		return clone self::$_mDIAMOND_BOOTS;
	}

	public static function DIAMOND_CHESTPLATE() : Armor{
		if(!isset(self::$_mDIAMOND_CHESTPLATE)){ self::init(); }
		return clone self::$_mDIAMOND_CHESTPLATE;
	}

	public static function DIAMOND_HELMET() : Armor{
		if(!isset(self::$_mDIAMOND_HELMET)){ self::init(); }
		return clone self::$_mDIAMOND_HELMET;
	}

	public static function DIAMOND_HOE() : Hoe{
		if(!isset(self::$_mDIAMOND_HOE)){ self::init(); }
		return clone self::$_mDIAMOND_HOE;
	}

	public static function DIAMOND_LEGGINGS() : Armor{
		if(!isset(self::$_mDIAMOND_LEGGINGS)){ self::init(); }
		return clone self::$_mDIAMOND_LEGGINGS;
	}

	public static function DIAMOND_PICKAXE() : Pickaxe{
		if(!isset(self::$_mDIAMOND_PICKAXE)){ self::init(); }
		return clone self::$_mDIAMOND_PICKAXE;
	}

	public static function DIAMOND_SHOVEL() : Shovel{
		if(!isset(self::$_mDIAMOND_SHOVEL)){ self::init(); }
		return clone self::$_mDIAMOND_SHOVEL;
	}

	public static function DIAMOND_SWORD() : Sword{
		if(!isset(self::$_mDIAMOND_SWORD)){ self::init(); }
		return clone self::$_mDIAMOND_SWORD;
	}

	public static function DISC_FRAGMENT_5() : Item{
		if(!isset(self::$_mDISC_FRAGMENT_5)){ self::init(); }
		return clone self::$_mDISC_FRAGMENT_5;
	}

	public static function DRAGON_BREATH() : Item{
		if(!isset(self::$_mDRAGON_BREATH)){ self::init(); }
		return clone self::$_mDRAGON_BREATH;
	}

	public static function DRIED_KELP() : DriedKelp{
		if(!isset(self::$_mDRIED_KELP)){ self::init(); }
		return clone self::$_mDRIED_KELP;
	}

	public static function DUNE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mDUNE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mDUNE_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function DYE() : Dye{
		if(!isset(self::$_mDYE)){ self::init(); }
		return clone self::$_mDYE;
	}

	public static function ECHO_SHARD() : Item{
		if(!isset(self::$_mECHO_SHARD)){ self::init(); }
		return clone self::$_mECHO_SHARD;
	}

	public static function EGG() : Egg{
		if(!isset(self::$_mEGG)){ self::init(); }
		return clone self::$_mEGG;
	}

	public static function EMERALD() : Item{
		if(!isset(self::$_mEMERALD)){ self::init(); }
		return clone self::$_mEMERALD;
	}

	public static function ENCHANTED_BOOK() : EnchantedBook{
		if(!isset(self::$_mENCHANTED_BOOK)){ self::init(); }
		return clone self::$_mENCHANTED_BOOK;
	}

	public static function ENCHANTED_GOLDEN_APPLE() : GoldenAppleEnchanted{
		if(!isset(self::$_mENCHANTED_GOLDEN_APPLE)){ self::init(); }
		return clone self::$_mENCHANTED_GOLDEN_APPLE;
	}

	public static function ENDER_PEARL() : EnderPearl{
		if(!isset(self::$_mENDER_PEARL)){ self::init(); }
		return clone self::$_mENDER_PEARL;
	}

	public static function END_CRYSTAL() : EndCrystal{
		if(!isset(self::$_mEND_CRYSTAL)){ self::init(); }
		return clone self::$_mEND_CRYSTAL;
	}

	public static function EXPERIENCE_BOTTLE() : ExperienceBottle{
		if(!isset(self::$_mEXPERIENCE_BOTTLE)){ self::init(); }
		return clone self::$_mEXPERIENCE_BOTTLE;
	}

	public static function EYE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mEYE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mEYE_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function FEATHER() : Item{
		if(!isset(self::$_mFEATHER)){ self::init(); }
		return clone self::$_mFEATHER;
	}

	public static function FERMENTED_SPIDER_EYE() : Item{
		if(!isset(self::$_mFERMENTED_SPIDER_EYE)){ self::init(); }
		return clone self::$_mFERMENTED_SPIDER_EYE;
	}

	public static function FIREWORK_ROCKET() : FireworkRocket{
		if(!isset(self::$_mFIREWORK_ROCKET)){ self::init(); }
		return clone self::$_mFIREWORK_ROCKET;
	}

	public static function FIREWORK_STAR() : FireworkStar{
		if(!isset(self::$_mFIREWORK_STAR)){ self::init(); }
		return clone self::$_mFIREWORK_STAR;
	}

	public static function FIRE_CHARGE() : FireCharge{
		if(!isset(self::$_mFIRE_CHARGE)){ self::init(); }
		return clone self::$_mFIRE_CHARGE;
	}

	public static function FISHING_ROD() : FishingRod{
		if(!isset(self::$_mFISHING_ROD)){ self::init(); }
		return clone self::$_mFISHING_ROD;
	}

	public static function FLINT() : Item{
		if(!isset(self::$_mFLINT)){ self::init(); }
		return clone self::$_mFLINT;
	}

	public static function FLINT_AND_STEEL() : FlintSteel{
		if(!isset(self::$_mFLINT_AND_STEEL)){ self::init(); }
		return clone self::$_mFLINT_AND_STEEL;
	}

	public static function GHAST_TEAR() : Item{
		if(!isset(self::$_mGHAST_TEAR)){ self::init(); }
		return clone self::$_mGHAST_TEAR;
	}

	public static function GLASS_BOTTLE() : GlassBottle{
		if(!isset(self::$_mGLASS_BOTTLE)){ self::init(); }
		return clone self::$_mGLASS_BOTTLE;
	}

	public static function GLISTERING_MELON() : Item{
		if(!isset(self::$_mGLISTERING_MELON)){ self::init(); }
		return clone self::$_mGLISTERING_MELON;
	}

	public static function GLOWSTONE_DUST() : Item{
		if(!isset(self::$_mGLOWSTONE_DUST)){ self::init(); }
		return clone self::$_mGLOWSTONE_DUST;
	}

	public static function GLOW_BERRIES() : GlowBerries{
		if(!isset(self::$_mGLOW_BERRIES)){ self::init(); }
		return clone self::$_mGLOW_BERRIES;
	}

	public static function GLOW_INK_SAC() : Item{
		if(!isset(self::$_mGLOW_INK_SAC)){ self::init(); }
		return clone self::$_mGLOW_INK_SAC;
	}

	public static function GOAT_HORN() : GoatHorn{
		if(!isset(self::$_mGOAT_HORN)){ self::init(); }
		return clone self::$_mGOAT_HORN;
	}

	public static function GOLDEN_APPLE() : GoldenApple{
		if(!isset(self::$_mGOLDEN_APPLE)){ self::init(); }
		return clone self::$_mGOLDEN_APPLE;
	}

	public static function GOLDEN_AXE() : Axe{
		if(!isset(self::$_mGOLDEN_AXE)){ self::init(); }
		return clone self::$_mGOLDEN_AXE;
	}

	public static function GOLDEN_BOOTS() : Armor{
		if(!isset(self::$_mGOLDEN_BOOTS)){ self::init(); }
		return clone self::$_mGOLDEN_BOOTS;
	}

	public static function GOLDEN_CARROT() : GoldenCarrot{
		if(!isset(self::$_mGOLDEN_CARROT)){ self::init(); }
		return clone self::$_mGOLDEN_CARROT;
	}

	public static function GOLDEN_CHESTPLATE() : Armor{
		if(!isset(self::$_mGOLDEN_CHESTPLATE)){ self::init(); }
		return clone self::$_mGOLDEN_CHESTPLATE;
	}

	public static function GOLDEN_HELMET() : Armor{
		if(!isset(self::$_mGOLDEN_HELMET)){ self::init(); }
		return clone self::$_mGOLDEN_HELMET;
	}

	public static function GOLDEN_HOE() : Hoe{
		if(!isset(self::$_mGOLDEN_HOE)){ self::init(); }
		return clone self::$_mGOLDEN_HOE;
	}

	public static function GOLDEN_LEGGINGS() : Armor{
		if(!isset(self::$_mGOLDEN_LEGGINGS)){ self::init(); }
		return clone self::$_mGOLDEN_LEGGINGS;
	}

	public static function GOLDEN_PICKAXE() : Pickaxe{
		if(!isset(self::$_mGOLDEN_PICKAXE)){ self::init(); }
		return clone self::$_mGOLDEN_PICKAXE;
	}

	public static function GOLDEN_SHOVEL() : Shovel{
		if(!isset(self::$_mGOLDEN_SHOVEL)){ self::init(); }
		return clone self::$_mGOLDEN_SHOVEL;
	}

	public static function GOLDEN_SWORD() : Sword{
		if(!isset(self::$_mGOLDEN_SWORD)){ self::init(); }
		return clone self::$_mGOLDEN_SWORD;
	}

	public static function GOLD_INGOT() : Item{
		if(!isset(self::$_mGOLD_INGOT)){ self::init(); }
		return clone self::$_mGOLD_INGOT;
	}

	public static function GOLD_NUGGET() : Item{
		if(!isset(self::$_mGOLD_NUGGET)){ self::init(); }
		return clone self::$_mGOLD_NUGGET;
	}

	public static function GUNPOWDER() : Item{
		if(!isset(self::$_mGUNPOWDER)){ self::init(); }
		return clone self::$_mGUNPOWDER;
	}

	public static function HEART_OF_THE_SEA() : Item{
		if(!isset(self::$_mHEART_OF_THE_SEA)){ self::init(); }
		return clone self::$_mHEART_OF_THE_SEA;
	}

	public static function HONEYCOMB() : Item{
		if(!isset(self::$_mHONEYCOMB)){ self::init(); }
		return clone self::$_mHONEYCOMB;
	}

	public static function HONEY_BOTTLE() : HoneyBottle{
		if(!isset(self::$_mHONEY_BOTTLE)){ self::init(); }
		return clone self::$_mHONEY_BOTTLE;
	}

	public static function HOST_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mHOST_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mHOST_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function ICE_BOMB() : IceBomb{
		if(!isset(self::$_mICE_BOMB)){ self::init(); }
		return clone self::$_mICE_BOMB;
	}

	public static function INK_SAC() : Item{
		if(!isset(self::$_mINK_SAC)){ self::init(); }
		return clone self::$_mINK_SAC;
	}

	public static function IRON_AXE() : Axe{
		if(!isset(self::$_mIRON_AXE)){ self::init(); }
		return clone self::$_mIRON_AXE;
	}

	public static function IRON_BOOTS() : Armor{
		if(!isset(self::$_mIRON_BOOTS)){ self::init(); }
		return clone self::$_mIRON_BOOTS;
	}

	public static function IRON_CHESTPLATE() : Armor{
		if(!isset(self::$_mIRON_CHESTPLATE)){ self::init(); }
		return clone self::$_mIRON_CHESTPLATE;
	}

	public static function IRON_HELMET() : Armor{
		if(!isset(self::$_mIRON_HELMET)){ self::init(); }
		return clone self::$_mIRON_HELMET;
	}

	public static function IRON_HOE() : Hoe{
		if(!isset(self::$_mIRON_HOE)){ self::init(); }
		return clone self::$_mIRON_HOE;
	}

	public static function IRON_INGOT() : Item{
		if(!isset(self::$_mIRON_INGOT)){ self::init(); }
		return clone self::$_mIRON_INGOT;
	}

	public static function IRON_LEGGINGS() : Armor{
		if(!isset(self::$_mIRON_LEGGINGS)){ self::init(); }
		return clone self::$_mIRON_LEGGINGS;
	}

	public static function IRON_NUGGET() : Item{
		if(!isset(self::$_mIRON_NUGGET)){ self::init(); }
		return clone self::$_mIRON_NUGGET;
	}

	public static function IRON_PICKAXE() : Pickaxe{
		if(!isset(self::$_mIRON_PICKAXE)){ self::init(); }
		return clone self::$_mIRON_PICKAXE;
	}

	public static function IRON_SHOVEL() : Shovel{
		if(!isset(self::$_mIRON_SHOVEL)){ self::init(); }
		return clone self::$_mIRON_SHOVEL;
	}

	public static function IRON_SWORD() : Sword{
		if(!isset(self::$_mIRON_SWORD)){ self::init(); }
		return clone self::$_mIRON_SWORD;
	}

	public static function JUNGLE_BOAT() : Boat{
		if(!isset(self::$_mJUNGLE_BOAT)){ self::init(); }
		return clone self::$_mJUNGLE_BOAT;
	}

	public static function JUNGLE_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mJUNGLE_HANGING_SIGN)){ self::init(); }
		return clone self::$_mJUNGLE_HANGING_SIGN;
	}

	public static function JUNGLE_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mJUNGLE_SIGN)){ self::init(); }
		return clone self::$_mJUNGLE_SIGN;
	}

	public static function LAPIS_LAZULI() : Item{
		if(!isset(self::$_mLAPIS_LAZULI)){ self::init(); }
		return clone self::$_mLAPIS_LAZULI;
	}

	public static function LAVA_BUCKET() : LiquidBucket{
		if(!isset(self::$_mLAVA_BUCKET)){ self::init(); }
		return clone self::$_mLAVA_BUCKET;
	}

	public static function LEATHER() : Item{
		if(!isset(self::$_mLEATHER)){ self::init(); }
		return clone self::$_mLEATHER;
	}

	public static function LEATHER_BOOTS() : Armor{
		if(!isset(self::$_mLEATHER_BOOTS)){ self::init(); }
		return clone self::$_mLEATHER_BOOTS;
	}

	public static function LEATHER_CAP() : Armor{
		if(!isset(self::$_mLEATHER_CAP)){ self::init(); }
		return clone self::$_mLEATHER_CAP;
	}

	public static function LEATHER_PANTS() : Armor{
		if(!isset(self::$_mLEATHER_PANTS)){ self::init(); }
		return clone self::$_mLEATHER_PANTS;
	}

	public static function LEATHER_TUNIC() : Armor{
		if(!isset(self::$_mLEATHER_TUNIC)){ self::init(); }
		return clone self::$_mLEATHER_TUNIC;
	}

	public static function LINGERING_POTION() : SplashPotion{
		if(!isset(self::$_mLINGERING_POTION)){ self::init(); }
		return clone self::$_mLINGERING_POTION;
	}

	public static function MAGMA_CREAM() : Item{
		if(!isset(self::$_mMAGMA_CREAM)){ self::init(); }
		return clone self::$_mMAGMA_CREAM;
	}

	public static function MANGROVE_BOAT() : Boat{
		if(!isset(self::$_mMANGROVE_BOAT)){ self::init(); }
		return clone self::$_mMANGROVE_BOAT;
	}

	public static function MANGROVE_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mMANGROVE_HANGING_SIGN)){ self::init(); }
		return clone self::$_mMANGROVE_HANGING_SIGN;
	}

	public static function MANGROVE_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mMANGROVE_SIGN)){ self::init(); }
		return clone self::$_mMANGROVE_SIGN;
	}

	public static function MEDICINE() : Medicine{
		if(!isset(self::$_mMEDICINE)){ self::init(); }
		return clone self::$_mMEDICINE;
	}

	public static function MELON() : Melon{
		if(!isset(self::$_mMELON)){ self::init(); }
		return clone self::$_mMELON;
	}

	public static function MELON_SEEDS() : MelonSeeds{
		if(!isset(self::$_mMELON_SEEDS)){ self::init(); }
		return clone self::$_mMELON_SEEDS;
	}

	public static function MILK_BUCKET() : MilkBucket{
		if(!isset(self::$_mMILK_BUCKET)){ self::init(); }
		return clone self::$_mMILK_BUCKET;
	}

	public static function MINECART() : Minecart{
		if(!isset(self::$_mMINECART)){ self::init(); }
		return clone self::$_mMINECART;
	}

	public static function MUSHROOM_STEW() : MushroomStew{
		if(!isset(self::$_mMUSHROOM_STEW)){ self::init(); }
		return clone self::$_mMUSHROOM_STEW;
	}

	public static function NAME_TAG() : NameTag{
		if(!isset(self::$_mNAME_TAG)){ self::init(); }
		return clone self::$_mNAME_TAG;
	}

	public static function NAUTILUS_SHELL() : Item{
		if(!isset(self::$_mNAUTILUS_SHELL)){ self::init(); }
		return clone self::$_mNAUTILUS_SHELL;
	}

	public static function NETHERITE_AXE() : Axe{
		if(!isset(self::$_mNETHERITE_AXE)){ self::init(); }
		return clone self::$_mNETHERITE_AXE;
	}

	public static function NETHERITE_BOOTS() : Armor{
		if(!isset(self::$_mNETHERITE_BOOTS)){ self::init(); }
		return clone self::$_mNETHERITE_BOOTS;
	}

	public static function NETHERITE_CHESTPLATE() : Armor{
		if(!isset(self::$_mNETHERITE_CHESTPLATE)){ self::init(); }
		return clone self::$_mNETHERITE_CHESTPLATE;
	}

	public static function NETHERITE_HELMET() : Armor{
		if(!isset(self::$_mNETHERITE_HELMET)){ self::init(); }
		return clone self::$_mNETHERITE_HELMET;
	}

	public static function NETHERITE_HOE() : Hoe{
		if(!isset(self::$_mNETHERITE_HOE)){ self::init(); }
		return clone self::$_mNETHERITE_HOE;
	}

	public static function NETHERITE_INGOT() : Item{
		if(!isset(self::$_mNETHERITE_INGOT)){ self::init(); }
		return clone self::$_mNETHERITE_INGOT;
	}

	public static function NETHERITE_LEGGINGS() : Armor{
		if(!isset(self::$_mNETHERITE_LEGGINGS)){ self::init(); }
		return clone self::$_mNETHERITE_LEGGINGS;
	}

	public static function NETHERITE_PICKAXE() : Pickaxe{
		if(!isset(self::$_mNETHERITE_PICKAXE)){ self::init(); }
		return clone self::$_mNETHERITE_PICKAXE;
	}

	public static function NETHERITE_SCRAP() : Item{
		if(!isset(self::$_mNETHERITE_SCRAP)){ self::init(); }
		return clone self::$_mNETHERITE_SCRAP;
	}

	public static function NETHERITE_SHOVEL() : Shovel{
		if(!isset(self::$_mNETHERITE_SHOVEL)){ self::init(); }
		return clone self::$_mNETHERITE_SHOVEL;
	}

	public static function NETHERITE_SWORD() : Sword{
		if(!isset(self::$_mNETHERITE_SWORD)){ self::init(); }
		return clone self::$_mNETHERITE_SWORD;
	}

	public static function NETHERITE_UPGRADE_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mNETHERITE_UPGRADE_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mNETHERITE_UPGRADE_SMITHING_TEMPLATE;
	}

	public static function NETHER_BRICK() : Item{
		if(!isset(self::$_mNETHER_BRICK)){ self::init(); }
		return clone self::$_mNETHER_BRICK;
	}

	public static function NETHER_QUARTZ() : Item{
		if(!isset(self::$_mNETHER_QUARTZ)){ self::init(); }
		return clone self::$_mNETHER_QUARTZ;
	}

	public static function NETHER_STAR() : Item{
		if(!isset(self::$_mNETHER_STAR)){ self::init(); }
		return clone self::$_mNETHER_STAR;
	}

	public static function OAK_BOAT() : Boat{
		if(!isset(self::$_mOAK_BOAT)){ self::init(); }
		return clone self::$_mOAK_BOAT;
	}

	public static function OAK_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mOAK_HANGING_SIGN)){ self::init(); }
		return clone self::$_mOAK_HANGING_SIGN;
	}

	public static function OAK_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mOAK_SIGN)){ self::init(); }
		return clone self::$_mOAK_SIGN;
	}

	public static function OMINOUS_BANNER() : ItemBlockWallOrFloor{
		if(!isset(self::$_mOMINOUS_BANNER)){ self::init(); }
		return clone self::$_mOMINOUS_BANNER;
	}

	public static function PAINTING() : PaintingItem{
		if(!isset(self::$_mPAINTING)){ self::init(); }
		return clone self::$_mPAINTING;
	}

	public static function PALE_OAK_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mPALE_OAK_HANGING_SIGN)){ self::init(); }
		return clone self::$_mPALE_OAK_HANGING_SIGN;
	}

	public static function PALE_OAK_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mPALE_OAK_SIGN)){ self::init(); }
		return clone self::$_mPALE_OAK_SIGN;
	}

	public static function PAPER() : Item{
		if(!isset(self::$_mPAPER)){ self::init(); }
		return clone self::$_mPAPER;
	}

	public static function PHANTOM_MEMBRANE() : Item{
		if(!isset(self::$_mPHANTOM_MEMBRANE)){ self::init(); }
		return clone self::$_mPHANTOM_MEMBRANE;
	}

	public static function PITCHER_POD() : PitcherPod{
		if(!isset(self::$_mPITCHER_POD)){ self::init(); }
		return clone self::$_mPITCHER_POD;
	}

	public static function POISONOUS_POTATO() : PoisonousPotato{
		if(!isset(self::$_mPOISONOUS_POTATO)){ self::init(); }
		return clone self::$_mPOISONOUS_POTATO;
	}

	public static function POPPED_CHORUS_FRUIT() : Item{
		if(!isset(self::$_mPOPPED_CHORUS_FRUIT)){ self::init(); }
		return clone self::$_mPOPPED_CHORUS_FRUIT;
	}

	public static function POTATO() : Potato{
		if(!isset(self::$_mPOTATO)){ self::init(); }
		return clone self::$_mPOTATO;
	}

	public static function POTION() : Potion{
		if(!isset(self::$_mPOTION)){ self::init(); }
		return clone self::$_mPOTION;
	}

	public static function PRISMARINE_CRYSTALS() : Item{
		if(!isset(self::$_mPRISMARINE_CRYSTALS)){ self::init(); }
		return clone self::$_mPRISMARINE_CRYSTALS;
	}

	public static function PRISMARINE_SHARD() : Item{
		if(!isset(self::$_mPRISMARINE_SHARD)){ self::init(); }
		return clone self::$_mPRISMARINE_SHARD;
	}

	public static function PUFFERFISH() : Pufferfish{
		if(!isset(self::$_mPUFFERFISH)){ self::init(); }
		return clone self::$_mPUFFERFISH;
	}

	public static function PUMPKIN_PIE() : PumpkinPie{
		if(!isset(self::$_mPUMPKIN_PIE)){ self::init(); }
		return clone self::$_mPUMPKIN_PIE;
	}

	public static function PUMPKIN_SEEDS() : PumpkinSeeds{
		if(!isset(self::$_mPUMPKIN_SEEDS)){ self::init(); }
		return clone self::$_mPUMPKIN_SEEDS;
	}

	public static function RABBIT_FOOT() : Item{
		if(!isset(self::$_mRABBIT_FOOT)){ self::init(); }
		return clone self::$_mRABBIT_FOOT;
	}

	public static function RABBIT_HIDE() : Item{
		if(!isset(self::$_mRABBIT_HIDE)){ self::init(); }
		return clone self::$_mRABBIT_HIDE;
	}

	public static function RABBIT_STEW() : RabbitStew{
		if(!isset(self::$_mRABBIT_STEW)){ self::init(); }
		return clone self::$_mRABBIT_STEW;
	}

	public static function RAISER_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mRAISER_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mRAISER_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function RAW_BEEF() : RawBeef{
		if(!isset(self::$_mRAW_BEEF)){ self::init(); }
		return clone self::$_mRAW_BEEF;
	}

	public static function RAW_CHICKEN() : RawChicken{
		if(!isset(self::$_mRAW_CHICKEN)){ self::init(); }
		return clone self::$_mRAW_CHICKEN;
	}

	public static function RAW_COPPER() : Item{
		if(!isset(self::$_mRAW_COPPER)){ self::init(); }
		return clone self::$_mRAW_COPPER;
	}

	public static function RAW_FISH() : RawFish{
		if(!isset(self::$_mRAW_FISH)){ self::init(); }
		return clone self::$_mRAW_FISH;
	}

	public static function RAW_GOLD() : Item{
		if(!isset(self::$_mRAW_GOLD)){ self::init(); }
		return clone self::$_mRAW_GOLD;
	}

	public static function RAW_IRON() : Item{
		if(!isset(self::$_mRAW_IRON)){ self::init(); }
		return clone self::$_mRAW_IRON;
	}

	public static function RAW_MUTTON() : RawMutton{
		if(!isset(self::$_mRAW_MUTTON)){ self::init(); }
		return clone self::$_mRAW_MUTTON;
	}

	public static function RAW_PORKCHOP() : RawPorkchop{
		if(!isset(self::$_mRAW_PORKCHOP)){ self::init(); }
		return clone self::$_mRAW_PORKCHOP;
	}

	public static function RAW_RABBIT() : RawRabbit{
		if(!isset(self::$_mRAW_RABBIT)){ self::init(); }
		return clone self::$_mRAW_RABBIT;
	}

	public static function RAW_SALMON() : RawSalmon{
		if(!isset(self::$_mRAW_SALMON)){ self::init(); }
		return clone self::$_mRAW_SALMON;
	}

	public static function RECORD_11() : Record{
		if(!isset(self::$_mRECORD_11)){ self::init(); }
		return clone self::$_mRECORD_11;
	}

	public static function RECORD_13() : Record{
		if(!isset(self::$_mRECORD_13)){ self::init(); }
		return clone self::$_mRECORD_13;
	}

	public static function RECORD_5() : Record{
		if(!isset(self::$_mRECORD_5)){ self::init(); }
		return clone self::$_mRECORD_5;
	}

	public static function RECORD_BLOCKS() : Record{
		if(!isset(self::$_mRECORD_BLOCKS)){ self::init(); }
		return clone self::$_mRECORD_BLOCKS;
	}

	public static function RECORD_CAT() : Record{
		if(!isset(self::$_mRECORD_CAT)){ self::init(); }
		return clone self::$_mRECORD_CAT;
	}

	public static function RECORD_CHIRP() : Record{
		if(!isset(self::$_mRECORD_CHIRP)){ self::init(); }
		return clone self::$_mRECORD_CHIRP;
	}

	public static function RECORD_CREATOR() : Record{
		if(!isset(self::$_mRECORD_CREATOR)){ self::init(); }
		return clone self::$_mRECORD_CREATOR;
	}

	public static function RECORD_CREATOR_MUSIC_BOX() : Record{
		if(!isset(self::$_mRECORD_CREATOR_MUSIC_BOX)){ self::init(); }
		return clone self::$_mRECORD_CREATOR_MUSIC_BOX;
	}

	public static function RECORD_FAR() : Record{
		if(!isset(self::$_mRECORD_FAR)){ self::init(); }
		return clone self::$_mRECORD_FAR;
	}

	public static function RECORD_LAVA_CHICKEN() : Record{
		if(!isset(self::$_mRECORD_LAVA_CHICKEN)){ self::init(); }
		return clone self::$_mRECORD_LAVA_CHICKEN;
	}

	public static function RECORD_MALL() : Record{
		if(!isset(self::$_mRECORD_MALL)){ self::init(); }
		return clone self::$_mRECORD_MALL;
	}

	public static function RECORD_MELLOHI() : Record{
		if(!isset(self::$_mRECORD_MELLOHI)){ self::init(); }
		return clone self::$_mRECORD_MELLOHI;
	}

	public static function RECORD_OTHERSIDE() : Record{
		if(!isset(self::$_mRECORD_OTHERSIDE)){ self::init(); }
		return clone self::$_mRECORD_OTHERSIDE;
	}

	public static function RECORD_PIGSTEP() : Record{
		if(!isset(self::$_mRECORD_PIGSTEP)){ self::init(); }
		return clone self::$_mRECORD_PIGSTEP;
	}

	public static function RECORD_PRECIPICE() : Record{
		if(!isset(self::$_mRECORD_PRECIPICE)){ self::init(); }
		return clone self::$_mRECORD_PRECIPICE;
	}

	public static function RECORD_RELIC() : Record{
		if(!isset(self::$_mRECORD_RELIC)){ self::init(); }
		return clone self::$_mRECORD_RELIC;
	}

	public static function RECORD_STAL() : Record{
		if(!isset(self::$_mRECORD_STAL)){ self::init(); }
		return clone self::$_mRECORD_STAL;
	}

	public static function RECORD_STRAD() : Record{
		if(!isset(self::$_mRECORD_STRAD)){ self::init(); }
		return clone self::$_mRECORD_STRAD;
	}

	public static function RECORD_WAIT() : Record{
		if(!isset(self::$_mRECORD_WAIT)){ self::init(); }
		return clone self::$_mRECORD_WAIT;
	}

	public static function RECORD_WARD() : Record{
		if(!isset(self::$_mRECORD_WARD)){ self::init(); }
		return clone self::$_mRECORD_WARD;
	}

	public static function RECOVERY_COMPASS() : Item{
		if(!isset(self::$_mRECOVERY_COMPASS)){ self::init(); }
		return clone self::$_mRECOVERY_COMPASS;
	}

	public static function REDSTONE_DUST() : Redstone{
		if(!isset(self::$_mREDSTONE_DUST)){ self::init(); }
		return clone self::$_mREDSTONE_DUST;
	}

	public static function RESIN_BRICK() : Item{
		if(!isset(self::$_mRESIN_BRICK)){ self::init(); }
		return clone self::$_mRESIN_BRICK;
	}

	public static function RIB_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mRIB_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mRIB_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function ROTTEN_FLESH() : RottenFlesh{
		if(!isset(self::$_mROTTEN_FLESH)){ self::init(); }
		return clone self::$_mROTTEN_FLESH;
	}

	public static function SCUTE() : Item{
		if(!isset(self::$_mSCUTE)){ self::init(); }
		return clone self::$_mSCUTE;
	}

	public static function SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSENTRY_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mSENTRY_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSHAPER_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mSHAPER_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function SHEARS() : Shears{
		if(!isset(self::$_mSHEARS)){ self::init(); }
		return clone self::$_mSHEARS;
	}

	public static function SHULKER_SHELL() : Item{
		if(!isset(self::$_mSHULKER_SHELL)){ self::init(); }
		return clone self::$_mSHULKER_SHELL;
	}

	public static function SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSILENCE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mSILENCE_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function SLIMEBALL() : Item{
		if(!isset(self::$_mSLIMEBALL)){ self::init(); }
		return clone self::$_mSLIMEBALL;
	}

	public static function SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSNOUT_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mSNOUT_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function SNOWBALL() : Snowball{
		if(!isset(self::$_mSNOWBALL)){ self::init(); }
		return clone self::$_mSNOWBALL;
	}

	public static function SPIDER_EYE() : SpiderEye{
		if(!isset(self::$_mSPIDER_EYE)){ self::init(); }
		return clone self::$_mSPIDER_EYE;
	}

	public static function SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSPIRE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mSPIRE_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function SPLASH_POTION() : SplashPotion{
		if(!isset(self::$_mSPLASH_POTION)){ self::init(); }
		return clone self::$_mSPLASH_POTION;
	}

	public static function SPRUCE_BOAT() : Boat{
		if(!isset(self::$_mSPRUCE_BOAT)){ self::init(); }
		return clone self::$_mSPRUCE_BOAT;
	}

	public static function SPRUCE_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mSPRUCE_HANGING_SIGN)){ self::init(); }
		return clone self::$_mSPRUCE_HANGING_SIGN;
	}

	public static function SPRUCE_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mSPRUCE_SIGN)){ self::init(); }
		return clone self::$_mSPRUCE_SIGN;
	}

	public static function SPYGLASS() : Spyglass{
		if(!isset(self::$_mSPYGLASS)){ self::init(); }
		return clone self::$_mSPYGLASS;
	}

	public static function SQUID_SPAWN_EGG() : SpawnEgg{
		if(!isset(self::$_mSQUID_SPAWN_EGG)){ self::init(); }
		return clone self::$_mSQUID_SPAWN_EGG;
	}

	public static function STEAK() : Steak{
		if(!isset(self::$_mSTEAK)){ self::init(); }
		return clone self::$_mSTEAK;
	}

	public static function STICK() : Stick{
		if(!isset(self::$_mSTICK)){ self::init(); }
		return clone self::$_mSTICK;
	}

	public static function STONE_AXE() : Axe{
		if(!isset(self::$_mSTONE_AXE)){ self::init(); }
		return clone self::$_mSTONE_AXE;
	}

	public static function STONE_HOE() : Hoe{
		if(!isset(self::$_mSTONE_HOE)){ self::init(); }
		return clone self::$_mSTONE_HOE;
	}

	public static function STONE_PICKAXE() : Pickaxe{
		if(!isset(self::$_mSTONE_PICKAXE)){ self::init(); }
		return clone self::$_mSTONE_PICKAXE;
	}

	public static function STONE_SHOVEL() : Shovel{
		if(!isset(self::$_mSTONE_SHOVEL)){ self::init(); }
		return clone self::$_mSTONE_SHOVEL;
	}

	public static function STONE_SWORD() : Sword{
		if(!isset(self::$_mSTONE_SWORD)){ self::init(); }
		return clone self::$_mSTONE_SWORD;
	}

	public static function STRING() : StringItem{
		if(!isset(self::$_mSTRING)){ self::init(); }
		return clone self::$_mSTRING;
	}

	public static function SUGAR() : Item{
		if(!isset(self::$_mSUGAR)){ self::init(); }
		return clone self::$_mSUGAR;
	}

	public static function SUSPICIOUS_STEW() : SuspiciousStew{
		if(!isset(self::$_mSUSPICIOUS_STEW)){ self::init(); }
		return clone self::$_mSUSPICIOUS_STEW;
	}

	public static function SWEET_BERRIES() : SweetBerries{
		if(!isset(self::$_mSWEET_BERRIES)){ self::init(); }
		return clone self::$_mSWEET_BERRIES;
	}

	public static function TIDE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mTIDE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mTIDE_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function TORCHFLOWER_SEEDS() : TorchflowerSeeds{
		if(!isset(self::$_mTORCHFLOWER_SEEDS)){ self::init(); }
		return clone self::$_mTORCHFLOWER_SEEDS;
	}

	public static function TOTEM() : Totem{
		if(!isset(self::$_mTOTEM)){ self::init(); }
		return clone self::$_mTOTEM;
	}

	public static function TRIDENT() : Trident{
		if(!isset(self::$_mTRIDENT)){ self::init(); }
		return clone self::$_mTRIDENT;
	}

	public static function TURTLE_HELMET() : TurtleHelmet{
		if(!isset(self::$_mTURTLE_HELMET)){ self::init(); }
		return clone self::$_mTURTLE_HELMET;
	}

	public static function VEX_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mVEX_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mVEX_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function VILLAGER_SPAWN_EGG() : SpawnEgg{
		if(!isset(self::$_mVILLAGER_SPAWN_EGG)){ self::init(); }
		return clone self::$_mVILLAGER_SPAWN_EGG;
	}

	public static function WARD_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mWARD_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mWARD_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function WARPED_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mWARPED_HANGING_SIGN)){ self::init(); }
		return clone self::$_mWARPED_HANGING_SIGN;
	}

	public static function WARPED_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mWARPED_SIGN)){ self::init(); }
		return clone self::$_mWARPED_SIGN;
	}

	public static function WATER_BUCKET() : LiquidBucket{
		if(!isset(self::$_mWATER_BUCKET)){ self::init(); }
		return clone self::$_mWATER_BUCKET;
	}

	public static function WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mWAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mWAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function WHEAT() : Item{
		if(!isset(self::$_mWHEAT)){ self::init(); }
		return clone self::$_mWHEAT;
	}

	public static function WHEAT_SEEDS() : WheatSeeds{
		if(!isset(self::$_mWHEAT_SEEDS)){ self::init(); }
		return clone self::$_mWHEAT_SEEDS;
	}

	public static function WILD_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mWILD_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return clone self::$_mWILD_ARMOR_TRIM_SMITHING_TEMPLATE;
	}

	public static function WOODEN_AXE() : Axe{
		if(!isset(self::$_mWOODEN_AXE)){ self::init(); }
		return clone self::$_mWOODEN_AXE;
	}

	public static function WOODEN_HOE() : Hoe{
		if(!isset(self::$_mWOODEN_HOE)){ self::init(); }
		return clone self::$_mWOODEN_HOE;
	}

	public static function WOODEN_PICKAXE() : Pickaxe{
		if(!isset(self::$_mWOODEN_PICKAXE)){ self::init(); }
		return clone self::$_mWOODEN_PICKAXE;
	}

	public static function WOODEN_SHOVEL() : Shovel{
		if(!isset(self::$_mWOODEN_SHOVEL)){ self::init(); }
		return clone self::$_mWOODEN_SHOVEL;
	}

	public static function WOODEN_SWORD() : Sword{
		if(!isset(self::$_mWOODEN_SWORD)){ self::init(); }
		return clone self::$_mWOODEN_SWORD;
	}

	public static function WRITABLE_BOOK() : WritableBook{
		if(!isset(self::$_mWRITABLE_BOOK)){ self::init(); }
		return clone self::$_mWRITABLE_BOOK;
	}

	public static function WRITTEN_BOOK() : WrittenBook{
		if(!isset(self::$_mWRITTEN_BOOK)){ self::init(); }
		return clone self::$_mWRITTEN_BOOK;
	}

	public static function ZOMBIE_SPAWN_EGG() : SpawnEgg{
		if(!isset(self::$_mZOMBIE_SPAWN_EGG)){ self::init(); }
		return clone self::$_mZOMBIE_SPAWN_EGG;
	}
}
