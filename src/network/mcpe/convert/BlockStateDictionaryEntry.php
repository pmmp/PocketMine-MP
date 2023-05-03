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
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\Tag;
use pocketmine\nbt\TreeRoot;
use function array_map;
use function count;
use function ksort;
use const SORT_STRING;

final class BlockStateDictionaryEntry{

	private string $rawStateProperties;

	/**
	 * @param Tag[] $stateProperties
	 */
	public function __construct(
		private string $stateName,
		array $stateProperties,
		private int $meta
	){
		$this->rawStateProperties = self::encodeStateProperties($stateProperties);
	}

	public function getStateName() : string{ return $this->stateName; }

	public function getRawStateProperties() : string{ return $this->rawStateProperties; }

	public function generateStateData() : BlockStateData{
		return new BlockStateData(
			$this->stateName,
			self::decodeStateProperties($this->rawStateProperties),
			BlockStateData::CURRENT_VERSION
		);
	}

	public function getMeta() : int{ return $this->meta; }

	/**
	 * @return Tag[]
	 */
	public static function decodeStateProperties(string $rawProperties) : array{
		if($rawProperties === ""){
			return [];
		}
		return array_map(fn(TreeRoot $root) => $root->getTag(), (new LittleEndianNbtSerializer())->readMultiple($rawProperties));
	}

	/**
	 * @param Tag[] $properties
	 */
	public static function encodeStateProperties(array $properties) : string{
		if(count($properties) === 0){
			return "";
		}
		//TODO: make a more efficient encoding - NBT will do for now, but it's not very compact
		ksort($properties, SORT_STRING);
		return (new LittleEndianNbtSerializer())->writeMultiple(array_map(fn(Tag $tag) => new TreeRoot($tag), $properties));
	}
}
