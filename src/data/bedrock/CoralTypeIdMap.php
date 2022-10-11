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

use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\utils\CoralType;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;

final class CoralTypeIdMap{
	use SingletonTrait;

	/**
	 * @var CoralType[]
	 * @phpstan-var array<int, CoralType>
	 */
	private array $idToEnum = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	public function __construct(){
		$this->register(BlockLegacyMetadata::CORAL_VARIANT_TUBE, CoralType::TUBE());
		$this->register(BlockLegacyMetadata::CORAL_VARIANT_BRAIN, CoralType::BRAIN());
		$this->register(BlockLegacyMetadata::CORAL_VARIANT_BUBBLE, CoralType::BUBBLE());
		$this->register(BlockLegacyMetadata::CORAL_VARIANT_FIRE, CoralType::FIRE());
		$this->register(BlockLegacyMetadata::CORAL_VARIANT_HORN, CoralType::HORN());
	}

	public function register(int $id, CoralType $type) : void{
		$this->idToEnum[$id] = $type;
		$this->enumToId[$type->id()] = $id;
	}

	public function fromId(int $id) : ?CoralType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(CoralType $type) : int{
		if(!array_key_exists($type->id(), $this->enumToId)){
			throw new \InvalidArgumentException("Coral type does not have a mapped ID"); //this should never happen
		}
		return $this->enumToId[$type->id()];
	}
}
