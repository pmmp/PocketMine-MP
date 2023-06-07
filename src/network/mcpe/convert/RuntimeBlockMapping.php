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

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\BedrockDataFiles;
use pocketmine\data\bedrock\LegacyBlockIdToStringIdMap;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;

/**
 * @internal
 */
final class RuntimeBlockMapping{
	use SingletonTrait;

	/** @var int[] */
	private array $legacyToRuntimeMap = [];
	/** @var int[] */
	private array $runtimeToLegacyMap = [];
	/** @var CompoundTag[]|null */
	private ?array $bedrockKnownStates = null;

	private static function make() : self{
		return new self(
			BedrockDataFiles::CANONICAL_BLOCK_STATES_NBT,
			BedrockDataFiles::R12_TO_CURRENT_BLOCK_MAP_BIN
		);
	}

	/**
	 * @param string[]                       $keyIndex
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
				$value = $valueIndex[$value->getType()][(new LittleEndianNbtSerializer())->write(new TreeRoot($value))] ??= self::deduplicateCompound($value, $keyIndex, $valueIndex);
			}elseif($value instanceof ByteTag || $value instanceof IntTag || $value instanceof StringTag){
				$value = $valueIndex[$value->getType()][$value->getValue()] ??= $value;
			}

			$newTag->setTag($key, $value);
		}

		return $newTag;
	}

	public function __construct(
		private string $canonicalBlockStatesFile,
		string $r12ToCurrentBlockMapFile
	){
		//do not cache this - we only need it to set up mappings under normal circumstances
		$bedrockKnownStates = $this->loadBedrockKnownStates();

		$legacyIdMap = LegacyBlockIdToStringIdMap::getInstance();
		/** @var R12ToCurrentBlockMapEntry[] $legacyStateMap */
		$legacyStateMap = [];
		$legacyStateMapReader = new BinaryStream(Filesystem::fileGetContents($r12ToCurrentBlockMapFile));
		$nbtReader = new NetworkNbtSerializer();
		while(!$legacyStateMapReader->feof()){
			$id = $legacyStateMapReader->get($legacyStateMapReader->getUnsignedVarInt());
			$meta = $legacyStateMapReader->getLShort();

			$offset = $legacyStateMapReader->getOffset();
			$state = $nbtReader->read($legacyStateMapReader->getBuffer(), $offset)->mustGetCompoundTag();
			$legacyStateMapReader->setOffset($offset);
			$legacyStateMap[] = new R12ToCurrentBlockMapEntry($id, $meta, $state);
		}

		/**
		 * @var int[][] $idToStatesMap string id -> int[] list of candidate state indices
		 */
		$idToStatesMap = [];
		foreach($bedrockKnownStates as $k => $state){
			$idToStatesMap[$state->getString("name")][] = $k;
		}
		foreach($legacyStateMap as $pair){
			$id = $legacyIdMap->stringToLegacy($pair->getId());
			if($id === null){
				throw new \RuntimeException("No legacy ID matches " . $pair->getId());
			}
			$data = $pair->getMeta();
			if($data > 15){
				//we can't handle metadata with more than 4 bits
				continue;
			}
			$mappedState = $pair->getBlockState();
			$mappedName = $mappedState->getString("name");
			if(!isset($idToStatesMap[$mappedName])){
				throw new \RuntimeException("Mapped new state does not appear in network table");
			}
			foreach($idToStatesMap[$mappedName] as $k){
				$networkState = $bedrockKnownStates[$k];
				if($mappedState->equals($networkState)){
					$this->registerMapping($k, $id, $data);
					continue 2;
				}
			}
			throw new \RuntimeException("Mapped new state does not appear in network table");
		}
	}

	/**
	 * @return CompoundTag[]
	 */
	private function loadBedrockKnownStates() : array{
		$stream = new BinaryStream(Filesystem::fileGetContents($this->canonicalBlockStatesFile));
		$list = [];
		$nbtReader = new NetworkNbtSerializer();

		$keyIndex = [];
		$valueIndex = [];
		while(!$stream->feof()){
			$offset = $stream->getOffset();
			$blockState = $nbtReader->read($stream->getBuffer(), $offset)->mustGetCompoundTag();
			$stream->setOffset($offset);
			$list[] = self::deduplicateCompound($blockState, $keyIndex, $valueIndex);
		}
		return $list;
	}

	public function toRuntimeId(int $internalStateId) : int{
		return $this->legacyToRuntimeMap[$internalStateId] ?? $this->legacyToRuntimeMap[BlockLegacyIds::INFO_UPDATE << Block::INTERNAL_METADATA_BITS];
	}

	public function fromRuntimeId(int $runtimeId) : int{
		return $this->runtimeToLegacyMap[$runtimeId];
	}

	private function registerMapping(int $staticRuntimeId, int $legacyId, int $legacyMeta) : void{
		$this->legacyToRuntimeMap[($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta] = $staticRuntimeId;
		$this->runtimeToLegacyMap[$staticRuntimeId] = ($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta;
	}

	/**
	 * WARNING: This method may load the palette from disk, which is a slow operation.
	 * Afterwards, it will cache the palette in memory, which requires (in some cases) tens of MB of memory.
	 * Avoid using this where possible.
	 *
	 * @deprecated
	 * @return CompoundTag[]
	 */
	public function getBedrockKnownStates() : array{
		return $this->bedrockKnownStates ??= $this->loadBedrockKnownStates();
	}
}
