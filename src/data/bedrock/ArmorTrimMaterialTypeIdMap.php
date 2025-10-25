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

use pocketmine\data\bedrock\ArmorTrimMaterialTypeIds as Ids;
use pocketmine\item\ArmorTrimMaterial;
use pocketmine\item\Item;
use pocketmine\item\VanillaArmorTrimMaterials as Materials;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;
use function array_values;
use function spl_object_id;

final class ArmorTrimMaterialTypeIdMap{
	use SingletonTrait;

	/**
	 * @var ArmorTrimMaterial[]
	 * @phpstan-var array<string, ArmorTrimMaterial>
	 */
	private array $idToMaterial = [];
	/**
	 * @var ArmorTrimMaterial[]
	 * @phpstan-var array<int, ArmorTrimMaterial>
	 */
	private array $itemToMaterial = [];
	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $materialToId = [];

	public function __construct(){
		foreach(Materials::getAll() as $material){
			$this->register(match($material){
				Materials::AMETHYST() => Ids::AMETHYST,
				Materials::COPPER() => Ids::COPPER,
				Materials::DIAMOND() => Ids::DIAMOND,
				Materials::EMERALD() => Ids::EMERALD,
				Materials::GOLD() => Ids::GOLD,
				Materials::IRON() => Ids::IRON,
				Materials::LAPIS() => Ids::LAPIS,
				Materials::NETHERITE() => Ids::NETHERITE,
				Materials::QUARTZ() => Ids::QUARTZ,
				Materials::REDSTONE() => Ids::REDSTONE,
				Materials::RESIN() => Ids::RESIN,
				default => throw new AssumptionFailedError("Unhandled armor trim material type")
			}, $material);
		}
	}

	public function register(string $stringId, ArmorTrimMaterial $material) : void{
		$this->idToMaterial[$stringId] = $material;
		$this->itemToMaterial[$material->getItem()->getStateId()] = $material;
		$this->materialToId[spl_object_id($material)] = $stringId;
	}

	public function fromId(string $id) : ?ArmorTrimMaterial{
		return $this->idToMaterial[$id] ?? null;
	}

	public function fromItem(Item $item) : ?ArmorTrimMaterial{
		return $this->itemToMaterial[$item->getStateId()] ?? null;
	}

	public function toId(ArmorTrimMaterial $material) : string{
		$k = spl_object_id($material);
		if(!array_key_exists($k, $this->materialToId)){
			throw new \InvalidArgumentException("Missing mapping for armor trim material with item" . $material->getItem()->getName() . " and color " . $material->getColor());
		}
		return $this->materialToId[$k];
	}

	/**
	 * @return ArmorTrimMaterial[]
	 * @phpstan-return list<ArmorTrimMaterial>
	 */
	public function getAllMaterials() : array{
		return array_values($this->idToMaterial);
	}
}