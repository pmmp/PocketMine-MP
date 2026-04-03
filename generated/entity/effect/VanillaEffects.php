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

namespace pocketmine\entity\effect;

use function array_keys;
use function count;
use function implode;
use function mb_strtoupper;

/**
 * Allows getting any vanilla entity effect implemented by PocketMine-MP
 *
 * This class is generated automatically from source class {@link VanillaEffectsInputs}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/codegen/registry-interface.php
 */
final class VanillaEffects{
	private static AbsorptionEffect $_mABSORPTION;
	private static Effect $_mBLINDNESS;
	private static Effect $_mCONDUIT_POWER;
	private static Effect $_mDARKNESS;
	private static PoisonEffect $_mFATAL_POISON;
	private static Effect $_mFIRE_RESISTANCE;
	private static Effect $_mHASTE;
	private static HealthBoostEffect $_mHEALTH_BOOST;
	private static HungerEffect $_mHUNGER;
	private static InstantDamageEffect $_mINSTANT_DAMAGE;
	private static InstantHealthEffect $_mINSTANT_HEALTH;
	private static InvisibilityEffect $_mINVISIBILITY;
	private static Effect $_mJUMP_BOOST;
	private static LevitationEffect $_mLEVITATION;
	private static Effect $_mMINING_FATIGUE;
	private static Effect $_mNAUSEA;
	private static Effect $_mNIGHT_VISION;
	private static PoisonEffect $_mPOISON;
	private static RegenerationEffect $_mREGENERATION;
	private static Effect $_mRESISTANCE;
	private static SaturationEffect $_mSATURATION;
	private static SlownessEffect $_mSLOWNESS;
	private static SpeedEffect $_mSPEED;
	private static Effect $_mSTRENGTH;
	private static Effect $_mWATER_BREATHING;
	private static Effect $_mWEAKNESS;
	private static WitherEffect $_mWITHER;

	/**
	 * @var Effect[]
	 * @phpstan-var array<string, Effect>
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
	 * @phpstan-param \Closure(never) : Effect $closure
	 */
	private static function unsafeAssign(\Closure $closure, Effect $memberValue) : void{
		/**
		 * This type is not correct either (the param is actually a subtype of Effect) but it's called
		 * unsafeAssign for a reason :)
		 * @phpstan-var \Closure(Effect) : Effect $closure
		 */
		$closure($memberValue);
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return array<string, \Closure(never) : Effect>
	 */
	private static function getInitAssigners() : array{
		return [
			"absorption" => fn(AbsorptionEffect $v) => self::$_mABSORPTION = $v,
			"blindness" => fn(Effect $v) => self::$_mBLINDNESS = $v,
			"conduit_power" => fn(Effect $v) => self::$_mCONDUIT_POWER = $v,
			"darkness" => fn(Effect $v) => self::$_mDARKNESS = $v,
			"fatal_poison" => fn(PoisonEffect $v) => self::$_mFATAL_POISON = $v,
			"fire_resistance" => fn(Effect $v) => self::$_mFIRE_RESISTANCE = $v,
			"haste" => fn(Effect $v) => self::$_mHASTE = $v,
			"health_boost" => fn(HealthBoostEffect $v) => self::$_mHEALTH_BOOST = $v,
			"hunger" => fn(HungerEffect $v) => self::$_mHUNGER = $v,
			"instant_damage" => fn(InstantDamageEffect $v) => self::$_mINSTANT_DAMAGE = $v,
			"instant_health" => fn(InstantHealthEffect $v) => self::$_mINSTANT_HEALTH = $v,
			"invisibility" => fn(InvisibilityEffect $v) => self::$_mINVISIBILITY = $v,
			"jump_boost" => fn(Effect $v) => self::$_mJUMP_BOOST = $v,
			"levitation" => fn(LevitationEffect $v) => self::$_mLEVITATION = $v,
			"mining_fatigue" => fn(Effect $v) => self::$_mMINING_FATIGUE = $v,
			"nausea" => fn(Effect $v) => self::$_mNAUSEA = $v,
			"night_vision" => fn(Effect $v) => self::$_mNIGHT_VISION = $v,
			"poison" => fn(PoisonEffect $v) => self::$_mPOISON = $v,
			"regeneration" => fn(RegenerationEffect $v) => self::$_mREGENERATION = $v,
			"resistance" => fn(Effect $v) => self::$_mRESISTANCE = $v,
			"saturation" => fn(SaturationEffect $v) => self::$_mSATURATION = $v,
			"slowness" => fn(SlownessEffect $v) => self::$_mSLOWNESS = $v,
			"speed" => fn(SpeedEffect $v) => self::$_mSPEED = $v,
			"strength" => fn(Effect $v) => self::$_mSTRENGTH = $v,
			"water_breathing" => fn(Effect $v) => self::$_mWATER_BREATHING = $v,
			"weakness" => fn(Effect $v) => self::$_mWEAKNESS = $v,
			"wither" => fn(WitherEffect $v) => self::$_mWITHER = $v,
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
		$source = new VanillaEffectsInputs();
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
	 * @return Effect[]
	 * @phpstan-return array<string, Effect>
	 */
	public static function getAll() : array{
		if(!isset(self::$members)){ self::init(); }
		return self::$members;
	}

	public static function ABSORPTION() : AbsorptionEffect{
		if(!isset(self::$_mABSORPTION)){ self::init(); }
		return self::$_mABSORPTION;
	}

	public static function BLINDNESS() : Effect{
		if(!isset(self::$_mBLINDNESS)){ self::init(); }
		return self::$_mBLINDNESS;
	}

	public static function CONDUIT_POWER() : Effect{
		if(!isset(self::$_mCONDUIT_POWER)){ self::init(); }
		return self::$_mCONDUIT_POWER;
	}

	public static function DARKNESS() : Effect{
		if(!isset(self::$_mDARKNESS)){ self::init(); }
		return self::$_mDARKNESS;
	}

	public static function FATAL_POISON() : PoisonEffect{
		if(!isset(self::$_mFATAL_POISON)){ self::init(); }
		return self::$_mFATAL_POISON;
	}

	public static function FIRE_RESISTANCE() : Effect{
		if(!isset(self::$_mFIRE_RESISTANCE)){ self::init(); }
		return self::$_mFIRE_RESISTANCE;
	}

	public static function HASTE() : Effect{
		if(!isset(self::$_mHASTE)){ self::init(); }
		return self::$_mHASTE;
	}

	public static function HEALTH_BOOST() : HealthBoostEffect{
		if(!isset(self::$_mHEALTH_BOOST)){ self::init(); }
		return self::$_mHEALTH_BOOST;
	}

	public static function HUNGER() : HungerEffect{
		if(!isset(self::$_mHUNGER)){ self::init(); }
		return self::$_mHUNGER;
	}

	public static function INSTANT_DAMAGE() : InstantDamageEffect{
		if(!isset(self::$_mINSTANT_DAMAGE)){ self::init(); }
		return self::$_mINSTANT_DAMAGE;
	}

	public static function INSTANT_HEALTH() : InstantHealthEffect{
		if(!isset(self::$_mINSTANT_HEALTH)){ self::init(); }
		return self::$_mINSTANT_HEALTH;
	}

	public static function INVISIBILITY() : InvisibilityEffect{
		if(!isset(self::$_mINVISIBILITY)){ self::init(); }
		return self::$_mINVISIBILITY;
	}

	public static function JUMP_BOOST() : Effect{
		if(!isset(self::$_mJUMP_BOOST)){ self::init(); }
		return self::$_mJUMP_BOOST;
	}

	public static function LEVITATION() : LevitationEffect{
		if(!isset(self::$_mLEVITATION)){ self::init(); }
		return self::$_mLEVITATION;
	}

	public static function MINING_FATIGUE() : Effect{
		if(!isset(self::$_mMINING_FATIGUE)){ self::init(); }
		return self::$_mMINING_FATIGUE;
	}

	public static function NAUSEA() : Effect{
		if(!isset(self::$_mNAUSEA)){ self::init(); }
		return self::$_mNAUSEA;
	}

	public static function NIGHT_VISION() : Effect{
		if(!isset(self::$_mNIGHT_VISION)){ self::init(); }
		return self::$_mNIGHT_VISION;
	}

	public static function POISON() : PoisonEffect{
		if(!isset(self::$_mPOISON)){ self::init(); }
		return self::$_mPOISON;
	}

	public static function REGENERATION() : RegenerationEffect{
		if(!isset(self::$_mREGENERATION)){ self::init(); }
		return self::$_mREGENERATION;
	}

	public static function RESISTANCE() : Effect{
		if(!isset(self::$_mRESISTANCE)){ self::init(); }
		return self::$_mRESISTANCE;
	}

	public static function SATURATION() : SaturationEffect{
		if(!isset(self::$_mSATURATION)){ self::init(); }
		return self::$_mSATURATION;
	}

	public static function SLOWNESS() : SlownessEffect{
		if(!isset(self::$_mSLOWNESS)){ self::init(); }
		return self::$_mSLOWNESS;
	}

	public static function SPEED() : SpeedEffect{
		if(!isset(self::$_mSPEED)){ self::init(); }
		return self::$_mSPEED;
	}

	public static function STRENGTH() : Effect{
		if(!isset(self::$_mSTRENGTH)){ self::init(); }
		return self::$_mSTRENGTH;
	}

	public static function WATER_BREATHING() : Effect{
		if(!isset(self::$_mWATER_BREATHING)){ self::init(); }
		return self::$_mWATER_BREATHING;
	}

	public static function WEAKNESS() : Effect{
		if(!isset(self::$_mWEAKNESS)){ self::init(); }
		return self::$_mWEAKNESS;
	}

	public static function WITHER() : WitherEffect{
		if(!isset(self::$_mWITHER)){ self::init(); }
		return self::$_mWITHER;
	}
}
