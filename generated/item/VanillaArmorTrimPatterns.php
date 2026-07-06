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

use function array_keys;
use function count;
use function implode;
use function mb_strtoupper;

/**
 * This class is generated automatically from source class {@link VanillaArmorTrimPatternsInputs}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/codegen/registry-interface.php
 */
final class VanillaArmorTrimPatterns{
	private static ArmorTrimPattern $_mCOAST;
	private static ArmorTrimPattern $_mDUNE;
	private static ArmorTrimPattern $_mEYE;
	private static ArmorTrimPattern $_mHOST;
	private static ArmorTrimPattern $_mRAISER;
	private static ArmorTrimPattern $_mRIB;
	private static ArmorTrimPattern $_mSENTRY;
	private static ArmorTrimPattern $_mSHAPER;
	private static ArmorTrimPattern $_mSILENCE;
	private static ArmorTrimPattern $_mSNOUT;
	private static ArmorTrimPattern $_mSPIRE;
	private static ArmorTrimPattern $_mTIDE;
	private static ArmorTrimPattern $_mVEX;
	private static ArmorTrimPattern $_mWARD;
	private static ArmorTrimPattern $_mWAYFINDER;
	private static ArmorTrimPattern $_mWILD;

	/**
	 * @var ArmorTrimPattern[]
	 * @phpstan-var array<string, ArmorTrimPattern>
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
	 * @phpstan-param \Closure(never) : ArmorTrimPattern $closure
	 */
	private static function unsafeAssign(\Closure $closure, ArmorTrimPattern $memberValue) : void{
		/**
		 * This type is not correct either (the param is actually a subtype of ArmorTrimPattern) but it's called
		 * unsafeAssign for a reason :)
		 * @phpstan-var \Closure(ArmorTrimPattern) : ArmorTrimPattern $closure
		 */
		$closure($memberValue);
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return array<string, \Closure(never) : ArmorTrimPattern>
	 */
	private static function getInitAssigners() : array{
		return [
			"coast" => fn(ArmorTrimPattern $v) => self::$_mCOAST = $v,
			"dune" => fn(ArmorTrimPattern $v) => self::$_mDUNE = $v,
			"eye" => fn(ArmorTrimPattern $v) => self::$_mEYE = $v,
			"host" => fn(ArmorTrimPattern $v) => self::$_mHOST = $v,
			"raiser" => fn(ArmorTrimPattern $v) => self::$_mRAISER = $v,
			"rib" => fn(ArmorTrimPattern $v) => self::$_mRIB = $v,
			"sentry" => fn(ArmorTrimPattern $v) => self::$_mSENTRY = $v,
			"shaper" => fn(ArmorTrimPattern $v) => self::$_mSHAPER = $v,
			"silence" => fn(ArmorTrimPattern $v) => self::$_mSILENCE = $v,
			"snout" => fn(ArmorTrimPattern $v) => self::$_mSNOUT = $v,
			"spire" => fn(ArmorTrimPattern $v) => self::$_mSPIRE = $v,
			"tide" => fn(ArmorTrimPattern $v) => self::$_mTIDE = $v,
			"vex" => fn(ArmorTrimPattern $v) => self::$_mVEX = $v,
			"ward" => fn(ArmorTrimPattern $v) => self::$_mWARD = $v,
			"wayfinder" => fn(ArmorTrimPattern $v) => self::$_mWAYFINDER = $v,
			"wild" => fn(ArmorTrimPattern $v) => self::$_mWILD = $v,
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
		$source = new VanillaArmorTrimPatternsInputs();
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
	 * @return ArmorTrimPattern[]
	 * @phpstan-return array<string, ArmorTrimPattern>
	 */
	public static function getAll() : array{
		if(!isset(self::$members)){ self::init(); }
		return self::$members;
	}

	public static function COAST() : ArmorTrimPattern{
		if(!isset(self::$_mCOAST)){ self::init(); }
		return self::$_mCOAST;
	}

	public static function DUNE() : ArmorTrimPattern{
		if(!isset(self::$_mDUNE)){ self::init(); }
		return self::$_mDUNE;
	}

	public static function EYE() : ArmorTrimPattern{
		if(!isset(self::$_mEYE)){ self::init(); }
		return self::$_mEYE;
	}

	public static function HOST() : ArmorTrimPattern{
		if(!isset(self::$_mHOST)){ self::init(); }
		return self::$_mHOST;
	}

	public static function RAISER() : ArmorTrimPattern{
		if(!isset(self::$_mRAISER)){ self::init(); }
		return self::$_mRAISER;
	}

	public static function RIB() : ArmorTrimPattern{
		if(!isset(self::$_mRIB)){ self::init(); }
		return self::$_mRIB;
	}

	public static function SENTRY() : ArmorTrimPattern{
		if(!isset(self::$_mSENTRY)){ self::init(); }
		return self::$_mSENTRY;
	}

	public static function SHAPER() : ArmorTrimPattern{
		if(!isset(self::$_mSHAPER)){ self::init(); }
		return self::$_mSHAPER;
	}

	public static function SILENCE() : ArmorTrimPattern{
		if(!isset(self::$_mSILENCE)){ self::init(); }
		return self::$_mSILENCE;
	}

	public static function SNOUT() : ArmorTrimPattern{
		if(!isset(self::$_mSNOUT)){ self::init(); }
		return self::$_mSNOUT;
	}

	public static function SPIRE() : ArmorTrimPattern{
		if(!isset(self::$_mSPIRE)){ self::init(); }
		return self::$_mSPIRE;
	}

	public static function TIDE() : ArmorTrimPattern{
		if(!isset(self::$_mTIDE)){ self::init(); }
		return self::$_mTIDE;
	}

	public static function VEX() : ArmorTrimPattern{
		if(!isset(self::$_mVEX)){ self::init(); }
		return self::$_mVEX;
	}

	public static function WARD() : ArmorTrimPattern{
		if(!isset(self::$_mWARD)){ self::init(); }
		return self::$_mWARD;
	}

	public static function WAYFINDER() : ArmorTrimPattern{
		if(!isset(self::$_mWAYFINDER)){ self::init(); }
		return self::$_mWAYFINDER;
	}

	public static function WILD() : ArmorTrimPattern{
		if(!isset(self::$_mWILD)){ self::init(); }
		return self::$_mWILD;
	}
}
