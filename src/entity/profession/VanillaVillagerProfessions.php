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

namespace pocketmine\entity\profession;

use pocketmine\utils\RegistryTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static ArmorerProfession ARMORER()
 * @method static ButcherProfession BUTCHER()
 * @method static CartographerProfession CARTOGRAPHER()
 * @method static ClericProfession CLERIC()
 * @method static FarmerProfession FARMER()
 * @method static FishermanProfession FISHERMAN()
 * @method static FletcherProfession FLETCHER()
 * @method static LeatherworkerProfession LEATHERWORKER()
 * @method static LibrarianProfession LIBRARIAN()
 * @method static MasonProfession MASON()
 * @method static NitwitProfession NITWIT()
 * @method static ShepherdProfession SHEPHERD()
 * @method static ToolsmithProfession TOOLSMITH()
 * @method static UnemployedProfession UNEMPLOYED()
 * @method static WeaponsmithProfession WEAPONSMITH()
 */
final class VanillaVillagerProfessions{
	use RegistryTrait;

	protected static function setup() : void{
		self::register("unemployed", new UnemployedProfession());
		self::register("farmer", new FarmerProfession());
		self::register("fisherman", new FishermanProfession());
		self::register("shepherd", new ShepherdProfession());
		self::register("fletcher", new FletcherProfession());
		self::register("librarian", new LibrarianProfession());
		self::register("cartographer", new CartographerProfession());
		self::register("cleric", new ClericProfession());
		self::register("armorer", new ArmorerProfession());
		self::register("weaponsmith", new WeaponsmithProfession());
		self::register("toolsmith", new ToolsmithProfession());
		self::register("butcher", new ButcherProfession());
		self::register("leatherworker", new LeatherworkerProfession());
		self::register("mason", new MasonProfession());
		self::register("nitwit", new NitwitProfession());
	}

	protected static function register(string $name, VillagerProfession $profession) : void{
		self::_registryRegister($name, $profession);
	}

	/**
	 * @return VillagerProfession[]
	 * @phpstan-return array<string, VillagerProfession>
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var VillagerProfession[] $result */
		$result = self::_registryGetAll();
		return $result;
	}
}
