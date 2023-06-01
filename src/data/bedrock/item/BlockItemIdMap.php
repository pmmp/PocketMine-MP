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

namespace pocketmine\data\bedrock\item;

use pocketmine\data\bedrock\BedrockDataFiles;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use function array_flip;
use function is_array;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Bidirectional map of block IDs to their corresponding blockitem IDs, used for storing items on disk
 */
final class BlockItemIdMap{
	use SingletonTrait;

	private static function make() : self{
		$map = json_decode(
			Filesystem::fileGetContents(BedrockDataFiles::BLOCK_ID_TO_ITEM_ID_MAP_JSON),
			associative: true,
			flags: JSON_THROW_ON_ERROR
		);
		if(!is_array($map)){
			throw new AssumptionFailedError("Invalid blockitem ID mapping table, expected array as root type");
		}

		return new self($map);
	}

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private array $itemToBlockId;

	/**
	 * @param string[] $blockToItemId
	 * @phpstan-param array<string, string> $blockToItemId
	 */
	public function __construct(private array $blockToItemId){
		$this->itemToBlockId = array_flip($this->blockToItemId);
	}

	public function lookupItemId(string $blockId) : ?string{
		return $this->blockToItemId[$blockId] ?? null;
	}

	public function lookupBlockId(string $itemId) : ?string{
		return $this->itemToBlockId[$itemId] ?? null;
	}
}
