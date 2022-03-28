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

namespace pocketmine\data\bedrock\blockstate\upgrade;

use pocketmine\data\bedrock\blockstate\BlockStateData;
use pocketmine\data\bedrock\LegacyBlockIdToStringIdMap;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\utils\BinaryStream;

/**
 * Handles translating legacy 1.12 block ID/meta into modern blockstates.
 */
final class LegacyBlockStateMapper{
	/**
	 * @param BlockStateData[][] $mappingTable
	 * @phpstan-param array<string, array<int, BlockStateData>> $mappingTable
	 */
	public function __construct(
		private array $mappingTable,
		private LegacyBlockIdToStringIdMap $legacyNumericIdMap
	){}

	public function fromStringIdMeta(string $id, int $meta) : ?BlockStateData{
		return $this->mappingTable[$id][$meta] ?? $this->mappingTable[$id][0] ?? null;
	}

	public function fromIntIdMeta(int $id, int $meta) : ?BlockStateData{
		$stringId = $this->legacyNumericIdMap->legacyToString($id);
		if($stringId === null){
			return null;
		}
		return $this->fromStringIdMeta($stringId, $meta);
	}

	public static function loadFromString(string $data, LegacyBlockIdToStringIdMap $idMap) : self{
		$mappingTable = [];

		$legacyStateMapReader = new BinaryStream($data);
		$nbtReader = new NetworkNbtSerializer();
		while(!$legacyStateMapReader->feof()){
			$id = $legacyStateMapReader->get($legacyStateMapReader->getUnsignedVarInt());
			$meta = $legacyStateMapReader->getLShort();

			$offset = $legacyStateMapReader->getOffset();
			$state = $nbtReader->read($legacyStateMapReader->getBuffer(), $offset)->mustGetCompoundTag();
			$legacyStateMapReader->setOffset($offset);
			$mappingTable[$id][$meta] = BlockStateData::fromNbt($state);
		}

		return new self($mappingTable, $idMap);
	}
}
