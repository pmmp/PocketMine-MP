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
 * This class is generated automatically from source class {@link VanillaArmorTrimMaterialsInputs}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/codegen/registry-interface.php
 */
final class VanillaArmorTrimMaterials{
	private static ArmorTrimMaterial $_mAMETHYST;
	private static ArmorTrimMaterial $_mCOPPER;
	private static ArmorTrimMaterial $_mDIAMOND;
	private static ArmorTrimMaterial $_mEMERALD;
	private static ArmorTrimMaterial $_mGOLD;
	private static ArmorTrimMaterial $_mIRON;
	private static ArmorTrimMaterial $_mLAPIS;
	private static ArmorTrimMaterial $_mNETHERITE;
	private static ArmorTrimMaterial $_mQUARTZ;
	private static ArmorTrimMaterial $_mREDSTONE;
	private static ArmorTrimMaterial $_mRESIN;

	/**
	 * @var ArmorTrimMaterial[]
	 * @phpstan-var array<string, ArmorTrimMaterial>
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
	 * @phpstan-param \Closure(never) : ArmorTrimMaterial $closure
	 */
	private static function unsafeAssign(\Closure $closure, ArmorTrimMaterial $memberValue) : void{
		/**
		 * This type is not correct either (the param is actually a subtype of ArmorTrimMaterial) but it's called
		 * unsafeAssign for a reason :)
		 * @phpstan-var \Closure(ArmorTrimMaterial) : ArmorTrimMaterial $closure
		 */
		$closure($memberValue);
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return array<string, \Closure(never) : ArmorTrimMaterial>
	 */
	private static function getInitAssigners() : array{
		return [
			"amethyst" => fn(ArmorTrimMaterial $v) => self::$_mAMETHYST = $v,
			"copper" => fn(ArmorTrimMaterial $v) => self::$_mCOPPER = $v,
			"diamond" => fn(ArmorTrimMaterial $v) => self::$_mDIAMOND = $v,
			"emerald" => fn(ArmorTrimMaterial $v) => self::$_mEMERALD = $v,
			"gold" => fn(ArmorTrimMaterial $v) => self::$_mGOLD = $v,
			"iron" => fn(ArmorTrimMaterial $v) => self::$_mIRON = $v,
			"lapis" => fn(ArmorTrimMaterial $v) => self::$_mLAPIS = $v,
			"netherite" => fn(ArmorTrimMaterial $v) => self::$_mNETHERITE = $v,
			"quartz" => fn(ArmorTrimMaterial $v) => self::$_mQUARTZ = $v,
			"redstone" => fn(ArmorTrimMaterial $v) => self::$_mREDSTONE = $v,
			"resin" => fn(ArmorTrimMaterial $v) => self::$_mRESIN = $v,
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
		$source = new VanillaArmorTrimMaterialsInputs();
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
	 * @return ArmorTrimMaterial[]
	 * @phpstan-return array<string, ArmorTrimMaterial>
	 */
	public static function getAll() : array{
		if(!isset(self::$members)){ self::init(); }
		return self::$members;
	}

	public static function AMETHYST() : ArmorTrimMaterial{
		if(!isset(self::$_mAMETHYST)){ self::init(); }
		return self::$_mAMETHYST;
	}

	public static function COPPER() : ArmorTrimMaterial{
		if(!isset(self::$_mCOPPER)){ self::init(); }
		return self::$_mCOPPER;
	}

	public static function DIAMOND() : ArmorTrimMaterial{
		if(!isset(self::$_mDIAMOND)){ self::init(); }
		return self::$_mDIAMOND;
	}

	public static function EMERALD() : ArmorTrimMaterial{
		if(!isset(self::$_mEMERALD)){ self::init(); }
		return self::$_mEMERALD;
	}

	public static function GOLD() : ArmorTrimMaterial{
		if(!isset(self::$_mGOLD)){ self::init(); }
		return self::$_mGOLD;
	}

	public static function IRON() : ArmorTrimMaterial{
		if(!isset(self::$_mIRON)){ self::init(); }
		return self::$_mIRON;
	}

	public static function LAPIS() : ArmorTrimMaterial{
		if(!isset(self::$_mLAPIS)){ self::init(); }
		return self::$_mLAPIS;
	}

	public static function NETHERITE() : ArmorTrimMaterial{
		if(!isset(self::$_mNETHERITE)){ self::init(); }
		return self::$_mNETHERITE;
	}

	public static function QUARTZ() : ArmorTrimMaterial{
		if(!isset(self::$_mQUARTZ)){ self::init(); }
		return self::$_mQUARTZ;
	}

	public static function REDSTONE() : ArmorTrimMaterial{
		if(!isset(self::$_mREDSTONE)){ self::init(); }
		return self::$_mREDSTONE;
	}

	public static function RESIN() : ArmorTrimMaterial{
		if(!isset(self::$_mRESIN)){ self::init(); }
		return self::$_mRESIN;
	}
}
