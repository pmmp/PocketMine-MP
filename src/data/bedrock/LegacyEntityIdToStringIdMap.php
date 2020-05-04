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
use pocketmine\utils\SingletonTrait;
use function file_get_contents;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;

final class LegacyEntityIdToStringIdMap{
	use SingletonTrait;

	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private $legacyToString = [];
	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private $stringToLegacy = [];

	public function __construct(){
		$rawJson = @file_get_contents(\pocketmine\RESOURCE_PATH . '/vanilla/entity_id_map.json');
		if($rawJson === false) throw new AssumptionFailedError("Missing required resource file");
		$mapping = json_decode($rawJson, true);
		if(!is_array($mapping)) throw new AssumptionFailedError("Entity ID map should be a JSON object");
		foreach($mapping as $stringId => $legacyId){
			if(!is_string($stringId) or !is_int($legacyId)){
				throw new AssumptionFailedError("Block ID map should have string keys and int values");
			}
			$this->legacyToString[$legacyId] = $stringId;
			$this->stringToLegacy[$stringId] = $legacyId;
		}
	}

	public function legacyToString(int $legacy) : ?string{
		return $this->legacyToString[$legacy] ?? null;
	}

	public function stringToLegacy(string $string) : ?int{
		return $this->stringToLegacy[$string] ?? null;
	}
}
