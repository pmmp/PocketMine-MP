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

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;

/**
 * Handles translating legacy 1.12 block ID/meta into modern blockstates.
 */
final class BlockIdMetaUpgrader{
	/**
	 * @param BlockStateData[][] $mappingTable
	 * @phpstan-param array<string, array<int, BlockStateData>> $mappingTable
	 */
	public function __construct(
		private array $mappingTable,
		private LegacyBlockIdToStringIdMap $legacyNumericIdMap
	){}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function fromStringIdMeta(string $id, int $meta) : BlockStateData{
		return $this->mappingTable[$id][$meta] ??
			$this->mappingTable[$id][0] ??
			throw new BlockStateDeserializeException("Unknown legacy block string ID $id");
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function fromIntIdMeta(int $id, int $meta) : BlockStateData{
		$stringId = $this->legacyNumericIdMap->legacyToString($id);
		if($stringId === null){
			throw new BlockStateDeserializeException("Unknown legacy block numeric ID $id");
		}
		return $this->fromStringIdMeta($stringId, $meta);
	}

	/**
	 * Adds a mapping of legacy block numeric ID to modern string ID. This is used for upgrading blocks from pre-1.2.13
	 * worlds (PM3). It's also needed for upgrading flower pot contents and falling blocks from PM4 worlds.
	 */
	public function addIntIdToStringIdMapping(int $intId, string $stringId) : void{
		$this->legacyNumericIdMap->add($stringId, $intId);
	}

	/**
	 * Adds a mapping of legacy block ID and meta to modern blockstate data. This may be needed for upgrading data from
	 * stored custom blocks from older versions of PocketMine-MP.
	 */
	public function addIdMetaToStateMapping(string $stringId, int $meta, BlockStateData $stateData) : void{
		if(isset($this->mappingTable[$stringId][$meta])){
			throw new \InvalidArgumentException("A mapping for $stringId:$meta already exists");
		}
		$this->mappingTable[$stringId][$meta] = $stateData;
	}

	public static function loadFromString(string $data, LegacyBlockIdToStringIdMap $idMap, BlockStateUpgrader $blockStateUpgrader) : self{
		$mappingTable = [];

		$legacyStateMapReader = new BinaryStream($data);
		$nbtReader = new LittleEndianNbtSerializer();

		$idCount = $legacyStateMapReader->getUnsignedVarInt();
		for($idIndex = 0; $idIndex < $idCount; $idIndex++){
			$id = $legacyStateMapReader->get($legacyStateMapReader->getUnsignedVarInt());

			$metaCount = $legacyStateMapReader->getUnsignedVarInt();
			for($metaIndex = 0; $metaIndex < $metaCount; $metaIndex++){
				$meta = $legacyStateMapReader->getUnsignedVarInt();

				$offset = $legacyStateMapReader->getOffset();
				$state = $nbtReader->read($legacyStateMapReader->getBuffer(), $offset)->mustGetCompoundTag();
				$legacyStateMapReader->setOffset($offset);
				$mappingTable[$id][$meta] = $blockStateUpgrader->upgrade(BlockStateData::fromNbt($state));
			}
		}
		if(!$legacyStateMapReader->feof()){
			throw new BinaryDataException("Unexpected trailing data in legacy state map data");
		}

		return new self($mappingTable, $idMap);
	}
}
