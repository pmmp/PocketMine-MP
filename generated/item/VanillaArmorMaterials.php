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

use function mb_strtoupper;

/**
 * This class is generated automatically from source class {@link VanillaArmorMaterialsInputs}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/generate-registry-interface.php
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

	private function __construct(){
		//NOOP
	}

	/**
	 * Hack to allow ignoring PHPStan wrong type assignment error in one place instead of hundreds or thousands
	 * Assumes that the input value already matches the expected type. If not, a TypeError will be thrown on assignment.
	 *
	 * @phpstan-template TValue of ArmorMaterial
	 * @phpstan-param \Closure(TValue): TValue $closure
	 */
	private static function unsafeAssign(\Closure $closure, ArmorMaterial $memberValue) : void{
		/** @phpstan-var TValue $memberValue */
		$closure($memberValue);
	}

	private static function init() : void{
		//This nasty mess of closures allows us to suppress PHPStan type assignment errors in one place instead of
		//on every single assignment. This will only run one time on first init, so it's fine for performance.
		$values = VanillaArmorMaterialsInputs::getAll();
		foreach($values as $name => $value){
			self::$members[mb_strtoupper($name)] = $value;
		}

		self::unsafeAssign(fn(ArmorMaterial $v) => self::$_mCHAINMAIL = $v, $values["chainmail"]);
		self::unsafeAssign(fn(ArmorMaterial $v) => self::$_mCOPPER = $v, $values["copper"]);
		self::unsafeAssign(fn(ArmorMaterial $v) => self::$_mDIAMOND = $v, $values["diamond"]);
		self::unsafeAssign(fn(ArmorMaterial $v) => self::$_mGOLD = $v, $values["gold"]);
		self::unsafeAssign(fn(ArmorMaterial $v) => self::$_mIRON = $v, $values["iron"]);
		self::unsafeAssign(fn(ArmorMaterial $v) => self::$_mLEATHER = $v, $values["leather"]);
		self::unsafeAssign(fn(ArmorMaterial $v) => self::$_mNETHERITE = $v, $values["netherite"]);
		self::unsafeAssign(fn(ArmorMaterial $v) => self::$_mTURTLE = $v, $values["turtle"]);

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
