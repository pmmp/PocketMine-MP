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

namespace pocketmine\item;

use pocketmine\data\bedrock\item\ItemDeserializer;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\bedrock\item\upgrade\ItemDataUpgrader;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use Symfony\Component\Filesystem\Path;
use function explode;
use function is_array;
use function is_numeric;
use function is_string;
use function json_decode;
use function str_replace;
use function strtolower;
use function trim;

/**
 * @deprecated
 * @see StringToItemParser
 *
 * This class replaces the functionality that used to be provided by ItemFactory::fromString(), but in a more dynamic
 * way.
 * Avoid using this wherever possible. Unless you need to parse item strings containing meta (e.g. "dye:4", "351:4") or
 * item IDs (e.g. "351"), you should prefer the newer StringToItemParser, which is much more user-friendly, more
 * flexible, and also supports registering custom aliases for any item in any state.
 *
 * WARNING: This class does NOT support items added during or after PocketMine-MP 5.0.0. Use StringToItemParser for
 * modern items.
 */
final class LegacyStringToItemParser{
	use SingletonTrait;

	private static function make() : self{
		$result = new self(
			GlobalItemDataHandlers::getUpgrader(),
			GlobalItemDataHandlers::getDeserializer()
		);

		$mappingsRaw = Filesystem::fileGetContents(Path::join(\pocketmine\RESOURCE_PATH, 'item_from_string_bc_map.json'));

		$mappings = json_decode($mappingsRaw, true);
		if(!is_array($mappings)) throw new AssumptionFailedError("Invalid mappings format, expected array");

		foreach(Utils::promoteKeys($mappings) as $name => $id){
			if(!is_string($id)) throw new AssumptionFailedError("Invalid mappings format, expected string values");
			$result->addMapping((string) $name, $id);
		}

		return $result;
	}

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private array $map = [];

	public function __construct(
		private ItemDataUpgrader $itemDataUpgrader,
		private ItemDeserializer $itemDeserializer
	){}

	public function addMapping(string $alias, string $id) : void{
		$this->map[$alias] = $id;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getMappings() : array{
		return $this->map;
	}

	/**
	 * Tries to parse the specified string into Item types.
	 *
	 * Example accepted formats:
	 * - `diamond_pickaxe:5`
	 * - `minecraft:string`
	 * - `351:4 (lapis lazuli ID:meta)`
	 *
	 * @throws LegacyStringToItemParserException if the given string cannot be parsed as an item identifier
	 */
	public function parse(string $input) : Item{
		$key = $this->reprocess($input);
		$b = explode(":", $key);

		if(!isset($b[1])){
			$meta = 0;
		}elseif(is_numeric($b[1])){
			$meta = (int) $b[1];
		}else{
			throw new LegacyStringToItemParserException("Unable to parse \"" . $b[1] . "\" from \"" . $input . "\" as a valid meta value");
		}

		$lower = strtolower($b[0]);
		if($lower === "0" || $lower === "air"){
			//item deserializer doesn't recognize air items since they aren't supposed to exist
			return VanillaItems::AIR();
		}

		$legacyId = $this->map[$lower] ?? null;
		if($legacyId === null){
			throw new LegacyStringToItemParserException("Unable to resolve \"" . $input . "\" to a valid item");
		}
		$itemData = $this->itemDataUpgrader->upgradeItemTypeDataString($legacyId, $meta, 1, null);

		try{
			return $this->itemDeserializer->deserializeStack($itemData);
		}catch(ItemTypeDeserializeException $e){
			throw new LegacyStringToItemParserException($e->getMessage(), 0, $e);
		}
	}

	protected function reprocess(string $input) : string{
		return str_replace([" ", "minecraft:"], ["_", ""], trim($input));
	}
}
