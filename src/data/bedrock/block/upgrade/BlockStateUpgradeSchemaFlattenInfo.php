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

namespace pocketmine\data\bedrock\block\upgrade;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use function ksort;
use const SORT_STRING;

final class BlockStateUpgradeSchemaFlattenInfo{

	/**
	 * @param string[] $flattenedValueRemaps
	 * @phpstan-param array<string, string> $flattenedValueRemaps
	 * @phpstan-param ?class-string<ByteTag|IntTag|StringTag> $flattenedPropertyType
	 */
	public function __construct(
		public string $prefix,
		public string $flattenedProperty,
		public string $suffix,
		public array $flattenedValueRemaps,
		public ?string $flattenedPropertyType = null
	){
		ksort($this->flattenedValueRemaps, SORT_STRING);
	}

	public function equals(self $that) : bool{
		return $this->prefix === $that->prefix &&
			$this->flattenedProperty === $that->flattenedProperty &&
			$this->suffix === $that->suffix &&
			$this->flattenedValueRemaps === $that->flattenedValueRemaps &&
			$this->flattenedPropertyType === $that->flattenedPropertyType;
	}
}
