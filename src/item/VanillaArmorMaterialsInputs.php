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

use pocketmine\utils\RegistrySource;
use pocketmine\world\sound\ArmorEquipChainSound;
use pocketmine\world\sound\ArmorEquipCopperSound;
use pocketmine\world\sound\ArmorEquipDiamondSound;
use pocketmine\world\sound\ArmorEquipGenericSound;
use pocketmine\world\sound\ArmorEquipGoldSound;
use pocketmine\world\sound\ArmorEquipIronSound;
use pocketmine\world\sound\ArmorEquipLeatherSound;
use pocketmine\world\sound\ArmorEquipNetheriteSound;

/**
 * @internal
 * @phpstan-extends RegistrySource<ArmorMaterial>
 */
final class VanillaArmorMaterialsInputs extends RegistrySource{
	public function getTargetClassName() : string{
		return "VanillaArmorMaterials";
	}

	public function getTargetClassDocComment() : array{
		return ["Allows getting any vanilla armor material implemented by PocketMine-MP"];
	}

	protected function register(string $name, ArmorMaterial $armorMaterial) : void{
		self::registerValue($name, $armorMaterial);
	}

	protected function setup() : void{
		self::register("leather", new ArmorMaterial(15, new ArmorEquipLeatherSound()));
		self::register("chainmail", new ArmorMaterial(12, new ArmorEquipChainSound()));
		self::register("copper", new ArmorMaterial(8, new ArmorEquipCopperSound()));
		self::register("iron", new ArmorMaterial(9, new ArmorEquipIronSound()));
		self::register("turtle", new ArmorMaterial(9, new ArmorEquipGenericSound()));
		self::register("gold", new ArmorMaterial(25, new ArmorEquipGoldSound()));
		self::register("diamond", new ArmorMaterial(10, new ArmorEquipDiamondSound()));
		self::register("netherite", new ArmorMaterial(15, new ArmorEquipNetheriteSound()));
	}
}
