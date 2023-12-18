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

use InvalidArgumentException;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\utils\TextFormat;

enum ArmorTrimMaterial : string{

	case AMETHYST = "amethyst";
	case COPPER = "copper";
	case DIAMOND = "diamond";
	case EMERALD = "emerald";
	case GOLD = "gold";
	case IRON = "iron";
	case LAPIS = "lapis";
	case NETHERITE = "netherite";
	case QUARTZ = "quartz";
	case REDSTONE = "redstone";

	public static function fromItem(Item $item) : ?self{
		return match($item->getTypeId()){
			ItemTypeIds::AMETHYST_SHARD => self::AMETHYST,
			ItemTypeIds::COPPER_INGOT => self::COPPER,
			ItemTypeIds::DIAMOND => self::DIAMOND,
			ItemTypeIds::EMERALD => self::EMERALD,
			ItemTypeIds::GOLD_INGOT => self::GOLD,
			ItemTypeIds::IRON_INGOT => self::IRON,
			ItemTypeIds::LAPIS_LAZULI => self::LAPIS,
			ItemTypeIds::NETHERITE_INGOT => self::NETHERITE,
			ItemTypeIds::NETHER_QUARTZ => self::QUARTZ,
			ItemTypeIds::REDSTONE_DUST => self::REDSTONE,
			default => throw new InvalidArgumentException("Item " . $item . " is no valid armor trim material")
		};
	}

	public function getItemId() : string{
		return match($this){
			self::AMETHYST => ItemTypeNames::AMETHYST_SHARD,
			self::COPPER => ItemTypeNames::COPPER_INGOT,
			self::DIAMOND => ItemTypeNames::DIAMOND,
			self::EMERALD => ItemTypeNames::EMERALD,
			self::GOLD => ItemTypeNames::GOLD_INGOT,
			self::IRON => ItemTypeNames::IRON_INGOT,
			self::LAPIS => ItemTypeNames::LAPIS_LAZULI,
			self::NETHERITE => ItemTypeNames::NETHERITE_INGOT,
			self::QUARTZ => ItemTypeNames::QUARTZ,
			self::REDSTONE => ItemTypeNames::REDSTONE
		};
	}

	public function getColor() : string{
		return match($this){
			self::AMETHYST => TextFormat::MATERIAL_AMETHYST,
			self::COPPER => TextFormat::MATERIAL_COPPER,
			self::DIAMOND => TextFormat::MATERIAL_DIAMOND,
			self::EMERALD => TextFormat::MATERIAL_EMERALD,
			self::GOLD => TextFormat::MATERIAL_GOLD,
			self::IRON => TextFormat::MATERIAL_IRON,
			self::LAPIS => TextFormat::MATERIAL_LAPIS,
			self::NETHERITE => TextFormat::MATERIAL_NETHERITE,
			self::QUARTZ => TextFormat::MATERIAL_QUARTZ,
			self::REDSTONE => TextFormat::MATERIAL_REDSTONE
		};
	}
}
