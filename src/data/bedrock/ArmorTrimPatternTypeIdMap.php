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

use pocketmine\data\bedrock\ArmorTrimPatternTypeIds as Ids;
use pocketmine\item\ArmorTrimPattern;
use pocketmine\item\Item;
use pocketmine\item\VanillaArmorTrimPatterns as Patterns;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;
use function array_values;
use function spl_object_id;

final class ArmorTrimPatternTypeIdMap{
	use SingletonTrait;

	/**
	 * @var ArmorTrimPattern[]
	 * @phpstan-var array<string, ArmorTrimPattern>
	 */
	private array $idToPattern = [];
	/**
	 * @var ArmorTrimPattern[]
	 * @phpstan-var array<int, ArmorTrimPattern>
	 */
	private array $itemToPattern = [];
	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $patternToId = [];

	public function __construct(){
		$this->register(Ids::COAST, Patterns::COAST());
		$this->register(Ids::DUNE, Patterns::DUNE());
		$this->register(Ids::EYE, Patterns::EYE());
		$this->register(Ids::HOST, Patterns::HOST());
		$this->register(Ids::RAISER, Patterns::RAISER());
		$this->register(Ids::RIB, Patterns::RIB());
		$this->register(Ids::SENTRY, Patterns::SENTRY());
		$this->register(Ids::SHAPER, Patterns::SHAPER());
		$this->register(Ids::SILENCE, Patterns::SILENCE());
		$this->register(Ids::SNOUT, Patterns::SNOUT());
		$this->register(Ids::SPIRE, Patterns::SPIRE());
		$this->register(Ids::TIDE, Patterns::TIDE());
		$this->register(Ids::VEX, Patterns::VEX());
		$this->register(Ids::WARD, Patterns::WARD());
		$this->register(Ids::WAYFINDER, Patterns::WAYFINDER());
		$this->register(Ids::WILD, Patterns::WILD());
	}

	public function register(string $stringId, ArmorTrimPattern $pattern) : void{
		$this->idToPattern[$stringId] = $pattern;
		$this->itemToPattern[$pattern->getItem()->getStateId()] = $pattern;
		$this->patternToId[spl_object_id($pattern)] = $stringId;
	}

	public function fromId(string $id) : ?ArmorTrimPattern{
		return $this->idToPattern[$id] ?? null;
	}

	public function fromItem(Item $item) : ?ArmorTrimPattern{
		return $this->itemToPattern[$item->getStateId()] ?? null;
	}

	public function toId(ArmorTrimPattern $pattern) : string{
		$k = spl_object_id($pattern);
		if(!array_key_exists($k, $this->patternToId)){
			throw new \InvalidArgumentException("Missing mapping for armor trim pattern with item" . $pattern->getItem()->getName());
		}
		return $this->patternToId[$k];
	}

	/**
	 * @return ArmorTrimPattern[]
	 */
	public function getAllPatterns() : array{
		return array_values($this->idToPattern);
	}
}
