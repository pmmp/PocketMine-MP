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

use pocketmine\data\bedrock\LegacyItemIdToStringIdMap;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use Webmozart\PathUtil\Path;
use function array_key_exists;
use function file_get_contents;
use function is_array;
use function is_numeric;
use function is_string;
use function json_decode;

/**
 * This class handles translation between network item ID+metadata to PocketMine-MP internal ID+metadata and vice versa.
 */
final class ItemTranslator{
	use SingletonTrait;

	/**
	 * @var int[][]
	 * @phpstan-var array<int, array<int, int>>
	 */
	private $simpleCoreToNetMapping = [];
	/**
	 * @var int[][]
	 * @phpstan-var array<int, array<int, int>>
	 */
	private $simpleNetToCoreMapping = [];

	/**
	 * runtimeId = array[internalId][metadata]
	 * @var int[][][]
	 * @phpstan-var array<int, array<int, array<int, int>>>
	 */
	private $complexCoreToNetMapping = [];
	/**
	 * [internalId, metadata] = array[runtimeId]
	 * @var int[][][]
	 * @phpstan-var array<int, array<int, array{int, int}>>
	 */
	private $complexNetToCoreMapping = [];

	private static function make() : self{
		$data = file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, 'r16_to_current_item_map.json'));
		if($data === false) throw new AssumptionFailedError("Missing required resource file");
		$json = json_decode($data, true);
		if(!is_array($json) or !isset($json["simple"], $json["complex"]) || !is_array($json["simple"]) || !is_array($json["complex"])){
			throw new AssumptionFailedError("Invalid item table format");
		}

		$legacyStringToIntMapRaw = file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, 'item_id_map.json'));
		if($legacyStringToIntMapRaw === false){
			throw new AssumptionFailedError("Missing required resource file");
		}
		$legacyStringToIntMap = LegacyItemIdToStringIdMap::getInstance();

		/** @phpstan-var array<string, int> $simpleMappings */
		$simpleMappings = [];
		foreach($json["simple"] as $oldId => $newId){
			if(!is_string($oldId) || !is_string($newId)){
				throw new AssumptionFailedError("Invalid item table format");
			}
			$intId = $legacyStringToIntMap->stringToLegacy($oldId);
			if($intId === null){
				//new item without a fixed legacy ID - we can't handle this right now
				continue;
			}
			$simpleMappings[$newId] = $intId;
		}
		foreach($legacyStringToIntMap->getStringToLegacyMap() as $stringId => $intId){
			if(isset($simpleMappings[$stringId])){
				throw new \UnexpectedValueException("Old ID $stringId collides with new ID");
			}
			$simpleMappings[$stringId] = $intId;
		}

		/** @phpstan-var array<string, array{int, int}> $complexMappings */
		$complexMappings = [];
		foreach($json["complex"] as $oldId => $map){
			if(!is_string($oldId) || !is_array($map)){
				throw new AssumptionFailedError("Invalid item table format");
			}
			foreach($map as $meta => $newId){
				if(!is_numeric($meta) || !is_string($newId)){
					throw new AssumptionFailedError("Invalid item table format");
				}
				$intId = $legacyStringToIntMap->stringToLegacy($oldId);
				if($intId === null){
					//new item without a fixed legacy ID - we can't handle this right now
					continue;
				}
				$complexMappings[$newId] = [$intId, (int) $meta];
			}
		}

		return new self(GlobalItemTypeDictionary::getInstance(), $simpleMappings, $complexMappings);
	}

	/**
	 * @param int[] $simpleMappings
	 * @param int[][] $complexMappings
	 * @phpstan-param array<string, int> $simpleMappings
	 * @phpstan-param array<string, array<int, int>> $complexMappings
	 */
	public function __construct(GlobalItemTypeDictionary $dictionaries, array $simpleMappings, array $complexMappings){
		foreach($dictionaries->getDictionaries() as $dictionaryProtocol => $dictionary){
			foreach($dictionary->getEntries() as $entry){
				$stringId = $entry->getStringId();
				$netId = $entry->getNumericId();
				if(isset($complexMappings[$stringId])){
					[$id, $meta] = $complexMappings[$stringId];
					$this->complexCoreToNetMapping[$dictionaryProtocol][$id][$meta] = $netId;
					$this->complexNetToCoreMapping[$dictionaryProtocol][$netId] = [$id, $meta];
				}elseif(isset($simpleMappings[$stringId])){
					$this->simpleCoreToNetMapping[$dictionaryProtocol][$simpleMappings[$stringId]] = $netId;
					$this->simpleNetToCoreMapping[$dictionaryProtocol][$netId] = $simpleMappings[$stringId];
				}else{
					//not all items have a legacy mapping - for now, we only support the ones that do
					continue;
				}
			}
		}
	}

	/**
	 * @return int[]|null
	 * @phpstan-return array{int, int}|null
	 */
	public function toNetworkIdQuiet(int $dictionaryProtocol, int $internalId, int $internalMeta) : ?array{
		if($internalMeta === -1){
			$internalMeta = 0x7fff;
		}
		if(isset($this->complexCoreToNetMapping[$dictionaryProtocol][$internalId][$internalMeta])){
			return [$this->complexCoreToNetMapping[$dictionaryProtocol][$internalId][$internalMeta], 0];
		}
		if(array_key_exists($internalId, $this->simpleCoreToNetMapping[$dictionaryProtocol])){
			return [$this->simpleCoreToNetMapping[$dictionaryProtocol][$internalId], $internalMeta];
		}

		return null;
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int}
	 */
	public function toNetworkId(int $dictionaryProtocol, int $internalId, int $internalMeta) : array{
		return $this->toNetworkIdQuiet($dictionaryProtocol, $internalId, $internalMeta) ??
			throw new \InvalidArgumentException("Unmapped ID/metadata combination $internalId:$internalMeta");
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int}
	 * @throws TypeConversionException
	 */
	public function fromNetworkId(int $dictionaryProtocol, int $networkId, int $networkMeta, ?bool &$isComplexMapping = null) : array{
		if(isset($this->complexNetToCoreMapping[$dictionaryProtocol][$networkId])){
			if($networkMeta !== 0){
				throw new TypeConversionException("Unexpected non-zero network meta on complex item mapping");
			}
			$isComplexMapping = true;
			return $this->complexNetToCoreMapping[$dictionaryProtocol][$networkId];
		}
		$isComplexMapping = false;
		if(isset($this->simpleNetToCoreMapping[$dictionaryProtocol][$networkId])){
			return [$this->simpleNetToCoreMapping[$dictionaryProtocol][$networkId], $networkMeta];
		}
		throw new TypeConversionException("Unmapped network ID/metadata combination $networkId:$networkMeta");
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int}
	 * @throws TypeConversionException
	 */
	public function fromNetworkIdWithWildcardHandling(int $dictionaryProtocol, int $networkId, int $networkMeta) : array{
		$isComplexMapping = false;
		if($networkMeta !== 0x7fff){
			return $this->fromNetworkId($dictionaryProtocol, $networkId, $networkMeta);
		}
		[$id, $meta] = $this->fromNetworkId($dictionaryProtocol, $networkId, 0, $isComplexMapping);
		return [$id, $isComplexMapping ? $meta : -1];
	}
}
