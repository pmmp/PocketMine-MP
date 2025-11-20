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
use function array_map;
use function mb_strtoupper;

/**
 * This class is generated automatically from source class {@link VanillaItemsInputs}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/generate-registry-interface.php
 */
final class VanillaItems{
	private static Boat $_mACACIA_BOAT;
	private static HangingSign $_mACACIA_HANGING_SIGN;
	private static ItemBlockWallOrFloor $_mACACIA_SIGN;
	private static ItemBlock $_mAIR;
	private static Item $_mAMETHYST_SHARD;
	private static Apple $_mAPPLE;
	private static Arrow $_mARROW;
	private static BakedPotato $_mBAKED_POTATO;
	private static Bamboo $_mBAMBOO;
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
	private static Armor $_mCOPPER_BOOTS;
	private static Armor $_mCOPPER_CHESTPLATE;
	private static Armor $_mCOPPER_HELMET;
	private static Item $_mCOPPER_INGOT;
	private static Armor $_mCOPPER_LEGGINGS;
	private static Item $_mCOPPER_NUGGET;
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

	private function __construct(){
		//NOOP
	}

	/**
	 * Hack to allow ignoring PHPStan wrong type assignment error in one place instead of hundreds or thousands
	 * Assumes that the input value already matches the expected type. If not, a TypeError will be thrown on assignment.
	 *
	 * @phpstan-template TValue of Item
	 * @phpstan-param \Closure(TValue): TValue $closure
	 */
	private static function unsafeAssign(\Closure $closure, Item $memberValue) : void{
		/** @phpstan-var TValue $memberValue */
		$closure($memberValue);
	}

	private static function init() : void{
		//This nasty mess of closures allows us to suppress PHPStan type assignment errors in one place instead of
		//on every single assignment. This will only run one time on first init, so it's fine for performance.
		$values = VanillaItemsInputs::getAll();
		foreach(Utils::stringifyKeys($values) as $name => $value){
			self::$members[mb_strtoupper($name)] = $value;
		}

		self::unsafeAssign(fn(Boat $v) => self::$_mACACIA_BOAT = $v, $values["acacia_boat"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mACACIA_HANGING_SIGN = $v, $values["acacia_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mACACIA_SIGN = $v, $values["acacia_sign"]);
		self::unsafeAssign(fn(ItemBlock $v) => self::$_mAIR = $v, $values["air"]);
		self::unsafeAssign(fn(Item $v) => self::$_mAMETHYST_SHARD = $v, $values["amethyst_shard"]);
		self::unsafeAssign(fn(Apple $v) => self::$_mAPPLE = $v, $values["apple"]);
		self::unsafeAssign(fn(Arrow $v) => self::$_mARROW = $v, $values["arrow"]);
		self::unsafeAssign(fn(BakedPotato $v) => self::$_mBAKED_POTATO = $v, $values["baked_potato"]);
		self::unsafeAssign(fn(Bamboo $v) => self::$_mBAMBOO = $v, $values["bamboo"]);
		self::unsafeAssign(fn(Banner $v) => self::$_mBANNER = $v, $values["banner"]);
		self::unsafeAssign(fn(Beetroot $v) => self::$_mBEETROOT = $v, $values["beetroot"]);
		self::unsafeAssign(fn(BeetrootSeeds $v) => self::$_mBEETROOT_SEEDS = $v, $values["beetroot_seeds"]);
		self::unsafeAssign(fn(BeetrootSoup $v) => self::$_mBEETROOT_SOUP = $v, $values["beetroot_soup"]);
		self::unsafeAssign(fn(Boat $v) => self::$_mBIRCH_BOAT = $v, $values["birch_boat"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mBIRCH_HANGING_SIGN = $v, $values["birch_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mBIRCH_SIGN = $v, $values["birch_sign"]);
		self::unsafeAssign(fn(Item $v) => self::$_mBLAZE_POWDER = $v, $values["blaze_powder"]);
		self::unsafeAssign(fn(BlazeRod $v) => self::$_mBLAZE_ROD = $v, $values["blaze_rod"]);
		self::unsafeAssign(fn(Item $v) => self::$_mBLEACH = $v, $values["bleach"]);
		self::unsafeAssign(fn(Item $v) => self::$_mBONE = $v, $values["bone"]);
		self::unsafeAssign(fn(Fertilizer $v) => self::$_mBONE_MEAL = $v, $values["bone_meal"]);
		self::unsafeAssign(fn(Book $v) => self::$_mBOOK = $v, $values["book"]);
		self::unsafeAssign(fn(Bow $v) => self::$_mBOW = $v, $values["bow"]);
		self::unsafeAssign(fn(Bowl $v) => self::$_mBOWL = $v, $values["bowl"]);
		self::unsafeAssign(fn(Bread $v) => self::$_mBREAD = $v, $values["bread"]);
		self::unsafeAssign(fn(Item $v) => self::$_mBRICK = $v, $values["brick"]);
		self::unsafeAssign(fn(Bucket $v) => self::$_mBUCKET = $v, $values["bucket"]);
		self::unsafeAssign(fn(Carrot $v) => self::$_mCARROT = $v, $values["carrot"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mCHAINMAIL_BOOTS = $v, $values["chainmail_boots"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mCHAINMAIL_CHESTPLATE = $v, $values["chainmail_chestplate"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mCHAINMAIL_HELMET = $v, $values["chainmail_helmet"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mCHAINMAIL_LEGGINGS = $v, $values["chainmail_leggings"]);
		self::unsafeAssign(fn(Coal $v) => self::$_mCHARCOAL = $v, $values["charcoal"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_ALUMINIUM_OXIDE = $v, $values["chemical_aluminium_oxide"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_AMMONIA = $v, $values["chemical_ammonia"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_BARIUM_SULPHATE = $v, $values["chemical_barium_sulphate"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_BENZENE = $v, $values["chemical_benzene"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_BORON_TRIOXIDE = $v, $values["chemical_boron_trioxide"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_CALCIUM_BROMIDE = $v, $values["chemical_calcium_bromide"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_CALCIUM_CHLORIDE = $v, $values["chemical_calcium_chloride"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_CERIUM_CHLORIDE = $v, $values["chemical_cerium_chloride"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_CHARCOAL = $v, $values["chemical_charcoal"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_CRUDE_OIL = $v, $values["chemical_crude_oil"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_GLUE = $v, $values["chemical_glue"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_HYDROGEN_PEROXIDE = $v, $values["chemical_hydrogen_peroxide"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_HYPOCHLORITE = $v, $values["chemical_hypochlorite"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_INK = $v, $values["chemical_ink"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_IRON_SULPHIDE = $v, $values["chemical_iron_sulphide"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_LATEX = $v, $values["chemical_latex"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_LITHIUM_HYDRIDE = $v, $values["chemical_lithium_hydride"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_LUMINOL = $v, $values["chemical_luminol"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_MAGNESIUM_NITRATE = $v, $values["chemical_magnesium_nitrate"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_MAGNESIUM_OXIDE = $v, $values["chemical_magnesium_oxide"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_MAGNESIUM_SALTS = $v, $values["chemical_magnesium_salts"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_MERCURIC_CHLORIDE = $v, $values["chemical_mercuric_chloride"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_POLYETHYLENE = $v, $values["chemical_polyethylene"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_POTASSIUM_CHLORIDE = $v, $values["chemical_potassium_chloride"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_POTASSIUM_IODIDE = $v, $values["chemical_potassium_iodide"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_RUBBISH = $v, $values["chemical_rubbish"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SALT = $v, $values["chemical_salt"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SOAP = $v, $values["chemical_soap"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SODIUM_ACETATE = $v, $values["chemical_sodium_acetate"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SODIUM_FLUORIDE = $v, $values["chemical_sodium_fluoride"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SODIUM_HYDRIDE = $v, $values["chemical_sodium_hydride"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SODIUM_HYDROXIDE = $v, $values["chemical_sodium_hydroxide"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SODIUM_HYPOCHLORITE = $v, $values["chemical_sodium_hypochlorite"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SODIUM_OXIDE = $v, $values["chemical_sodium_oxide"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SUGAR = $v, $values["chemical_sugar"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_SULPHATE = $v, $values["chemical_sulphate"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_TUNGSTEN_CHLORIDE = $v, $values["chemical_tungsten_chloride"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCHEMICAL_WATER = $v, $values["chemical_water"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mCHERRY_HANGING_SIGN = $v, $values["cherry_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mCHERRY_SIGN = $v, $values["cherry_sign"]);
		self::unsafeAssign(fn(ChorusFruit $v) => self::$_mCHORUS_FRUIT = $v, $values["chorus_fruit"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCLAY = $v, $values["clay"]);
		self::unsafeAssign(fn(Clock $v) => self::$_mCLOCK = $v, $values["clock"]);
		self::unsafeAssign(fn(Clownfish $v) => self::$_mCLOWNFISH = $v, $values["clownfish"]);
		self::unsafeAssign(fn(Coal $v) => self::$_mCOAL = $v, $values["coal"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCOAST_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["coast_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(CocoaBeans $v) => self::$_mCOCOA_BEANS = $v, $values["cocoa_beans"]);
		self::unsafeAssign(fn(Compass $v) => self::$_mCOMPASS = $v, $values["compass"]);
		self::unsafeAssign(fn(CookedChicken $v) => self::$_mCOOKED_CHICKEN = $v, $values["cooked_chicken"]);
		self::unsafeAssign(fn(CookedFish $v) => self::$_mCOOKED_FISH = $v, $values["cooked_fish"]);
		self::unsafeAssign(fn(CookedMutton $v) => self::$_mCOOKED_MUTTON = $v, $values["cooked_mutton"]);
		self::unsafeAssign(fn(CookedPorkchop $v) => self::$_mCOOKED_PORKCHOP = $v, $values["cooked_porkchop"]);
		self::unsafeAssign(fn(CookedRabbit $v) => self::$_mCOOKED_RABBIT = $v, $values["cooked_rabbit"]);
		self::unsafeAssign(fn(CookedSalmon $v) => self::$_mCOOKED_SALMON = $v, $values["cooked_salmon"]);
		self::unsafeAssign(fn(Cookie $v) => self::$_mCOOKIE = $v, $values["cookie"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mCOPPER_BOOTS = $v, $values["copper_boots"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mCOPPER_CHESTPLATE = $v, $values["copper_chestplate"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mCOPPER_HELMET = $v, $values["copper_helmet"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCOPPER_INGOT = $v, $values["copper_ingot"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mCOPPER_LEGGINGS = $v, $values["copper_leggings"]);
		self::unsafeAssign(fn(Item $v) => self::$_mCOPPER_NUGGET = $v, $values["copper_nugget"]);
		self::unsafeAssign(fn(CoralFan $v) => self::$_mCORAL_FAN = $v, $values["coral_fan"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mCRIMSON_HANGING_SIGN = $v, $values["crimson_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mCRIMSON_SIGN = $v, $values["crimson_sign"]);
		self::unsafeAssign(fn(Boat $v) => self::$_mDARK_OAK_BOAT = $v, $values["dark_oak_boat"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mDARK_OAK_HANGING_SIGN = $v, $values["dark_oak_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mDARK_OAK_SIGN = $v, $values["dark_oak_sign"]);
		self::unsafeAssign(fn(Item $v) => self::$_mDIAMOND = $v, $values["diamond"]);
		self::unsafeAssign(fn(Axe $v) => self::$_mDIAMOND_AXE = $v, $values["diamond_axe"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mDIAMOND_BOOTS = $v, $values["diamond_boots"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mDIAMOND_CHESTPLATE = $v, $values["diamond_chestplate"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mDIAMOND_HELMET = $v, $values["diamond_helmet"]);
		self::unsafeAssign(fn(Hoe $v) => self::$_mDIAMOND_HOE = $v, $values["diamond_hoe"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mDIAMOND_LEGGINGS = $v, $values["diamond_leggings"]);
		self::unsafeAssign(fn(Pickaxe $v) => self::$_mDIAMOND_PICKAXE = $v, $values["diamond_pickaxe"]);
		self::unsafeAssign(fn(Shovel $v) => self::$_mDIAMOND_SHOVEL = $v, $values["diamond_shovel"]);
		self::unsafeAssign(fn(Sword $v) => self::$_mDIAMOND_SWORD = $v, $values["diamond_sword"]);
		self::unsafeAssign(fn(Item $v) => self::$_mDISC_FRAGMENT_5 = $v, $values["disc_fragment_5"]);
		self::unsafeAssign(fn(Item $v) => self::$_mDRAGON_BREATH = $v, $values["dragon_breath"]);
		self::unsafeAssign(fn(DriedKelp $v) => self::$_mDRIED_KELP = $v, $values["dried_kelp"]);
		self::unsafeAssign(fn(Item $v) => self::$_mDUNE_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["dune_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(Dye $v) => self::$_mDYE = $v, $values["dye"]);
		self::unsafeAssign(fn(Item $v) => self::$_mECHO_SHARD = $v, $values["echo_shard"]);
		self::unsafeAssign(fn(Egg $v) => self::$_mEGG = $v, $values["egg"]);
		self::unsafeAssign(fn(Item $v) => self::$_mEMERALD = $v, $values["emerald"]);
		self::unsafeAssign(fn(EnchantedBook $v) => self::$_mENCHANTED_BOOK = $v, $values["enchanted_book"]);
		self::unsafeAssign(fn(GoldenAppleEnchanted $v) => self::$_mENCHANTED_GOLDEN_APPLE = $v, $values["enchanted_golden_apple"]);
		self::unsafeAssign(fn(EnderPearl $v) => self::$_mENDER_PEARL = $v, $values["ender_pearl"]);
		self::unsafeAssign(fn(EndCrystal $v) => self::$_mEND_CRYSTAL = $v, $values["end_crystal"]);
		self::unsafeAssign(fn(ExperienceBottle $v) => self::$_mEXPERIENCE_BOTTLE = $v, $values["experience_bottle"]);
		self::unsafeAssign(fn(Item $v) => self::$_mEYE_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["eye_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(Item $v) => self::$_mFEATHER = $v, $values["feather"]);
		self::unsafeAssign(fn(Item $v) => self::$_mFERMENTED_SPIDER_EYE = $v, $values["fermented_spider_eye"]);
		self::unsafeAssign(fn(FireworkRocket $v) => self::$_mFIREWORK_ROCKET = $v, $values["firework_rocket"]);
		self::unsafeAssign(fn(FireworkStar $v) => self::$_mFIREWORK_STAR = $v, $values["firework_star"]);
		self::unsafeAssign(fn(FireCharge $v) => self::$_mFIRE_CHARGE = $v, $values["fire_charge"]);
		self::unsafeAssign(fn(FishingRod $v) => self::$_mFISHING_ROD = $v, $values["fishing_rod"]);
		self::unsafeAssign(fn(Item $v) => self::$_mFLINT = $v, $values["flint"]);
		self::unsafeAssign(fn(FlintSteel $v) => self::$_mFLINT_AND_STEEL = $v, $values["flint_and_steel"]);
		self::unsafeAssign(fn(Item $v) => self::$_mGHAST_TEAR = $v, $values["ghast_tear"]);
		self::unsafeAssign(fn(GlassBottle $v) => self::$_mGLASS_BOTTLE = $v, $values["glass_bottle"]);
		self::unsafeAssign(fn(Item $v) => self::$_mGLISTERING_MELON = $v, $values["glistering_melon"]);
		self::unsafeAssign(fn(Item $v) => self::$_mGLOWSTONE_DUST = $v, $values["glowstone_dust"]);
		self::unsafeAssign(fn(GlowBerries $v) => self::$_mGLOW_BERRIES = $v, $values["glow_berries"]);
		self::unsafeAssign(fn(Item $v) => self::$_mGLOW_INK_SAC = $v, $values["glow_ink_sac"]);
		self::unsafeAssign(fn(GoatHorn $v) => self::$_mGOAT_HORN = $v, $values["goat_horn"]);
		self::unsafeAssign(fn(GoldenApple $v) => self::$_mGOLDEN_APPLE = $v, $values["golden_apple"]);
		self::unsafeAssign(fn(Axe $v) => self::$_mGOLDEN_AXE = $v, $values["golden_axe"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mGOLDEN_BOOTS = $v, $values["golden_boots"]);
		self::unsafeAssign(fn(GoldenCarrot $v) => self::$_mGOLDEN_CARROT = $v, $values["golden_carrot"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mGOLDEN_CHESTPLATE = $v, $values["golden_chestplate"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mGOLDEN_HELMET = $v, $values["golden_helmet"]);
		self::unsafeAssign(fn(Hoe $v) => self::$_mGOLDEN_HOE = $v, $values["golden_hoe"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mGOLDEN_LEGGINGS = $v, $values["golden_leggings"]);
		self::unsafeAssign(fn(Pickaxe $v) => self::$_mGOLDEN_PICKAXE = $v, $values["golden_pickaxe"]);
		self::unsafeAssign(fn(Shovel $v) => self::$_mGOLDEN_SHOVEL = $v, $values["golden_shovel"]);
		self::unsafeAssign(fn(Sword $v) => self::$_mGOLDEN_SWORD = $v, $values["golden_sword"]);
		self::unsafeAssign(fn(Item $v) => self::$_mGOLD_INGOT = $v, $values["gold_ingot"]);
		self::unsafeAssign(fn(Item $v) => self::$_mGOLD_NUGGET = $v, $values["gold_nugget"]);
		self::unsafeAssign(fn(Item $v) => self::$_mGUNPOWDER = $v, $values["gunpowder"]);
		self::unsafeAssign(fn(Item $v) => self::$_mHEART_OF_THE_SEA = $v, $values["heart_of_the_sea"]);
		self::unsafeAssign(fn(Item $v) => self::$_mHONEYCOMB = $v, $values["honeycomb"]);
		self::unsafeAssign(fn(HoneyBottle $v) => self::$_mHONEY_BOTTLE = $v, $values["honey_bottle"]);
		self::unsafeAssign(fn(Item $v) => self::$_mHOST_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["host_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(IceBomb $v) => self::$_mICE_BOMB = $v, $values["ice_bomb"]);
		self::unsafeAssign(fn(Item $v) => self::$_mINK_SAC = $v, $values["ink_sac"]);
		self::unsafeAssign(fn(Axe $v) => self::$_mIRON_AXE = $v, $values["iron_axe"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mIRON_BOOTS = $v, $values["iron_boots"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mIRON_CHESTPLATE = $v, $values["iron_chestplate"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mIRON_HELMET = $v, $values["iron_helmet"]);
		self::unsafeAssign(fn(Hoe $v) => self::$_mIRON_HOE = $v, $values["iron_hoe"]);
		self::unsafeAssign(fn(Item $v) => self::$_mIRON_INGOT = $v, $values["iron_ingot"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mIRON_LEGGINGS = $v, $values["iron_leggings"]);
		self::unsafeAssign(fn(Item $v) => self::$_mIRON_NUGGET = $v, $values["iron_nugget"]);
		self::unsafeAssign(fn(Pickaxe $v) => self::$_mIRON_PICKAXE = $v, $values["iron_pickaxe"]);
		self::unsafeAssign(fn(Shovel $v) => self::$_mIRON_SHOVEL = $v, $values["iron_shovel"]);
		self::unsafeAssign(fn(Sword $v) => self::$_mIRON_SWORD = $v, $values["iron_sword"]);
		self::unsafeAssign(fn(Boat $v) => self::$_mJUNGLE_BOAT = $v, $values["jungle_boat"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mJUNGLE_HANGING_SIGN = $v, $values["jungle_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mJUNGLE_SIGN = $v, $values["jungle_sign"]);
		self::unsafeAssign(fn(Item $v) => self::$_mLAPIS_LAZULI = $v, $values["lapis_lazuli"]);
		self::unsafeAssign(fn(LiquidBucket $v) => self::$_mLAVA_BUCKET = $v, $values["lava_bucket"]);
		self::unsafeAssign(fn(Item $v) => self::$_mLEATHER = $v, $values["leather"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mLEATHER_BOOTS = $v, $values["leather_boots"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mLEATHER_CAP = $v, $values["leather_cap"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mLEATHER_PANTS = $v, $values["leather_pants"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mLEATHER_TUNIC = $v, $values["leather_tunic"]);
		self::unsafeAssign(fn(SplashPotion $v) => self::$_mLINGERING_POTION = $v, $values["lingering_potion"]);
		self::unsafeAssign(fn(Item $v) => self::$_mMAGMA_CREAM = $v, $values["magma_cream"]);
		self::unsafeAssign(fn(Boat $v) => self::$_mMANGROVE_BOAT = $v, $values["mangrove_boat"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mMANGROVE_HANGING_SIGN = $v, $values["mangrove_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mMANGROVE_SIGN = $v, $values["mangrove_sign"]);
		self::unsafeAssign(fn(Medicine $v) => self::$_mMEDICINE = $v, $values["medicine"]);
		self::unsafeAssign(fn(Melon $v) => self::$_mMELON = $v, $values["melon"]);
		self::unsafeAssign(fn(MelonSeeds $v) => self::$_mMELON_SEEDS = $v, $values["melon_seeds"]);
		self::unsafeAssign(fn(MilkBucket $v) => self::$_mMILK_BUCKET = $v, $values["milk_bucket"]);
		self::unsafeAssign(fn(Minecart $v) => self::$_mMINECART = $v, $values["minecart"]);
		self::unsafeAssign(fn(MushroomStew $v) => self::$_mMUSHROOM_STEW = $v, $values["mushroom_stew"]);
		self::unsafeAssign(fn(NameTag $v) => self::$_mNAME_TAG = $v, $values["name_tag"]);
		self::unsafeAssign(fn(Item $v) => self::$_mNAUTILUS_SHELL = $v, $values["nautilus_shell"]);
		self::unsafeAssign(fn(Axe $v) => self::$_mNETHERITE_AXE = $v, $values["netherite_axe"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mNETHERITE_BOOTS = $v, $values["netherite_boots"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mNETHERITE_CHESTPLATE = $v, $values["netherite_chestplate"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mNETHERITE_HELMET = $v, $values["netherite_helmet"]);
		self::unsafeAssign(fn(Hoe $v) => self::$_mNETHERITE_HOE = $v, $values["netherite_hoe"]);
		self::unsafeAssign(fn(Item $v) => self::$_mNETHERITE_INGOT = $v, $values["netherite_ingot"]);
		self::unsafeAssign(fn(Armor $v) => self::$_mNETHERITE_LEGGINGS = $v, $values["netherite_leggings"]);
		self::unsafeAssign(fn(Pickaxe $v) => self::$_mNETHERITE_PICKAXE = $v, $values["netherite_pickaxe"]);
		self::unsafeAssign(fn(Item $v) => self::$_mNETHERITE_SCRAP = $v, $values["netherite_scrap"]);
		self::unsafeAssign(fn(Shovel $v) => self::$_mNETHERITE_SHOVEL = $v, $values["netherite_shovel"]);
		self::unsafeAssign(fn(Sword $v) => self::$_mNETHERITE_SWORD = $v, $values["netherite_sword"]);
		self::unsafeAssign(fn(Item $v) => self::$_mNETHERITE_UPGRADE_SMITHING_TEMPLATE = $v, $values["netherite_upgrade_smithing_template"]);
		self::unsafeAssign(fn(Item $v) => self::$_mNETHER_BRICK = $v, $values["nether_brick"]);
		self::unsafeAssign(fn(Item $v) => self::$_mNETHER_QUARTZ = $v, $values["nether_quartz"]);
		self::unsafeAssign(fn(Item $v) => self::$_mNETHER_STAR = $v, $values["nether_star"]);
		self::unsafeAssign(fn(Boat $v) => self::$_mOAK_BOAT = $v, $values["oak_boat"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mOAK_HANGING_SIGN = $v, $values["oak_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mOAK_SIGN = $v, $values["oak_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mOMINOUS_BANNER = $v, $values["ominous_banner"]);
		self::unsafeAssign(fn(PaintingItem $v) => self::$_mPAINTING = $v, $values["painting"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mPALE_OAK_HANGING_SIGN = $v, $values["pale_oak_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mPALE_OAK_SIGN = $v, $values["pale_oak_sign"]);
		self::unsafeAssign(fn(Item $v) => self::$_mPAPER = $v, $values["paper"]);
		self::unsafeAssign(fn(Item $v) => self::$_mPHANTOM_MEMBRANE = $v, $values["phantom_membrane"]);
		self::unsafeAssign(fn(PitcherPod $v) => self::$_mPITCHER_POD = $v, $values["pitcher_pod"]);
		self::unsafeAssign(fn(PoisonousPotato $v) => self::$_mPOISONOUS_POTATO = $v, $values["poisonous_potato"]);
		self::unsafeAssign(fn(Item $v) => self::$_mPOPPED_CHORUS_FRUIT = $v, $values["popped_chorus_fruit"]);
		self::unsafeAssign(fn(Potato $v) => self::$_mPOTATO = $v, $values["potato"]);
		self::unsafeAssign(fn(Potion $v) => self::$_mPOTION = $v, $values["potion"]);
		self::unsafeAssign(fn(Item $v) => self::$_mPRISMARINE_CRYSTALS = $v, $values["prismarine_crystals"]);
		self::unsafeAssign(fn(Item $v) => self::$_mPRISMARINE_SHARD = $v, $values["prismarine_shard"]);
		self::unsafeAssign(fn(Pufferfish $v) => self::$_mPUFFERFISH = $v, $values["pufferfish"]);
		self::unsafeAssign(fn(PumpkinPie $v) => self::$_mPUMPKIN_PIE = $v, $values["pumpkin_pie"]);
		self::unsafeAssign(fn(PumpkinSeeds $v) => self::$_mPUMPKIN_SEEDS = $v, $values["pumpkin_seeds"]);
		self::unsafeAssign(fn(Item $v) => self::$_mRABBIT_FOOT = $v, $values["rabbit_foot"]);
		self::unsafeAssign(fn(Item $v) => self::$_mRABBIT_HIDE = $v, $values["rabbit_hide"]);
		self::unsafeAssign(fn(RabbitStew $v) => self::$_mRABBIT_STEW = $v, $values["rabbit_stew"]);
		self::unsafeAssign(fn(Item $v) => self::$_mRAISER_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["raiser_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(RawBeef $v) => self::$_mRAW_BEEF = $v, $values["raw_beef"]);
		self::unsafeAssign(fn(RawChicken $v) => self::$_mRAW_CHICKEN = $v, $values["raw_chicken"]);
		self::unsafeAssign(fn(Item $v) => self::$_mRAW_COPPER = $v, $values["raw_copper"]);
		self::unsafeAssign(fn(RawFish $v) => self::$_mRAW_FISH = $v, $values["raw_fish"]);
		self::unsafeAssign(fn(Item $v) => self::$_mRAW_GOLD = $v, $values["raw_gold"]);
		self::unsafeAssign(fn(Item $v) => self::$_mRAW_IRON = $v, $values["raw_iron"]);
		self::unsafeAssign(fn(RawMutton $v) => self::$_mRAW_MUTTON = $v, $values["raw_mutton"]);
		self::unsafeAssign(fn(RawPorkchop $v) => self::$_mRAW_PORKCHOP = $v, $values["raw_porkchop"]);
		self::unsafeAssign(fn(RawRabbit $v) => self::$_mRAW_RABBIT = $v, $values["raw_rabbit"]);
		self::unsafeAssign(fn(RawSalmon $v) => self::$_mRAW_SALMON = $v, $values["raw_salmon"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_11 = $v, $values["record_11"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_13 = $v, $values["record_13"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_5 = $v, $values["record_5"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_BLOCKS = $v, $values["record_blocks"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_CAT = $v, $values["record_cat"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_CHIRP = $v, $values["record_chirp"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_CREATOR = $v, $values["record_creator"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_CREATOR_MUSIC_BOX = $v, $values["record_creator_music_box"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_FAR = $v, $values["record_far"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_MALL = $v, $values["record_mall"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_MELLOHI = $v, $values["record_mellohi"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_OTHERSIDE = $v, $values["record_otherside"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_PIGSTEP = $v, $values["record_pigstep"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_PRECIPICE = $v, $values["record_precipice"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_RELIC = $v, $values["record_relic"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_STAL = $v, $values["record_stal"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_STRAD = $v, $values["record_strad"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_WAIT = $v, $values["record_wait"]);
		self::unsafeAssign(fn(Record $v) => self::$_mRECORD_WARD = $v, $values["record_ward"]);
		self::unsafeAssign(fn(Item $v) => self::$_mRECOVERY_COMPASS = $v, $values["recovery_compass"]);
		self::unsafeAssign(fn(Redstone $v) => self::$_mREDSTONE_DUST = $v, $values["redstone_dust"]);
		self::unsafeAssign(fn(Item $v) => self::$_mRESIN_BRICK = $v, $values["resin_brick"]);
		self::unsafeAssign(fn(Item $v) => self::$_mRIB_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["rib_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(RottenFlesh $v) => self::$_mROTTEN_FLESH = $v, $values["rotten_flesh"]);
		self::unsafeAssign(fn(Item $v) => self::$_mSCUTE = $v, $values["scute"]);
		self::unsafeAssign(fn(Item $v) => self::$_mSENTRY_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["sentry_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(Item $v) => self::$_mSHAPER_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["shaper_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(Shears $v) => self::$_mSHEARS = $v, $values["shears"]);
		self::unsafeAssign(fn(Item $v) => self::$_mSHULKER_SHELL = $v, $values["shulker_shell"]);
		self::unsafeAssign(fn(Item $v) => self::$_mSILENCE_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["silence_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(Item $v) => self::$_mSLIMEBALL = $v, $values["slimeball"]);
		self::unsafeAssign(fn(Item $v) => self::$_mSNOUT_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["snout_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(Snowball $v) => self::$_mSNOWBALL = $v, $values["snowball"]);
		self::unsafeAssign(fn(SpiderEye $v) => self::$_mSPIDER_EYE = $v, $values["spider_eye"]);
		self::unsafeAssign(fn(Item $v) => self::$_mSPIRE_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["spire_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(SplashPotion $v) => self::$_mSPLASH_POTION = $v, $values["splash_potion"]);
		self::unsafeAssign(fn(Boat $v) => self::$_mSPRUCE_BOAT = $v, $values["spruce_boat"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mSPRUCE_HANGING_SIGN = $v, $values["spruce_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mSPRUCE_SIGN = $v, $values["spruce_sign"]);
		self::unsafeAssign(fn(Spyglass $v) => self::$_mSPYGLASS = $v, $values["spyglass"]);
		self::unsafeAssign(fn(SpawnEgg $v) => self::$_mSQUID_SPAWN_EGG = $v, $values["squid_spawn_egg"]);
		self::unsafeAssign(fn(Steak $v) => self::$_mSTEAK = $v, $values["steak"]);
		self::unsafeAssign(fn(Stick $v) => self::$_mSTICK = $v, $values["stick"]);
		self::unsafeAssign(fn(Axe $v) => self::$_mSTONE_AXE = $v, $values["stone_axe"]);
		self::unsafeAssign(fn(Hoe $v) => self::$_mSTONE_HOE = $v, $values["stone_hoe"]);
		self::unsafeAssign(fn(Pickaxe $v) => self::$_mSTONE_PICKAXE = $v, $values["stone_pickaxe"]);
		self::unsafeAssign(fn(Shovel $v) => self::$_mSTONE_SHOVEL = $v, $values["stone_shovel"]);
		self::unsafeAssign(fn(Sword $v) => self::$_mSTONE_SWORD = $v, $values["stone_sword"]);
		self::unsafeAssign(fn(StringItem $v) => self::$_mSTRING = $v, $values["string"]);
		self::unsafeAssign(fn(Item $v) => self::$_mSUGAR = $v, $values["sugar"]);
		self::unsafeAssign(fn(SuspiciousStew $v) => self::$_mSUSPICIOUS_STEW = $v, $values["suspicious_stew"]);
		self::unsafeAssign(fn(SweetBerries $v) => self::$_mSWEET_BERRIES = $v, $values["sweet_berries"]);
		self::unsafeAssign(fn(Item $v) => self::$_mTIDE_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["tide_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(TorchflowerSeeds $v) => self::$_mTORCHFLOWER_SEEDS = $v, $values["torchflower_seeds"]);
		self::unsafeAssign(fn(Totem $v) => self::$_mTOTEM = $v, $values["totem"]);
		self::unsafeAssign(fn(Trident $v) => self::$_mTRIDENT = $v, $values["trident"]);
		self::unsafeAssign(fn(TurtleHelmet $v) => self::$_mTURTLE_HELMET = $v, $values["turtle_helmet"]);
		self::unsafeAssign(fn(Item $v) => self::$_mVEX_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["vex_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(SpawnEgg $v) => self::$_mVILLAGER_SPAWN_EGG = $v, $values["villager_spawn_egg"]);
		self::unsafeAssign(fn(Item $v) => self::$_mWARD_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["ward_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(HangingSign $v) => self::$_mWARPED_HANGING_SIGN = $v, $values["warped_hanging_sign"]);
		self::unsafeAssign(fn(ItemBlockWallOrFloor $v) => self::$_mWARPED_SIGN = $v, $values["warped_sign"]);
		self::unsafeAssign(fn(LiquidBucket $v) => self::$_mWATER_BUCKET = $v, $values["water_bucket"]);
		self::unsafeAssign(fn(Item $v) => self::$_mWAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["wayfinder_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(Item $v) => self::$_mWHEAT = $v, $values["wheat"]);
		self::unsafeAssign(fn(WheatSeeds $v) => self::$_mWHEAT_SEEDS = $v, $values["wheat_seeds"]);
		self::unsafeAssign(fn(Item $v) => self::$_mWILD_ARMOR_TRIM_SMITHING_TEMPLATE = $v, $values["wild_armor_trim_smithing_template"]);
		self::unsafeAssign(fn(Axe $v) => self::$_mWOODEN_AXE = $v, $values["wooden_axe"]);
		self::unsafeAssign(fn(Hoe $v) => self::$_mWOODEN_HOE = $v, $values["wooden_hoe"]);
		self::unsafeAssign(fn(Pickaxe $v) => self::$_mWOODEN_PICKAXE = $v, $values["wooden_pickaxe"]);
		self::unsafeAssign(fn(Shovel $v) => self::$_mWOODEN_SHOVEL = $v, $values["wooden_shovel"]);
		self::unsafeAssign(fn(Sword $v) => self::$_mWOODEN_SWORD = $v, $values["wooden_sword"]);
		self::unsafeAssign(fn(WritableBook $v) => self::$_mWRITABLE_BOOK = $v, $values["writable_book"]);
		self::unsafeAssign(fn(WrittenBook $v) => self::$_mWRITTEN_BOOK = $v, $values["written_book"]);
		self::unsafeAssign(fn(SpawnEgg $v) => self::$_mZOMBIE_SPAWN_EGG = $v, $values["zombie_spawn_egg"]);
	}

	/**
	 * @return Item[]
	 * @phpstan-return array<string, Item>
	 */
	public static function getAll() : array{
		if(!isset(self::$members)){ self::init(); }
		return array_map(VanillaItemsInputs::cloneMember(...), self::$members);
	}

	public static function ACACIA_BOAT() : Boat{
		if(!isset(self::$_mACACIA_BOAT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mACACIA_BOAT);
	}

	public static function ACACIA_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mACACIA_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mACACIA_HANGING_SIGN);
	}

	public static function ACACIA_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mACACIA_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mACACIA_SIGN);
	}

	public static function AIR() : ItemBlock{
		if(!isset(self::$_mAIR)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mAIR);
	}

	public static function AMETHYST_SHARD() : Item{
		if(!isset(self::$_mAMETHYST_SHARD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mAMETHYST_SHARD);
	}

	public static function APPLE() : Apple{
		if(!isset(self::$_mAPPLE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mAPPLE);
	}

	public static function ARROW() : Arrow{
		if(!isset(self::$_mARROW)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mARROW);
	}

	public static function BAKED_POTATO() : BakedPotato{
		if(!isset(self::$_mBAKED_POTATO)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBAKED_POTATO);
	}

	public static function BAMBOO() : Bamboo{
		if(!isset(self::$_mBAMBOO)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBAMBOO);
	}

	public static function BANNER() : Banner{
		if(!isset(self::$_mBANNER)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBANNER);
	}

	public static function BEETROOT() : Beetroot{
		if(!isset(self::$_mBEETROOT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBEETROOT);
	}

	public static function BEETROOT_SEEDS() : BeetrootSeeds{
		if(!isset(self::$_mBEETROOT_SEEDS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBEETROOT_SEEDS);
	}

	public static function BEETROOT_SOUP() : BeetrootSoup{
		if(!isset(self::$_mBEETROOT_SOUP)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBEETROOT_SOUP);
	}

	public static function BIRCH_BOAT() : Boat{
		if(!isset(self::$_mBIRCH_BOAT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBIRCH_BOAT);
	}

	public static function BIRCH_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mBIRCH_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBIRCH_HANGING_SIGN);
	}

	public static function BIRCH_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mBIRCH_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBIRCH_SIGN);
	}

	public static function BLAZE_POWDER() : Item{
		if(!isset(self::$_mBLAZE_POWDER)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBLAZE_POWDER);
	}

	public static function BLAZE_ROD() : BlazeRod{
		if(!isset(self::$_mBLAZE_ROD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBLAZE_ROD);
	}

	public static function BLEACH() : Item{
		if(!isset(self::$_mBLEACH)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBLEACH);
	}

	public static function BONE() : Item{
		if(!isset(self::$_mBONE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBONE);
	}

	public static function BONE_MEAL() : Fertilizer{
		if(!isset(self::$_mBONE_MEAL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBONE_MEAL);
	}

	public static function BOOK() : Book{
		if(!isset(self::$_mBOOK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBOOK);
	}

	public static function BOW() : Bow{
		if(!isset(self::$_mBOW)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBOW);
	}

	public static function BOWL() : Bowl{
		if(!isset(self::$_mBOWL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBOWL);
	}

	public static function BREAD() : Bread{
		if(!isset(self::$_mBREAD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBREAD);
	}

	public static function BRICK() : Item{
		if(!isset(self::$_mBRICK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBRICK);
	}

	public static function BUCKET() : Bucket{
		if(!isset(self::$_mBUCKET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mBUCKET);
	}

	public static function CARROT() : Carrot{
		if(!isset(self::$_mCARROT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCARROT);
	}

	public static function CHAINMAIL_BOOTS() : Armor{
		if(!isset(self::$_mCHAINMAIL_BOOTS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHAINMAIL_BOOTS);
	}

	public static function CHAINMAIL_CHESTPLATE() : Armor{
		if(!isset(self::$_mCHAINMAIL_CHESTPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHAINMAIL_CHESTPLATE);
	}

	public static function CHAINMAIL_HELMET() : Armor{
		if(!isset(self::$_mCHAINMAIL_HELMET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHAINMAIL_HELMET);
	}

	public static function CHAINMAIL_LEGGINGS() : Armor{
		if(!isset(self::$_mCHAINMAIL_LEGGINGS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHAINMAIL_LEGGINGS);
	}

	public static function CHARCOAL() : Coal{
		if(!isset(self::$_mCHARCOAL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHARCOAL);
	}

	public static function CHEMICAL_ALUMINIUM_OXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_ALUMINIUM_OXIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_ALUMINIUM_OXIDE);
	}

	public static function CHEMICAL_AMMONIA() : Item{
		if(!isset(self::$_mCHEMICAL_AMMONIA)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_AMMONIA);
	}

	public static function CHEMICAL_BARIUM_SULPHATE() : Item{
		if(!isset(self::$_mCHEMICAL_BARIUM_SULPHATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_BARIUM_SULPHATE);
	}

	public static function CHEMICAL_BENZENE() : Item{
		if(!isset(self::$_mCHEMICAL_BENZENE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_BENZENE);
	}

	public static function CHEMICAL_BORON_TRIOXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_BORON_TRIOXIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_BORON_TRIOXIDE);
	}

	public static function CHEMICAL_CALCIUM_BROMIDE() : Item{
		if(!isset(self::$_mCHEMICAL_CALCIUM_BROMIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_CALCIUM_BROMIDE);
	}

	public static function CHEMICAL_CALCIUM_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_CALCIUM_CHLORIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_CALCIUM_CHLORIDE);
	}

	public static function CHEMICAL_CERIUM_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_CERIUM_CHLORIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_CERIUM_CHLORIDE);
	}

	public static function CHEMICAL_CHARCOAL() : Item{
		if(!isset(self::$_mCHEMICAL_CHARCOAL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_CHARCOAL);
	}

	public static function CHEMICAL_CRUDE_OIL() : Item{
		if(!isset(self::$_mCHEMICAL_CRUDE_OIL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_CRUDE_OIL);
	}

	public static function CHEMICAL_GLUE() : Item{
		if(!isset(self::$_mCHEMICAL_GLUE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_GLUE);
	}

	public static function CHEMICAL_HYDROGEN_PEROXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_HYDROGEN_PEROXIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_HYDROGEN_PEROXIDE);
	}

	public static function CHEMICAL_HYPOCHLORITE() : Item{
		if(!isset(self::$_mCHEMICAL_HYPOCHLORITE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_HYPOCHLORITE);
	}

	public static function CHEMICAL_INK() : Item{
		if(!isset(self::$_mCHEMICAL_INK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_INK);
	}

	public static function CHEMICAL_IRON_SULPHIDE() : Item{
		if(!isset(self::$_mCHEMICAL_IRON_SULPHIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_IRON_SULPHIDE);
	}

	public static function CHEMICAL_LATEX() : Item{
		if(!isset(self::$_mCHEMICAL_LATEX)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_LATEX);
	}

	public static function CHEMICAL_LITHIUM_HYDRIDE() : Item{
		if(!isset(self::$_mCHEMICAL_LITHIUM_HYDRIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_LITHIUM_HYDRIDE);
	}

	public static function CHEMICAL_LUMINOL() : Item{
		if(!isset(self::$_mCHEMICAL_LUMINOL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_LUMINOL);
	}

	public static function CHEMICAL_MAGNESIUM_NITRATE() : Item{
		if(!isset(self::$_mCHEMICAL_MAGNESIUM_NITRATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_MAGNESIUM_NITRATE);
	}

	public static function CHEMICAL_MAGNESIUM_OXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_MAGNESIUM_OXIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_MAGNESIUM_OXIDE);
	}

	public static function CHEMICAL_MAGNESIUM_SALTS() : Item{
		if(!isset(self::$_mCHEMICAL_MAGNESIUM_SALTS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_MAGNESIUM_SALTS);
	}

	public static function CHEMICAL_MERCURIC_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_MERCURIC_CHLORIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_MERCURIC_CHLORIDE);
	}

	public static function CHEMICAL_POLYETHYLENE() : Item{
		if(!isset(self::$_mCHEMICAL_POLYETHYLENE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_POLYETHYLENE);
	}

	public static function CHEMICAL_POTASSIUM_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_POTASSIUM_CHLORIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_POTASSIUM_CHLORIDE);
	}

	public static function CHEMICAL_POTASSIUM_IODIDE() : Item{
		if(!isset(self::$_mCHEMICAL_POTASSIUM_IODIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_POTASSIUM_IODIDE);
	}

	public static function CHEMICAL_RUBBISH() : Item{
		if(!isset(self::$_mCHEMICAL_RUBBISH)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_RUBBISH);
	}

	public static function CHEMICAL_SALT() : Item{
		if(!isset(self::$_mCHEMICAL_SALT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SALT);
	}

	public static function CHEMICAL_SOAP() : Item{
		if(!isset(self::$_mCHEMICAL_SOAP)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SOAP);
	}

	public static function CHEMICAL_SODIUM_ACETATE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_ACETATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SODIUM_ACETATE);
	}

	public static function CHEMICAL_SODIUM_FLUORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_FLUORIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SODIUM_FLUORIDE);
	}

	public static function CHEMICAL_SODIUM_HYDRIDE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_HYDRIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SODIUM_HYDRIDE);
	}

	public static function CHEMICAL_SODIUM_HYDROXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_HYDROXIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SODIUM_HYDROXIDE);
	}

	public static function CHEMICAL_SODIUM_HYPOCHLORITE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_HYPOCHLORITE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SODIUM_HYPOCHLORITE);
	}

	public static function CHEMICAL_SODIUM_OXIDE() : Item{
		if(!isset(self::$_mCHEMICAL_SODIUM_OXIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SODIUM_OXIDE);
	}

	public static function CHEMICAL_SUGAR() : Item{
		if(!isset(self::$_mCHEMICAL_SUGAR)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SUGAR);
	}

	public static function CHEMICAL_SULPHATE() : Item{
		if(!isset(self::$_mCHEMICAL_SULPHATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_SULPHATE);
	}

	public static function CHEMICAL_TUNGSTEN_CHLORIDE() : Item{
		if(!isset(self::$_mCHEMICAL_TUNGSTEN_CHLORIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_TUNGSTEN_CHLORIDE);
	}

	public static function CHEMICAL_WATER() : Item{
		if(!isset(self::$_mCHEMICAL_WATER)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHEMICAL_WATER);
	}

	public static function CHERRY_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mCHERRY_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHERRY_HANGING_SIGN);
	}

	public static function CHERRY_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mCHERRY_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHERRY_SIGN);
	}

	public static function CHORUS_FRUIT() : ChorusFruit{
		if(!isset(self::$_mCHORUS_FRUIT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCHORUS_FRUIT);
	}

	public static function CLAY() : Item{
		if(!isset(self::$_mCLAY)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCLAY);
	}

	public static function CLOCK() : Clock{
		if(!isset(self::$_mCLOCK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCLOCK);
	}

	public static function CLOWNFISH() : Clownfish{
		if(!isset(self::$_mCLOWNFISH)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCLOWNFISH);
	}

	public static function COAL() : Coal{
		if(!isset(self::$_mCOAL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOAL);
	}

	public static function COAST_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mCOAST_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOAST_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function COCOA_BEANS() : CocoaBeans{
		if(!isset(self::$_mCOCOA_BEANS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOCOA_BEANS);
	}

	public static function COMPASS() : Compass{
		if(!isset(self::$_mCOMPASS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOMPASS);
	}

	public static function COOKED_CHICKEN() : CookedChicken{
		if(!isset(self::$_mCOOKED_CHICKEN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOOKED_CHICKEN);
	}

	public static function COOKED_FISH() : CookedFish{
		if(!isset(self::$_mCOOKED_FISH)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOOKED_FISH);
	}

	public static function COOKED_MUTTON() : CookedMutton{
		if(!isset(self::$_mCOOKED_MUTTON)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOOKED_MUTTON);
	}

	public static function COOKED_PORKCHOP() : CookedPorkchop{
		if(!isset(self::$_mCOOKED_PORKCHOP)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOOKED_PORKCHOP);
	}

	public static function COOKED_RABBIT() : CookedRabbit{
		if(!isset(self::$_mCOOKED_RABBIT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOOKED_RABBIT);
	}

	public static function COOKED_SALMON() : CookedSalmon{
		if(!isset(self::$_mCOOKED_SALMON)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOOKED_SALMON);
	}

	public static function COOKIE() : Cookie{
		if(!isset(self::$_mCOOKIE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOOKIE);
	}

	public static function COPPER_BOOTS() : Armor{
		if(!isset(self::$_mCOPPER_BOOTS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOPPER_BOOTS);
	}

	public static function COPPER_CHESTPLATE() : Armor{
		if(!isset(self::$_mCOPPER_CHESTPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOPPER_CHESTPLATE);
	}

	public static function COPPER_HELMET() : Armor{
		if(!isset(self::$_mCOPPER_HELMET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOPPER_HELMET);
	}

	public static function COPPER_INGOT() : Item{
		if(!isset(self::$_mCOPPER_INGOT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOPPER_INGOT);
	}

	public static function COPPER_LEGGINGS() : Armor{
		if(!isset(self::$_mCOPPER_LEGGINGS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOPPER_LEGGINGS);
	}

	public static function COPPER_NUGGET() : Item{
		if(!isset(self::$_mCOPPER_NUGGET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCOPPER_NUGGET);
	}

	public static function CORAL_FAN() : CoralFan{
		if(!isset(self::$_mCORAL_FAN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCORAL_FAN);
	}

	public static function CRIMSON_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mCRIMSON_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCRIMSON_HANGING_SIGN);
	}

	public static function CRIMSON_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mCRIMSON_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mCRIMSON_SIGN);
	}

	public static function DARK_OAK_BOAT() : Boat{
		if(!isset(self::$_mDARK_OAK_BOAT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDARK_OAK_BOAT);
	}

	public static function DARK_OAK_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mDARK_OAK_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDARK_OAK_HANGING_SIGN);
	}

	public static function DARK_OAK_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mDARK_OAK_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDARK_OAK_SIGN);
	}

	public static function DIAMOND() : Item{
		if(!isset(self::$_mDIAMOND)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND);
	}

	public static function DIAMOND_AXE() : Axe{
		if(!isset(self::$_mDIAMOND_AXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND_AXE);
	}

	public static function DIAMOND_BOOTS() : Armor{
		if(!isset(self::$_mDIAMOND_BOOTS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND_BOOTS);
	}

	public static function DIAMOND_CHESTPLATE() : Armor{
		if(!isset(self::$_mDIAMOND_CHESTPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND_CHESTPLATE);
	}

	public static function DIAMOND_HELMET() : Armor{
		if(!isset(self::$_mDIAMOND_HELMET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND_HELMET);
	}

	public static function DIAMOND_HOE() : Hoe{
		if(!isset(self::$_mDIAMOND_HOE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND_HOE);
	}

	public static function DIAMOND_LEGGINGS() : Armor{
		if(!isset(self::$_mDIAMOND_LEGGINGS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND_LEGGINGS);
	}

	public static function DIAMOND_PICKAXE() : Pickaxe{
		if(!isset(self::$_mDIAMOND_PICKAXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND_PICKAXE);
	}

	public static function DIAMOND_SHOVEL() : Shovel{
		if(!isset(self::$_mDIAMOND_SHOVEL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND_SHOVEL);
	}

	public static function DIAMOND_SWORD() : Sword{
		if(!isset(self::$_mDIAMOND_SWORD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDIAMOND_SWORD);
	}

	public static function DISC_FRAGMENT_5() : Item{
		if(!isset(self::$_mDISC_FRAGMENT_5)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDISC_FRAGMENT_5);
	}

	public static function DRAGON_BREATH() : Item{
		if(!isset(self::$_mDRAGON_BREATH)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDRAGON_BREATH);
	}

	public static function DRIED_KELP() : DriedKelp{
		if(!isset(self::$_mDRIED_KELP)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDRIED_KELP);
	}

	public static function DUNE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mDUNE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDUNE_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function DYE() : Dye{
		if(!isset(self::$_mDYE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mDYE);
	}

	public static function ECHO_SHARD() : Item{
		if(!isset(self::$_mECHO_SHARD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mECHO_SHARD);
	}

	public static function EGG() : Egg{
		if(!isset(self::$_mEGG)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mEGG);
	}

	public static function EMERALD() : Item{
		if(!isset(self::$_mEMERALD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mEMERALD);
	}

	public static function ENCHANTED_BOOK() : EnchantedBook{
		if(!isset(self::$_mENCHANTED_BOOK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mENCHANTED_BOOK);
	}

	public static function ENCHANTED_GOLDEN_APPLE() : GoldenAppleEnchanted{
		if(!isset(self::$_mENCHANTED_GOLDEN_APPLE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mENCHANTED_GOLDEN_APPLE);
	}

	public static function ENDER_PEARL() : EnderPearl{
		if(!isset(self::$_mENDER_PEARL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mENDER_PEARL);
	}

	public static function END_CRYSTAL() : EndCrystal{
		if(!isset(self::$_mEND_CRYSTAL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mEND_CRYSTAL);
	}

	public static function EXPERIENCE_BOTTLE() : ExperienceBottle{
		if(!isset(self::$_mEXPERIENCE_BOTTLE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mEXPERIENCE_BOTTLE);
	}

	public static function EYE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mEYE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mEYE_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function FEATHER() : Item{
		if(!isset(self::$_mFEATHER)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mFEATHER);
	}

	public static function FERMENTED_SPIDER_EYE() : Item{
		if(!isset(self::$_mFERMENTED_SPIDER_EYE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mFERMENTED_SPIDER_EYE);
	}

	public static function FIREWORK_ROCKET() : FireworkRocket{
		if(!isset(self::$_mFIREWORK_ROCKET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mFIREWORK_ROCKET);
	}

	public static function FIREWORK_STAR() : FireworkStar{
		if(!isset(self::$_mFIREWORK_STAR)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mFIREWORK_STAR);
	}

	public static function FIRE_CHARGE() : FireCharge{
		if(!isset(self::$_mFIRE_CHARGE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mFIRE_CHARGE);
	}

	public static function FISHING_ROD() : FishingRod{
		if(!isset(self::$_mFISHING_ROD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mFISHING_ROD);
	}

	public static function FLINT() : Item{
		if(!isset(self::$_mFLINT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mFLINT);
	}

	public static function FLINT_AND_STEEL() : FlintSteel{
		if(!isset(self::$_mFLINT_AND_STEEL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mFLINT_AND_STEEL);
	}

	public static function GHAST_TEAR() : Item{
		if(!isset(self::$_mGHAST_TEAR)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGHAST_TEAR);
	}

	public static function GLASS_BOTTLE() : GlassBottle{
		if(!isset(self::$_mGLASS_BOTTLE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGLASS_BOTTLE);
	}

	public static function GLISTERING_MELON() : Item{
		if(!isset(self::$_mGLISTERING_MELON)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGLISTERING_MELON);
	}

	public static function GLOWSTONE_DUST() : Item{
		if(!isset(self::$_mGLOWSTONE_DUST)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGLOWSTONE_DUST);
	}

	public static function GLOW_BERRIES() : GlowBerries{
		if(!isset(self::$_mGLOW_BERRIES)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGLOW_BERRIES);
	}

	public static function GLOW_INK_SAC() : Item{
		if(!isset(self::$_mGLOW_INK_SAC)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGLOW_INK_SAC);
	}

	public static function GOAT_HORN() : GoatHorn{
		if(!isset(self::$_mGOAT_HORN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOAT_HORN);
	}

	public static function GOLDEN_APPLE() : GoldenApple{
		if(!isset(self::$_mGOLDEN_APPLE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_APPLE);
	}

	public static function GOLDEN_AXE() : Axe{
		if(!isset(self::$_mGOLDEN_AXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_AXE);
	}

	public static function GOLDEN_BOOTS() : Armor{
		if(!isset(self::$_mGOLDEN_BOOTS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_BOOTS);
	}

	public static function GOLDEN_CARROT() : GoldenCarrot{
		if(!isset(self::$_mGOLDEN_CARROT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_CARROT);
	}

	public static function GOLDEN_CHESTPLATE() : Armor{
		if(!isset(self::$_mGOLDEN_CHESTPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_CHESTPLATE);
	}

	public static function GOLDEN_HELMET() : Armor{
		if(!isset(self::$_mGOLDEN_HELMET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_HELMET);
	}

	public static function GOLDEN_HOE() : Hoe{
		if(!isset(self::$_mGOLDEN_HOE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_HOE);
	}

	public static function GOLDEN_LEGGINGS() : Armor{
		if(!isset(self::$_mGOLDEN_LEGGINGS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_LEGGINGS);
	}

	public static function GOLDEN_PICKAXE() : Pickaxe{
		if(!isset(self::$_mGOLDEN_PICKAXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_PICKAXE);
	}

	public static function GOLDEN_SHOVEL() : Shovel{
		if(!isset(self::$_mGOLDEN_SHOVEL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_SHOVEL);
	}

	public static function GOLDEN_SWORD() : Sword{
		if(!isset(self::$_mGOLDEN_SWORD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLDEN_SWORD);
	}

	public static function GOLD_INGOT() : Item{
		if(!isset(self::$_mGOLD_INGOT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLD_INGOT);
	}

	public static function GOLD_NUGGET() : Item{
		if(!isset(self::$_mGOLD_NUGGET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGOLD_NUGGET);
	}

	public static function GUNPOWDER() : Item{
		if(!isset(self::$_mGUNPOWDER)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mGUNPOWDER);
	}

	public static function HEART_OF_THE_SEA() : Item{
		if(!isset(self::$_mHEART_OF_THE_SEA)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mHEART_OF_THE_SEA);
	}

	public static function HONEYCOMB() : Item{
		if(!isset(self::$_mHONEYCOMB)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mHONEYCOMB);
	}

	public static function HONEY_BOTTLE() : HoneyBottle{
		if(!isset(self::$_mHONEY_BOTTLE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mHONEY_BOTTLE);
	}

	public static function HOST_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mHOST_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mHOST_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function ICE_BOMB() : IceBomb{
		if(!isset(self::$_mICE_BOMB)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mICE_BOMB);
	}

	public static function INK_SAC() : Item{
		if(!isset(self::$_mINK_SAC)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mINK_SAC);
	}

	public static function IRON_AXE() : Axe{
		if(!isset(self::$_mIRON_AXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_AXE);
	}

	public static function IRON_BOOTS() : Armor{
		if(!isset(self::$_mIRON_BOOTS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_BOOTS);
	}

	public static function IRON_CHESTPLATE() : Armor{
		if(!isset(self::$_mIRON_CHESTPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_CHESTPLATE);
	}

	public static function IRON_HELMET() : Armor{
		if(!isset(self::$_mIRON_HELMET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_HELMET);
	}

	public static function IRON_HOE() : Hoe{
		if(!isset(self::$_mIRON_HOE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_HOE);
	}

	public static function IRON_INGOT() : Item{
		if(!isset(self::$_mIRON_INGOT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_INGOT);
	}

	public static function IRON_LEGGINGS() : Armor{
		if(!isset(self::$_mIRON_LEGGINGS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_LEGGINGS);
	}

	public static function IRON_NUGGET() : Item{
		if(!isset(self::$_mIRON_NUGGET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_NUGGET);
	}

	public static function IRON_PICKAXE() : Pickaxe{
		if(!isset(self::$_mIRON_PICKAXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_PICKAXE);
	}

	public static function IRON_SHOVEL() : Shovel{
		if(!isset(self::$_mIRON_SHOVEL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_SHOVEL);
	}

	public static function IRON_SWORD() : Sword{
		if(!isset(self::$_mIRON_SWORD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mIRON_SWORD);
	}

	public static function JUNGLE_BOAT() : Boat{
		if(!isset(self::$_mJUNGLE_BOAT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mJUNGLE_BOAT);
	}

	public static function JUNGLE_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mJUNGLE_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mJUNGLE_HANGING_SIGN);
	}

	public static function JUNGLE_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mJUNGLE_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mJUNGLE_SIGN);
	}

	public static function LAPIS_LAZULI() : Item{
		if(!isset(self::$_mLAPIS_LAZULI)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mLAPIS_LAZULI);
	}

	public static function LAVA_BUCKET() : LiquidBucket{
		if(!isset(self::$_mLAVA_BUCKET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mLAVA_BUCKET);
	}

	public static function LEATHER() : Item{
		if(!isset(self::$_mLEATHER)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mLEATHER);
	}

	public static function LEATHER_BOOTS() : Armor{
		if(!isset(self::$_mLEATHER_BOOTS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mLEATHER_BOOTS);
	}

	public static function LEATHER_CAP() : Armor{
		if(!isset(self::$_mLEATHER_CAP)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mLEATHER_CAP);
	}

	public static function LEATHER_PANTS() : Armor{
		if(!isset(self::$_mLEATHER_PANTS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mLEATHER_PANTS);
	}

	public static function LEATHER_TUNIC() : Armor{
		if(!isset(self::$_mLEATHER_TUNIC)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mLEATHER_TUNIC);
	}

	public static function LINGERING_POTION() : SplashPotion{
		if(!isset(self::$_mLINGERING_POTION)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mLINGERING_POTION);
	}

	public static function MAGMA_CREAM() : Item{
		if(!isset(self::$_mMAGMA_CREAM)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMAGMA_CREAM);
	}

	public static function MANGROVE_BOAT() : Boat{
		if(!isset(self::$_mMANGROVE_BOAT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMANGROVE_BOAT);
	}

	public static function MANGROVE_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mMANGROVE_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMANGROVE_HANGING_SIGN);
	}

	public static function MANGROVE_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mMANGROVE_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMANGROVE_SIGN);
	}

	public static function MEDICINE() : Medicine{
		if(!isset(self::$_mMEDICINE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMEDICINE);
	}

	public static function MELON() : Melon{
		if(!isset(self::$_mMELON)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMELON);
	}

	public static function MELON_SEEDS() : MelonSeeds{
		if(!isset(self::$_mMELON_SEEDS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMELON_SEEDS);
	}

	public static function MILK_BUCKET() : MilkBucket{
		if(!isset(self::$_mMILK_BUCKET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMILK_BUCKET);
	}

	public static function MINECART() : Minecart{
		if(!isset(self::$_mMINECART)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMINECART);
	}

	public static function MUSHROOM_STEW() : MushroomStew{
		if(!isset(self::$_mMUSHROOM_STEW)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mMUSHROOM_STEW);
	}

	public static function NAME_TAG() : NameTag{
		if(!isset(self::$_mNAME_TAG)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNAME_TAG);
	}

	public static function NAUTILUS_SHELL() : Item{
		if(!isset(self::$_mNAUTILUS_SHELL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNAUTILUS_SHELL);
	}

	public static function NETHERITE_AXE() : Axe{
		if(!isset(self::$_mNETHERITE_AXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_AXE);
	}

	public static function NETHERITE_BOOTS() : Armor{
		if(!isset(self::$_mNETHERITE_BOOTS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_BOOTS);
	}

	public static function NETHERITE_CHESTPLATE() : Armor{
		if(!isset(self::$_mNETHERITE_CHESTPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_CHESTPLATE);
	}

	public static function NETHERITE_HELMET() : Armor{
		if(!isset(self::$_mNETHERITE_HELMET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_HELMET);
	}

	public static function NETHERITE_HOE() : Hoe{
		if(!isset(self::$_mNETHERITE_HOE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_HOE);
	}

	public static function NETHERITE_INGOT() : Item{
		if(!isset(self::$_mNETHERITE_INGOT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_INGOT);
	}

	public static function NETHERITE_LEGGINGS() : Armor{
		if(!isset(self::$_mNETHERITE_LEGGINGS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_LEGGINGS);
	}

	public static function NETHERITE_PICKAXE() : Pickaxe{
		if(!isset(self::$_mNETHERITE_PICKAXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_PICKAXE);
	}

	public static function NETHERITE_SCRAP() : Item{
		if(!isset(self::$_mNETHERITE_SCRAP)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_SCRAP);
	}

	public static function NETHERITE_SHOVEL() : Shovel{
		if(!isset(self::$_mNETHERITE_SHOVEL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_SHOVEL);
	}

	public static function NETHERITE_SWORD() : Sword{
		if(!isset(self::$_mNETHERITE_SWORD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_SWORD);
	}

	public static function NETHERITE_UPGRADE_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mNETHERITE_UPGRADE_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHERITE_UPGRADE_SMITHING_TEMPLATE);
	}

	public static function NETHER_BRICK() : Item{
		if(!isset(self::$_mNETHER_BRICK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHER_BRICK);
	}

	public static function NETHER_QUARTZ() : Item{
		if(!isset(self::$_mNETHER_QUARTZ)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHER_QUARTZ);
	}

	public static function NETHER_STAR() : Item{
		if(!isset(self::$_mNETHER_STAR)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mNETHER_STAR);
	}

	public static function OAK_BOAT() : Boat{
		if(!isset(self::$_mOAK_BOAT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mOAK_BOAT);
	}

	public static function OAK_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mOAK_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mOAK_HANGING_SIGN);
	}

	public static function OAK_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mOAK_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mOAK_SIGN);
	}

	public static function OMINOUS_BANNER() : ItemBlockWallOrFloor{
		if(!isset(self::$_mOMINOUS_BANNER)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mOMINOUS_BANNER);
	}

	public static function PAINTING() : PaintingItem{
		if(!isset(self::$_mPAINTING)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPAINTING);
	}

	public static function PALE_OAK_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mPALE_OAK_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPALE_OAK_HANGING_SIGN);
	}

	public static function PALE_OAK_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mPALE_OAK_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPALE_OAK_SIGN);
	}

	public static function PAPER() : Item{
		if(!isset(self::$_mPAPER)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPAPER);
	}

	public static function PHANTOM_MEMBRANE() : Item{
		if(!isset(self::$_mPHANTOM_MEMBRANE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPHANTOM_MEMBRANE);
	}

	public static function PITCHER_POD() : PitcherPod{
		if(!isset(self::$_mPITCHER_POD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPITCHER_POD);
	}

	public static function POISONOUS_POTATO() : PoisonousPotato{
		if(!isset(self::$_mPOISONOUS_POTATO)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPOISONOUS_POTATO);
	}

	public static function POPPED_CHORUS_FRUIT() : Item{
		if(!isset(self::$_mPOPPED_CHORUS_FRUIT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPOPPED_CHORUS_FRUIT);
	}

	public static function POTATO() : Potato{
		if(!isset(self::$_mPOTATO)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPOTATO);
	}

	public static function POTION() : Potion{
		if(!isset(self::$_mPOTION)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPOTION);
	}

	public static function PRISMARINE_CRYSTALS() : Item{
		if(!isset(self::$_mPRISMARINE_CRYSTALS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPRISMARINE_CRYSTALS);
	}

	public static function PRISMARINE_SHARD() : Item{
		if(!isset(self::$_mPRISMARINE_SHARD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPRISMARINE_SHARD);
	}

	public static function PUFFERFISH() : Pufferfish{
		if(!isset(self::$_mPUFFERFISH)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPUFFERFISH);
	}

	public static function PUMPKIN_PIE() : PumpkinPie{
		if(!isset(self::$_mPUMPKIN_PIE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPUMPKIN_PIE);
	}

	public static function PUMPKIN_SEEDS() : PumpkinSeeds{
		if(!isset(self::$_mPUMPKIN_SEEDS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mPUMPKIN_SEEDS);
	}

	public static function RABBIT_FOOT() : Item{
		if(!isset(self::$_mRABBIT_FOOT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRABBIT_FOOT);
	}

	public static function RABBIT_HIDE() : Item{
		if(!isset(self::$_mRABBIT_HIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRABBIT_HIDE);
	}

	public static function RABBIT_STEW() : RabbitStew{
		if(!isset(self::$_mRABBIT_STEW)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRABBIT_STEW);
	}

	public static function RAISER_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mRAISER_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAISER_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function RAW_BEEF() : RawBeef{
		if(!isset(self::$_mRAW_BEEF)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_BEEF);
	}

	public static function RAW_CHICKEN() : RawChicken{
		if(!isset(self::$_mRAW_CHICKEN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_CHICKEN);
	}

	public static function RAW_COPPER() : Item{
		if(!isset(self::$_mRAW_COPPER)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_COPPER);
	}

	public static function RAW_FISH() : RawFish{
		if(!isset(self::$_mRAW_FISH)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_FISH);
	}

	public static function RAW_GOLD() : Item{
		if(!isset(self::$_mRAW_GOLD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_GOLD);
	}

	public static function RAW_IRON() : Item{
		if(!isset(self::$_mRAW_IRON)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_IRON);
	}

	public static function RAW_MUTTON() : RawMutton{
		if(!isset(self::$_mRAW_MUTTON)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_MUTTON);
	}

	public static function RAW_PORKCHOP() : RawPorkchop{
		if(!isset(self::$_mRAW_PORKCHOP)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_PORKCHOP);
	}

	public static function RAW_RABBIT() : RawRabbit{
		if(!isset(self::$_mRAW_RABBIT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_RABBIT);
	}

	public static function RAW_SALMON() : RawSalmon{
		if(!isset(self::$_mRAW_SALMON)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRAW_SALMON);
	}

	public static function RECORD_11() : Record{
		if(!isset(self::$_mRECORD_11)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_11);
	}

	public static function RECORD_13() : Record{
		if(!isset(self::$_mRECORD_13)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_13);
	}

	public static function RECORD_5() : Record{
		if(!isset(self::$_mRECORD_5)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_5);
	}

	public static function RECORD_BLOCKS() : Record{
		if(!isset(self::$_mRECORD_BLOCKS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_BLOCKS);
	}

	public static function RECORD_CAT() : Record{
		if(!isset(self::$_mRECORD_CAT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_CAT);
	}

	public static function RECORD_CHIRP() : Record{
		if(!isset(self::$_mRECORD_CHIRP)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_CHIRP);
	}

	public static function RECORD_CREATOR() : Record{
		if(!isset(self::$_mRECORD_CREATOR)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_CREATOR);
	}

	public static function RECORD_CREATOR_MUSIC_BOX() : Record{
		if(!isset(self::$_mRECORD_CREATOR_MUSIC_BOX)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_CREATOR_MUSIC_BOX);
	}

	public static function RECORD_FAR() : Record{
		if(!isset(self::$_mRECORD_FAR)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_FAR);
	}

	public static function RECORD_MALL() : Record{
		if(!isset(self::$_mRECORD_MALL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_MALL);
	}

	public static function RECORD_MELLOHI() : Record{
		if(!isset(self::$_mRECORD_MELLOHI)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_MELLOHI);
	}

	public static function RECORD_OTHERSIDE() : Record{
		if(!isset(self::$_mRECORD_OTHERSIDE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_OTHERSIDE);
	}

	public static function RECORD_PIGSTEP() : Record{
		if(!isset(self::$_mRECORD_PIGSTEP)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_PIGSTEP);
	}

	public static function RECORD_PRECIPICE() : Record{
		if(!isset(self::$_mRECORD_PRECIPICE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_PRECIPICE);
	}

	public static function RECORD_RELIC() : Record{
		if(!isset(self::$_mRECORD_RELIC)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_RELIC);
	}

	public static function RECORD_STAL() : Record{
		if(!isset(self::$_mRECORD_STAL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_STAL);
	}

	public static function RECORD_STRAD() : Record{
		if(!isset(self::$_mRECORD_STRAD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_STRAD);
	}

	public static function RECORD_WAIT() : Record{
		if(!isset(self::$_mRECORD_WAIT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_WAIT);
	}

	public static function RECORD_WARD() : Record{
		if(!isset(self::$_mRECORD_WARD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECORD_WARD);
	}

	public static function RECOVERY_COMPASS() : Item{
		if(!isset(self::$_mRECOVERY_COMPASS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRECOVERY_COMPASS);
	}

	public static function REDSTONE_DUST() : Redstone{
		if(!isset(self::$_mREDSTONE_DUST)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mREDSTONE_DUST);
	}

	public static function RESIN_BRICK() : Item{
		if(!isset(self::$_mRESIN_BRICK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRESIN_BRICK);
	}

	public static function RIB_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mRIB_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mRIB_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function ROTTEN_FLESH() : RottenFlesh{
		if(!isset(self::$_mROTTEN_FLESH)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mROTTEN_FLESH);
	}

	public static function SCUTE() : Item{
		if(!isset(self::$_mSCUTE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSCUTE);
	}

	public static function SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSENTRY_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSENTRY_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSHAPER_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSHAPER_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function SHEARS() : Shears{
		if(!isset(self::$_mSHEARS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSHEARS);
	}

	public static function SHULKER_SHELL() : Item{
		if(!isset(self::$_mSHULKER_SHELL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSHULKER_SHELL);
	}

	public static function SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSILENCE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSILENCE_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function SLIMEBALL() : Item{
		if(!isset(self::$_mSLIMEBALL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSLIMEBALL);
	}

	public static function SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSNOUT_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSNOUT_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function SNOWBALL() : Snowball{
		if(!isset(self::$_mSNOWBALL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSNOWBALL);
	}

	public static function SPIDER_EYE() : SpiderEye{
		if(!isset(self::$_mSPIDER_EYE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSPIDER_EYE);
	}

	public static function SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mSPIRE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSPIRE_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function SPLASH_POTION() : SplashPotion{
		if(!isset(self::$_mSPLASH_POTION)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSPLASH_POTION);
	}

	public static function SPRUCE_BOAT() : Boat{
		if(!isset(self::$_mSPRUCE_BOAT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSPRUCE_BOAT);
	}

	public static function SPRUCE_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mSPRUCE_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSPRUCE_HANGING_SIGN);
	}

	public static function SPRUCE_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mSPRUCE_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSPRUCE_SIGN);
	}

	public static function SPYGLASS() : Spyglass{
		if(!isset(self::$_mSPYGLASS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSPYGLASS);
	}

	public static function SQUID_SPAWN_EGG() : SpawnEgg{
		if(!isset(self::$_mSQUID_SPAWN_EGG)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSQUID_SPAWN_EGG);
	}

	public static function STEAK() : Steak{
		if(!isset(self::$_mSTEAK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSTEAK);
	}

	public static function STICK() : Stick{
		if(!isset(self::$_mSTICK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSTICK);
	}

	public static function STONE_AXE() : Axe{
		if(!isset(self::$_mSTONE_AXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSTONE_AXE);
	}

	public static function STONE_HOE() : Hoe{
		if(!isset(self::$_mSTONE_HOE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSTONE_HOE);
	}

	public static function STONE_PICKAXE() : Pickaxe{
		if(!isset(self::$_mSTONE_PICKAXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSTONE_PICKAXE);
	}

	public static function STONE_SHOVEL() : Shovel{
		if(!isset(self::$_mSTONE_SHOVEL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSTONE_SHOVEL);
	}

	public static function STONE_SWORD() : Sword{
		if(!isset(self::$_mSTONE_SWORD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSTONE_SWORD);
	}

	public static function STRING() : StringItem{
		if(!isset(self::$_mSTRING)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSTRING);
	}

	public static function SUGAR() : Item{
		if(!isset(self::$_mSUGAR)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSUGAR);
	}

	public static function SUSPICIOUS_STEW() : SuspiciousStew{
		if(!isset(self::$_mSUSPICIOUS_STEW)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSUSPICIOUS_STEW);
	}

	public static function SWEET_BERRIES() : SweetBerries{
		if(!isset(self::$_mSWEET_BERRIES)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mSWEET_BERRIES);
	}

	public static function TIDE_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mTIDE_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mTIDE_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function TORCHFLOWER_SEEDS() : TorchflowerSeeds{
		if(!isset(self::$_mTORCHFLOWER_SEEDS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mTORCHFLOWER_SEEDS);
	}

	public static function TOTEM() : Totem{
		if(!isset(self::$_mTOTEM)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mTOTEM);
	}

	public static function TRIDENT() : Trident{
		if(!isset(self::$_mTRIDENT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mTRIDENT);
	}

	public static function TURTLE_HELMET() : TurtleHelmet{
		if(!isset(self::$_mTURTLE_HELMET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mTURTLE_HELMET);
	}

	public static function VEX_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mVEX_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mVEX_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function VILLAGER_SPAWN_EGG() : SpawnEgg{
		if(!isset(self::$_mVILLAGER_SPAWN_EGG)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mVILLAGER_SPAWN_EGG);
	}

	public static function WARD_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mWARD_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWARD_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function WARPED_HANGING_SIGN() : HangingSign{
		if(!isset(self::$_mWARPED_HANGING_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWARPED_HANGING_SIGN);
	}

	public static function WARPED_SIGN() : ItemBlockWallOrFloor{
		if(!isset(self::$_mWARPED_SIGN)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWARPED_SIGN);
	}

	public static function WATER_BUCKET() : LiquidBucket{
		if(!isset(self::$_mWATER_BUCKET)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWATER_BUCKET);
	}

	public static function WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mWAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function WHEAT() : Item{
		if(!isset(self::$_mWHEAT)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWHEAT);
	}

	public static function WHEAT_SEEDS() : WheatSeeds{
		if(!isset(self::$_mWHEAT_SEEDS)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWHEAT_SEEDS);
	}

	public static function WILD_ARMOR_TRIM_SMITHING_TEMPLATE() : Item{
		if(!isset(self::$_mWILD_ARMOR_TRIM_SMITHING_TEMPLATE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWILD_ARMOR_TRIM_SMITHING_TEMPLATE);
	}

	public static function WOODEN_AXE() : Axe{
		if(!isset(self::$_mWOODEN_AXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWOODEN_AXE);
	}

	public static function WOODEN_HOE() : Hoe{
		if(!isset(self::$_mWOODEN_HOE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWOODEN_HOE);
	}

	public static function WOODEN_PICKAXE() : Pickaxe{
		if(!isset(self::$_mWOODEN_PICKAXE)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWOODEN_PICKAXE);
	}

	public static function WOODEN_SHOVEL() : Shovel{
		if(!isset(self::$_mWOODEN_SHOVEL)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWOODEN_SHOVEL);
	}

	public static function WOODEN_SWORD() : Sword{
		if(!isset(self::$_mWOODEN_SWORD)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWOODEN_SWORD);
	}

	public static function WRITABLE_BOOK() : WritableBook{
		if(!isset(self::$_mWRITABLE_BOOK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWRITABLE_BOOK);
	}

	public static function WRITTEN_BOOK() : WrittenBook{
		if(!isset(self::$_mWRITTEN_BOOK)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mWRITTEN_BOOK);
	}

	public static function ZOMBIE_SPAWN_EGG() : SpawnEgg{
		if(!isset(self::$_mZOMBIE_SPAWN_EGG)){ self::init(); }
		return VanillaItemsInputs::cloneMember(self::$_mZOMBIE_SPAWN_EGG);
	}
}
