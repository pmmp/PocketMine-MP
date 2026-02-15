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

namespace pocketmine\item\enchantment;

use function array_keys;
use function count;
use function implode;
use function mb_strtoupper;

/**
 * Allows getting any vanilla enchantment implemented by PocketMine-MP
 *
 * This class is generated automatically from source class {@link VanillaEnchantmentsInputs}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/codegen/registry-interface.php
 */
final class VanillaEnchantments{
	private static Enchantment $_mAQUA_AFFINITY;
	private static ProtectionEnchantment $_mBLAST_PROTECTION;
	private static Enchantment $_mEFFICIENCY;
	private static ProtectionEnchantment $_mFEATHER_FALLING;
	private static FireAspectEnchantment $_mFIRE_ASPECT;
	private static ProtectionEnchantment $_mFIRE_PROTECTION;
	private static Enchantment $_mFLAME;
	private static Enchantment $_mFORTUNE;
	private static Enchantment $_mFROST_WALKER;
	private static Enchantment $_mINFINITY;
	private static KnockbackEnchantment $_mKNOCKBACK;
	private static Enchantment $_mMENDING;
	private static Enchantment $_mPOWER;
	private static ProtectionEnchantment $_mPROJECTILE_PROTECTION;
	private static ProtectionEnchantment $_mPROTECTION;
	private static Enchantment $_mPUNCH;
	private static Enchantment $_mRESPIRATION;
	private static SharpnessEnchantment $_mSHARPNESS;
	private static Enchantment $_mSILK_TOUCH;
	private static Enchantment $_mSWIFT_SNEAK;
	private static Enchantment $_mTHORNS;
	private static Enchantment $_mUNBREAKING;
	private static Enchantment $_mVANISHING;

	/**
	 * @var Enchantment[]
	 * @phpstan-var array<string, Enchantment>
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
	 * @phpstan-param \Closure(never) : Enchantment $closure
	 */
	private static function unsafeAssign(\Closure $closure, Enchantment $memberValue) : void{
		/**
		 * This type is not correct either (the param is actually a subtype of Enchantment) but it's called
		 * unsafeAssign for a reason :)
		 * @phpstan-var \Closure(Enchantment) : Enchantment $closure
		 */
		$closure($memberValue);
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return array<string, \Closure(never) : Enchantment>
	 */
	private static function getInitAssigners() : array{
		return [
			"AQUA_AFFINITY" => fn(Enchantment $v) => self::$_mAQUA_AFFINITY = $v,
			"BLAST_PROTECTION" => fn(ProtectionEnchantment $v) => self::$_mBLAST_PROTECTION = $v,
			"EFFICIENCY" => fn(Enchantment $v) => self::$_mEFFICIENCY = $v,
			"FEATHER_FALLING" => fn(ProtectionEnchantment $v) => self::$_mFEATHER_FALLING = $v,
			"FIRE_ASPECT" => fn(FireAspectEnchantment $v) => self::$_mFIRE_ASPECT = $v,
			"FIRE_PROTECTION" => fn(ProtectionEnchantment $v) => self::$_mFIRE_PROTECTION = $v,
			"FLAME" => fn(Enchantment $v) => self::$_mFLAME = $v,
			"FORTUNE" => fn(Enchantment $v) => self::$_mFORTUNE = $v,
			"FROST_WALKER" => fn(Enchantment $v) => self::$_mFROST_WALKER = $v,
			"INFINITY" => fn(Enchantment $v) => self::$_mINFINITY = $v,
			"KNOCKBACK" => fn(KnockbackEnchantment $v) => self::$_mKNOCKBACK = $v,
			"MENDING" => fn(Enchantment $v) => self::$_mMENDING = $v,
			"POWER" => fn(Enchantment $v) => self::$_mPOWER = $v,
			"PROJECTILE_PROTECTION" => fn(ProtectionEnchantment $v) => self::$_mPROJECTILE_PROTECTION = $v,
			"PROTECTION" => fn(ProtectionEnchantment $v) => self::$_mPROTECTION = $v,
			"PUNCH" => fn(Enchantment $v) => self::$_mPUNCH = $v,
			"RESPIRATION" => fn(Enchantment $v) => self::$_mRESPIRATION = $v,
			"SHARPNESS" => fn(SharpnessEnchantment $v) => self::$_mSHARPNESS = $v,
			"SILK_TOUCH" => fn(Enchantment $v) => self::$_mSILK_TOUCH = $v,
			"SWIFT_SNEAK" => fn(Enchantment $v) => self::$_mSWIFT_SNEAK = $v,
			"THORNS" => fn(Enchantment $v) => self::$_mTHORNS = $v,
			"UNBREAKING" => fn(Enchantment $v) => self::$_mUNBREAKING = $v,
			"VANISHING" => fn(Enchantment $v) => self::$_mVANISHING = $v,
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
		$source = new VanillaEnchantmentsInputs();
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
	 * @return Enchantment[]
	 * @phpstan-return array<string, Enchantment>
	 */
	public static function getAll() : array{
		if(!isset(self::$members)){ self::init(); }
		return self::$members;
	}

	public static function AQUA_AFFINITY() : Enchantment{
		if(!isset(self::$_mAQUA_AFFINITY)){ self::init(); }
		return self::$_mAQUA_AFFINITY;
	}

	public static function BLAST_PROTECTION() : ProtectionEnchantment{
		if(!isset(self::$_mBLAST_PROTECTION)){ self::init(); }
		return self::$_mBLAST_PROTECTION;
	}

	public static function EFFICIENCY() : Enchantment{
		if(!isset(self::$_mEFFICIENCY)){ self::init(); }
		return self::$_mEFFICIENCY;
	}

	public static function FEATHER_FALLING() : ProtectionEnchantment{
		if(!isset(self::$_mFEATHER_FALLING)){ self::init(); }
		return self::$_mFEATHER_FALLING;
	}

	public static function FIRE_ASPECT() : FireAspectEnchantment{
		if(!isset(self::$_mFIRE_ASPECT)){ self::init(); }
		return self::$_mFIRE_ASPECT;
	}

	public static function FIRE_PROTECTION() : ProtectionEnchantment{
		if(!isset(self::$_mFIRE_PROTECTION)){ self::init(); }
		return self::$_mFIRE_PROTECTION;
	}

	public static function FLAME() : Enchantment{
		if(!isset(self::$_mFLAME)){ self::init(); }
		return self::$_mFLAME;
	}

	public static function FORTUNE() : Enchantment{
		if(!isset(self::$_mFORTUNE)){ self::init(); }
		return self::$_mFORTUNE;
	}

	public static function FROST_WALKER() : Enchantment{
		if(!isset(self::$_mFROST_WALKER)){ self::init(); }
		return self::$_mFROST_WALKER;
	}

	public static function INFINITY() : Enchantment{
		if(!isset(self::$_mINFINITY)){ self::init(); }
		return self::$_mINFINITY;
	}

	public static function KNOCKBACK() : KnockbackEnchantment{
		if(!isset(self::$_mKNOCKBACK)){ self::init(); }
		return self::$_mKNOCKBACK;
	}

	public static function MENDING() : Enchantment{
		if(!isset(self::$_mMENDING)){ self::init(); }
		return self::$_mMENDING;
	}

	public static function POWER() : Enchantment{
		if(!isset(self::$_mPOWER)){ self::init(); }
		return self::$_mPOWER;
	}

	public static function PROJECTILE_PROTECTION() : ProtectionEnchantment{
		if(!isset(self::$_mPROJECTILE_PROTECTION)){ self::init(); }
		return self::$_mPROJECTILE_PROTECTION;
	}

	public static function PROTECTION() : ProtectionEnchantment{
		if(!isset(self::$_mPROTECTION)){ self::init(); }
		return self::$_mPROTECTION;
	}

	public static function PUNCH() : Enchantment{
		if(!isset(self::$_mPUNCH)){ self::init(); }
		return self::$_mPUNCH;
	}

	public static function RESPIRATION() : Enchantment{
		if(!isset(self::$_mRESPIRATION)){ self::init(); }
		return self::$_mRESPIRATION;
	}

	public static function SHARPNESS() : SharpnessEnchantment{
		if(!isset(self::$_mSHARPNESS)){ self::init(); }
		return self::$_mSHARPNESS;
	}

	public static function SILK_TOUCH() : Enchantment{
		if(!isset(self::$_mSILK_TOUCH)){ self::init(); }
		return self::$_mSILK_TOUCH;
	}

	public static function SWIFT_SNEAK() : Enchantment{
		if(!isset(self::$_mSWIFT_SNEAK)){ self::init(); }
		return self::$_mSWIFT_SNEAK;
	}

	public static function THORNS() : Enchantment{
		if(!isset(self::$_mTHORNS)){ self::init(); }
		return self::$_mTHORNS;
	}

	public static function UNBREAKING() : Enchantment{
		if(!isset(self::$_mUNBREAKING)){ self::init(); }
		return self::$_mUNBREAKING;
	}

	public static function VANISHING() : Enchantment{
		if(!isset(self::$_mVANISHING)){ self::init(); }
		return self::$_mVANISHING;
	}
}
