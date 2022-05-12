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
use pocketmine\utils\Utils;
use Webmozart\PathUtil\Path;
use function array_search;
use function file_get_contents;
use function is_array;
use function json_decode;
use const pocketmine\BEDROCK_DATA_PATH;

/**
 * Bidirectional map of block IDs to their corresponding blockitem IDs, used for storing items on disk
 */
final class BlockItemIdMap{
	use SingletonTrait;

	private static function make() : self{
		$map = json_decode(
			Utils::assumeNotFalse(file_get_contents(Path::join(BEDROCK_DATA_PATH, 'block_id_to_item_id_map.json')), "Missing required resource file"),
			associative: true,
			flags: JSON_THROW_ON_ERROR
		);
		if(!is_array($map)){
			throw new AssumptionFailedError("Invalid blockitem ID mapping table, expected array as root type");
		}

		return new self($map);
	}

	/**
	 * @param string[] $blockToItemId
	 * @phpstan-param array<string, string> $blockToItemId
	 */
	public function __construct(private array $blockToItemId){}

	public function lookupItemId(string $blockId) : ?string{
		return $this->blockToItemId[$blockId] ?? null;
	}

	public function lookupBlockId(string $itemId) : ?string{
		//we don't need this for any functionality, so we're not concerned about performance here
		//however, it might be nice to have for debugging
		$blockId = array_search($itemId, $this->blockToItemId, true);
		return $blockId !== false ? $blockId : null;
	}
}
