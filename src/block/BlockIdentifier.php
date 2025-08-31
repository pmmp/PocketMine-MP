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

namespace pocketmine\block;

use pocketmine\block\tile\Tile;
use pocketmine\utils\Utils;

class BlockIdentifier{
	private int $typeNumber;

	/**
	 * @phpstan-param class-string<Tile>|null $tileClass
	 */
	public function __construct(
		private string $blockTypeId,
		private ?string $tileClass = null
	){
		$this->typeNumber = self::lookupTypeNumberFromTypeId($this->blockTypeId);
		if($tileClass !== null){
			Utils::testValidInstance($tileClass, Tile::class);
		}
	}

	public function getBlockTypeId() : string{ return $this->blockTypeId; }

	/**
	 * @internal
	 */
	public function getBlockTypeNumber() : int{ return $this->typeNumber; }

	/**
	 * @phpstan-return class-string<Tile>|null
	 */
	public function getTileClass() : ?string{
		return $this->tileClass;
	}

	public const AIR_TYPE_NUMBER = 10000;

	private static int $nextTypeNumber = self::AIR_TYPE_NUMBER + 1; //fixed ID reserved for air, for Block::EMPTY_STATE_ID

	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private static $typeIdToTypeNumber = [];
	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private static $typeNumberToTypeId = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private static $typeIdXorMasks = [];

	public static function firstUnusedTypeNumber() : int{
		return self::$nextTypeNumber;
	}

	private static function claimTypeId(string $typeId) : int{
		if(isset(self::$typeIdToTypeNumber[$typeId])){
			throw new \InvalidArgumentException("Type ID \"$typeId\" has already been claimed");
		}
		$typeNumber = $typeId === BlockTypeIds::AIR ? self::AIR_TYPE_NUMBER : self::$nextTypeNumber++;
		self::$typeIdToTypeNumber[$typeId] = $typeNumber;
		self::$typeNumberToTypeId[$typeNumber] = $typeId;
		self::$typeIdXorMasks[$typeNumber] = Block::computeStateIdXorMask($typeNumber);
		return $typeNumber;
	}

	public static function lookupTypeNumberFromTypeId(string $typeId) : int{
		return self::$typeIdToTypeNumber[$typeId] ??= self::claimTypeId($typeId);
	}

	public static function stateIdXorMask(int $typeId) : int{
		return self::$typeIdXorMasks[$typeId];
	}

	public static function lookupTypeIdFromTypeNumber(int $typeNumber) : string{
		return self::$typeNumberToTypeId[$typeNumber] ?? throw new \InvalidArgumentException("Unknown type number $typeNumber (probably not registered on this thread?)");
	}
}
