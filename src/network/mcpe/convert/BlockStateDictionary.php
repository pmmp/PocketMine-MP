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

namespace pocketmine\network\mcpe\convert;

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use function array_map;
use function get_debug_type;
use function is_array;
use function is_int;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Handles translation of network block runtime IDs into blockstate data, and vice versa
 */
final class BlockStateDictionary{

	private BlockStateLookupCache $stateDataToStateIdLookupCache;
	/**
	 * @var int[][]|null
	 * @phpstan-var array<int, array<string, int>>|null
	 */
	private ?array $idMetaToStateIdLookupCache = null;

	/**
	 * @param BlockStateDictionaryEntry[] $states
	 *
	 * @phpstan-param list<BlockStateDictionaryEntry> $states
	 */
	public function __construct(
		private array $states
	){
		$this->stateDataToStateIdLookupCache = new BlockStateLookupCache(array_map(fn(BlockStateDictionaryEntry $entry) => $entry->getStateData(), $this->states));
	}

	/**
	 * @return int[][]
	 * @phpstan-return array<int, array<string, int>>
	 */
	private function getIdMetaToStateIdLookup() : array{
		if($this->idMetaToStateIdLookupCache === null){
			//TODO: if we ever allow mutating the dictionary, this would need to be rebuilt on modification
			$this->idMetaToStateIdLookupCache = [];

			foreach($this->states as $i => $state){
				$this->idMetaToStateIdLookupCache[$state->getMeta()][$state->getStateData()->getName()] = $i;
			}
		}

		return $this->idMetaToStateIdLookupCache;
	}

	public function getDataFromStateId(int $networkRuntimeId) : ?BlockStateData{
		return ($this->states[$networkRuntimeId] ?? null)?->getStateData();
	}

	/**
	 * Searches for the appropriate state ID which matches the given blockstate NBT.
	 * Returns null if there were no matches.
	 */
	public function lookupStateIdFromData(BlockStateData $data) : ?int{
		return $this->stateDataToStateIdLookupCache->lookupStateId($data);
	}

	/**
	 * Returns the blockstate meta value associated with the given blockstate runtime ID.
	 * This is used for serializing crafting recipe inputs.
	 */
	public function getMetaFromStateId(int $networkRuntimeId) : ?int{
		return ($this->states[$networkRuntimeId] ?? null)?->getMeta();
	}

	/**
	 * Returns the blockstate data associated with the given block ID and meta value.
	 * This is used for deserializing crafting recipe inputs.
	 */
	public function lookupStateIdFromIdMeta(string $id, int $meta) : ?int{
		return $this->getIdMetaToStateIdLookup()[$meta][$id] ?? null;
	}

	/**
	 * Returns an array mapping runtime ID => blockstate data.
	 * @return BlockStateDictionaryEntry[]
	 * @phpstan-return array<int, BlockStateDictionaryEntry>
	 */
	public function getStates() : array{ return $this->states; }

	/**
	 * @param string[] $keyIndex
	 * @param (ByteTag|StringTag|IntTag)[][] $valueIndex
	 * @phpstan-param array<string, string> $keyIndex
	 * @phpstan-param array<int, array<int|string, ByteTag|IntTag|StringTag>> $valueIndex
	 */
	private static function deduplicateCompound(CompoundTag $tag, array &$keyIndex, array &$valueIndex) : CompoundTag{
		if($tag->count() === 0){
			return $tag;
		}

		$newTag = CompoundTag::create();
		foreach($tag as $key => $value){
			$key = $keyIndex[$key] ??= $key;

			if($value instanceof CompoundTag){
				$value = self::deduplicateCompound($value, $keyIndex, $valueIndex);
			}elseif($value instanceof ByteTag || $value instanceof IntTag || $value instanceof StringTag){
				$value = $valueIndex[$value->getType()][$value->getValue()] ??= $value;
			}

			$newTag->setTag($key, $value);
		}

		return $newTag;
	}

	/**
	 * @return BlockStateData[]
	 * @phpstan-return list<BlockStateData>
	 *
	 * @throws NbtDataException
	 */
	public static function loadPaletteFromString(string $blockPaletteContents) : array{
		$keyIndex = [];
		$valueIndex = [];

		return array_map(
			function(TreeRoot $root) use (&$keyIndex, &$valueIndex) : BlockStateData{
				return BlockStateData::fromNbt(self::deduplicateCompound($root->mustGetCompoundTag(), $keyIndex, $valueIndex));
			},
			(new NetworkNbtSerializer())->readMultiple($blockPaletteContents)
		);
	}

	public static function loadFromString(string $blockPaletteContents, string $metaMapContents) : self{
		$metaMap = json_decode($metaMapContents, flags: JSON_THROW_ON_ERROR);
		if(!is_array($metaMap)){
			throw new \InvalidArgumentException("Invalid metaMap, expected array for root type, got " . get_debug_type($metaMap));
		}

		$entries = [];

		foreach(self::loadPaletteFromString($blockPaletteContents) as $i => $state){
			$meta = $metaMap[$i] ?? null;
			if($meta === null){
				throw new \InvalidArgumentException("Missing associated meta value for state $i (" . $state->toNbt() . ")");
			}
			if(!is_int($meta)){
				throw new \InvalidArgumentException("Invalid metaMap offset $i, expected int, got " . get_debug_type($meta));
			}
			$entries[$i] = new BlockStateDictionaryEntry($state, $meta);
		}

		return new self($entries);
	}
}
