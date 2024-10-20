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

use pocketmine\data\bedrock\item\ItemTypeNames as Names;
use pocketmine\item\ItemTypeIds as Ids;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

final class ArmorTrimRegistry{
	use SingletonTrait;

	/**
	 * @var ArmorTrimMaterial[] $materials
	 * @phpstan-var array<string, ArmorTrimMaterial>
	 */
	private array $materials = [];

	/**
	 * @var ArmorTrimMaterial[] $patterns,
	 * @phpstan-var array<int, ArmorTrimMaterial>
	 */
	private array $idToMaterialMap = [];

	/**
	 * @var ArmorTrimPattern[] $patterns
	 * @phpstan-var array<string, ArmorTrimPattern>
	 */
	private array $patterns = [];

	/**
	 * @var ArmorTrimPattern[] $patterns,
	 * @phpstan-var array<int, ArmorTrimPattern>
	 */
	private array $idToPatternMap = [];

	public function __construct(){
		$this->registerDefaultMaterials();
		$this->registerDefaultPatterns();
	}

	private function registerDefaultMaterials() : void{
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::AMETHYST, TextFormat::MATERIAL_AMETHYST, Names::AMETHYST_SHARD, Ids::AMETHYST_SHARD));
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::COPPER, TextFormat::MATERIAL_COPPER, Names::COPPER_INGOT, Ids::COPPER_INGOT));
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::DIAMOND, TextFormat::MATERIAL_DIAMOND, Names::DIAMOND, Ids::DIAMOND));
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::EMERALD, TextFormat::MATERIAL_EMERALD, Names::EMERALD, Ids::EMERALD));
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::GOLD, TextFormat::MATERIAL_GOLD, Names::GOLD_INGOT, Ids::GOLD_INGOT));
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::IRON, TextFormat::MATERIAL_IRON, Names::IRON_INGOT, Ids::IRON_INGOT));
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::LAPIS, TextFormat::MATERIAL_LAPIS, Names::LAPIS_LAZULI, Ids::LAPIS_LAZULI));
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::NETHERITE, TextFormat::MATERIAL_NETHERITE, Names::NETHERITE_INGOT, Ids::NETHERITE_INGOT));
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::QUARTZ, TextFormat::MATERIAL_QUARTZ, Names::QUARTZ, Ids::NETHER_QUARTZ));
		$this->registerMaterial(new ArmorTrimMaterial(ArmorTrimMaterial::REDSTONE, TextFormat::MATERIAL_REDSTONE, Names::REDSTONE, Ids::REDSTONE_DUST));
	}

	private function registerDefaultPatterns() : void{
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::COAST, Names::COAST_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::COAST_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::DUNE, Names::DUNE_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::DUNE_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::EYE, Names::EYE_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::EYE_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::HOST, Names::HOST_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::HOST_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::RAISER, Names::RAISER_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::RAISER_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::RIB, Names::RIB_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::RIB_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::SENTRY, Names::SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::SHAPER, Names::SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::SILENCE, Names::SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::SNOUT,Names::SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::SPIRE,Names::SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::TIDE,Names::TIDE_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::TIDE_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::VEX,Names::VEX_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::VEX_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::WARD,Names::WARD_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::WARD_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::WAYFINDER,Names::WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE));
		$this->registerPattern(new ArmorTrimPattern(ArmorTrimPattern::WILD,Names::WILD_ARMOR_TRIM_SMITHING_TEMPLATE, Ids::WILD_ARMOR_TRIM_SMITHING_TEMPLATE));
	}

	public function registerMaterial(ArmorTrimMaterial $material, bool $overwrite = false) : bool{
		if((isset($this->materials[$material->getIdentifier()]) || isset($this->idToMaterialMap[$material->getTypeId()])) && !$overwrite){
			return false;
		}
		$this->materials[$material->getIdentifier()] = $material;
		$this->idToMaterialMap[$material->getTypeId()] = $material;
		return true;
	}

	public function registerPattern(ArmorTrimPattern $pattern, bool $overwrite = false) : bool{
		if((isset($this->patterns[$pattern->getIdentifier()]) || isset($this->idToPatternMap[$pattern->getTypeId()])) && !$overwrite){
			return false;
		}
		$this->patterns[$pattern->getIdentifier()] = $pattern;
		$this->idToPatternMap[$pattern->getTypeId()] = $pattern;
		return true;
	}

	public function getMaterial(string $identifier) : ?ArmorTrimMaterial{
		return $this->materials[$identifier] ?? null;
	}

	public function getMaterialFromItem(Item $item) : ?ArmorTrimMaterial{
		return $this->idToMaterialMap[$item->getTypeId()] ?? null;
	}

	public function getPattern(string $identifier) : ?ArmorTrimPattern{
		return $this->patterns[$identifier] ?? null;
	}

	public function getPatternFromItem(Item $item) : ?ArmorTrimPattern{
		return $this->idToPatternMap[$item->getTypeId()] ?? null;
	}

	/**
	 * @return ArmorTrimMaterial[]
	 */
	public function getMaterials() : array{
		return $this->materials;
	}

	/**
	 * @return ArmorTrimPattern[]
	 */
	public function getPatterns() : array{
		return $this->patterns;
	}
}
