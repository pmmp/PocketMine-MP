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
use SOFe\Pathetique\Path;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;

abstract class LegacyToStringBidirectionalIdMap{

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

	public function __construct(Path $file){
		$stringToLegacyId = json_decode($file->toString()->getContents(), true);
		if(!is_array($stringToLegacyId)){
			throw new AssumptionFailedError("Invalid format of ID map");
		}
		foreach($stringToLegacyId as $stringId => $legacyId){
			if(!is_string($stringId) or !is_int($legacyId)){
				throw new AssumptionFailedError("ID map should have string keys and int values");
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

	/**
	 * @return string[]
	 * @phpstan-return array<int, string>
	 */
	public function getLegacyToStringMap() : array{
		return $this->legacyToString;
	}

	/**
	 * @return int[]
	 * @phpstan-return array<string, int>
	 */
	public function getStringToLegacyMap() : array{
		return $this->stringToLegacy;
	}
}
