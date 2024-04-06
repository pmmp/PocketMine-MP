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

namespace pocketmine\player\camera;

use pocketmine\utils\RegistryTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static CameraPreset FIRST_PERSON()
 * @method static CameraPreset FREE()
 * @method static CameraPreset THIRD_PERSON()
 * @method static CameraPreset THIRD_PERSON_FRONT()
 */
final class VanillaCameraPresets{
	use RegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, CameraPreset $member) : void{
		self::_registryRegister($name, $member);
	}

	/**
	 * @return CameraPreset[]
	 * @phpstan-return array<string, CameraPreset>
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var CameraPreset[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup() : void{
		$factory = CameraPresetFactory::getInstance();

		self::register("first_person", $factory->fromId("minecraft:first_person"));
		self::register("third_person", $factory->fromId("minecraft:third_person"));
		self::register("third_person_front", $factory->fromId("minecraft:third_person_front"));
		self::register("free", $factory->fromId("minecraft:free"));
	}
}
