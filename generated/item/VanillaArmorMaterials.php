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
 * Allows getting any vanilla armor material implemented by PocketMine-MP
 *
 * This class is generated automatically from source class {@link VanillaArmorMaterialsInputs}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/codegen/registry-interface.php
 */
final class VanillaArmorMaterials{
	private static ArmorMaterial $_mCHAINMAIL;
	private static ArmorMaterial $_mCOPPER;
	private static ArmorMaterial $_mDIAMOND;
	private static ArmorMaterial $_mGOLD;
	private static ArmorMaterial $_mIRON;
	private static ArmorMaterial $_mLEATHER;
	private static ArmorMaterial $_mNETHERITE;
	private static ArmorMaterial $_mTURTLE;

	/**
	 * @var ArmorMaterial[]
	 * @phpstan-var array<string, ArmorMaterial>
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
	 * @phpstan-param \Closure(never) : ArmorMaterial $closure
	 */
	private static function unsafeAssign(\Closure $closure, ArmorMaterial $memberValue) : void{
		/**
		 * This type is not correct either (the param is actually a subtype of ArmorMaterial) but it's called
		 * unsafeAssign for a reason :)
		 * @phpstan-var \Closure(ArmorMaterial) : ArmorMaterial $closure
		 */
		$closure($memberValue);
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return array<string, \Closure(never) : ArmorMaterial>
	 */
	private static function getInitAssigners() : array{
		return [
			"chainmail" => fn(ArmorMaterial $v) => self::$_mCHAINMAIL = $v,
			"copper" => fn(ArmorMaterial $v) => self::$_mCOPPER = $v,
			"diamond" => fn(ArmorMaterial $v) => self::$_mDIAMOND = $v,
			"gold" => fn(ArmorMaterial $v) => self::$_mGOLD = $v,
			"iron" => fn(ArmorMaterial $v) => self::$_mIRON = $v,
			"leather" => fn(ArmorMaterial $v) => self::$_mLEATHER = $v,
			"netherite" => fn(ArmorMaterial $v) => self::$_mNETHERITE = $v,
			"turtle" => fn(ArmorMaterial $v) => self::$_mTURTLE = $v,
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
		$source = new VanillaArmorMaterialsInputs();
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
	 * @return ArmorMaterial[]
	 * @phpstan-return array<string, ArmorMaterial>
	 */
	public static function getAll() : array{
		if(!isset(self::$members)){ self::init(); }
		return self::$members;
	}

	public static function CHAINMAIL() : ArmorMaterial{
		if(!isset(self::$_mCHAINMAIL)){ self::init(); }
		return self::$_mCHAINMAIL;
	}

	public static function COPPER() : ArmorMaterial{
		if(!isset(self::$_mCOPPER)){ self::init(); }
		return self::$_mCOPPER;
	}

	public static function DIAMOND() : ArmorMaterial{
		if(!isset(self::$_mDIAMOND)){ self::init(); }
		return self::$_mDIAMOND;
	}

	public static function GOLD() : ArmorMaterial{
		if(!isset(self::$_mGOLD)){ self::init(); }
		return self::$_mGOLD;
	}

	public static function IRON() : ArmorMaterial{
		if(!isset(self::$_mIRON)){ self::init(); }
		return self::$_mIRON;
	}

	public static function LEATHER() : ArmorMaterial{
		if(!isset(self::$_mLEATHER)){ self::init(); }
		return self::$_mLEATHER;
	}

	public static function NETHERITE() : ArmorMaterial{
		if(!isset(self::$_mNETHERITE)){ self::init(); }
		return self::$_mNETHERITE;
	}

	public static function TURTLE() : ArmorMaterial{
		if(!isset(self::$_mTURTLE)){ self::init(); }
		return self::$_mTURTLE;
	}
}
