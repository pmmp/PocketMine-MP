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

namespace pocketmine\data\bedrock\block\convert;

use function array_flip;
use function is_int;

final class IntFromIntStateMap{

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $deserializeMap;

	/**
	 * Constructs a bidirectional mapping, given a mapping of internal values -> serialized values, and an optional set
	 * of aliases per internal value (used for deserializing invalid serialized values).
	 *
	 * @param int[] $serializeMap
	 * @param int[] $deserializeAliases
	 *
	 * @phpstan-param array<int, int>              $serializeMap
	 * @phpstan-param array<int, int|list<int>> $deserializeAliases
	 */
	public function __construct(
		private array $serializeMap,
		array $deserializeAliases = []
	){
		$this->deserializeMap = array_flip($this->serializeMap);
		foreach($deserializeAliases as $pmValue => $mcValues){
			if(is_int($mcValues)){
				$this->deserializeMap[$mcValues] = $pmValue;
			}else{
				foreach($mcValues as $mcValue){
					$this->deserializeMap[$mcValue] = $pmValue;
				}
			}
		}
	}

	public function deserialize(int $mcValue) : ?int{
		return $this->deserializeMap[$mcValue] ?? null;
	}

	public function serialize(int $pmValue) : int{
		return $this->serializeMap[$pmValue] ?? throw new \LogicException("No mapping for $pmValue");
	}
}
