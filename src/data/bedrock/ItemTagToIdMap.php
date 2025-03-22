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

use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use function array_keys;
use function gettype;
use function is_array;
use function is_string;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Tracks Minecraft Bedrock item tags, and the item IDs which belong to them
 *
 * @internal
 */
final class ItemTagToIdMap{
	use SingletonTrait;

	private static function make() : self{
		$map = json_decode(Filesystem::fileGetContents(BedrockDataFiles::ITEM_TAGS_JSON), true, flags: JSON_THROW_ON_ERROR);
		if(!is_array($map)){
			throw new AssumptionFailedError("Invalid item tag map, expected array");
		}
		$cleanMap = [];
		foreach(Utils::promoteKeys($map) as $tagName => $ids){
			if(!is_string($tagName)){
				throw new AssumptionFailedError("Invalid item tag name $tagName, expected string as key");
			}
			if(!is_array($ids)){
				throw new AssumptionFailedError("Invalid item tag $tagName, expected array of IDs as value");
			}
			$cleanIds = [];
			foreach($ids as $id){
				if(!is_string($id)){
					throw new AssumptionFailedError("Invalid item tag $tagName, expected string as ID, got " . gettype($id));
				}
				$cleanIds[] = $id;
			}
			$cleanMap[$tagName] = $cleanIds;
		}

		return new self($cleanMap);
	}

	/**
	 * @var true[][]
	 * @phpstan-var array<string, array<string, true>>
	 */
	private array $tagToIdsMap = [];

	/**
	 * @param string[][] $tagToIds
	 * @phpstan-param array<string, list<string>> $tagToIds
	 */
	public function __construct(
		array $tagToIds
	){
		foreach(Utils::stringifyKeys($tagToIds) as $tag => $ids){
			foreach($ids as $id){
				$this->tagToIdsMap[$tag][$id] = true;
			}
		}
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getIdsForTag(string $tag) : array{
		return array_keys($this->tagToIdsMap[$tag] ?? []);
	}

	public function tagContainsId(string $tag, string $id) : bool{
		return isset($this->tagToIdsMap[$tag][$id]);
	}

	public function addIdToTag(string $tag, string $id) : void{
		$this->tagToIdsMap[$tag][$id] = true;
	}
}
