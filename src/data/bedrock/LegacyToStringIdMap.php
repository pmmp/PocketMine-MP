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
use pocketmine\utils\Utils;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;

abstract class LegacyToStringIdMap{

	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $legacyToString = [];

	public function __construct(string $file){
		$stringToLegacyId = json_decode(Filesystem::fileGetContents($file), true);
		if(!is_array($stringToLegacyId)){
			throw new AssumptionFailedError("Invalid format of ID map");
		}
		foreach(Utils::promoteKeys($stringToLegacyId) as $stringId => $legacyId){
			if(!is_string($stringId) || !is_int($legacyId)){
				throw new AssumptionFailedError("ID map should have string keys and int values");
			}
			$this->legacyToString[$legacyId] = $stringId;
		}
	}

	public function legacyToString(int $legacy) : ?string{
		return $this->legacyToString[$legacy] ?? null;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<int, string>
	 */
	public function getLegacyToStringMap() : array{
		return $this->legacyToString;
	}

	public function add(string $string, int $legacy) : void{
		if(isset($this->legacyToString[$legacy])){
			if($this->legacyToString[$legacy] === $string){
				return;
			}
			throw new \InvalidArgumentException("Legacy ID $legacy is already mapped to string " . $this->legacyToString[$legacy]);
		}
		$this->legacyToString[$legacy] = $string;
	}
}
