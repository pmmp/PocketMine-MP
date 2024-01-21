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

namespace pocketmine\data\bedrock;

use pocketmine\item\ArmorTrimMaterial;
use pocketmine\utils\SingletonTrait;

final class TrimMaterialTypeIdMap{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<ArmorTrimMaterial> */
	use IntSaveIdMapTrait;

	private function __construct(){
		$this->register(TrimMaterialTypeIds::AMETHYST, ArmorTrimMaterial::AMETHYST);
		$this->register(TrimMaterialTypeIds::COPPER, ArmorTrimMaterial::COPPER);
		$this->register(TrimMaterialTypeIds::DIAMOND, ArmorTrimMaterial::DIAMOND);
		$this->register(TrimMaterialTypeIds::EMERALD, ArmorTrimMaterial::EMERALD);
		$this->register(TrimMaterialTypeIds::GOLD, ArmorTrimMaterial::GOLD);
		$this->register(TrimMaterialTypeIds::IRON, ArmorTrimMaterial::IRON);
		$this->register(TrimMaterialTypeIds::LAPIS, ArmorTrimMaterial::LAPIS);
		$this->register(TrimMaterialTypeIds::NETHERITE, ArmorTrimMaterial::NETHERITE);
		$this->register(TrimMaterialTypeIds::QUARTZ, ArmorTrimMaterial::QUARTZ);
		$this->register(TrimMaterialTypeIds::REDSTONE, ArmorTrimMaterial::REDSTONE);
	}
}
