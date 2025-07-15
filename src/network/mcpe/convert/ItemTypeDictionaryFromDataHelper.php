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

use pocketmine\data\bedrock\BedrockDataFiles;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use function base64_decode;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function json_decode;
use function str_replace;

final class ItemTypeDictionaryFromDataHelper{

	private const PATHS = [
		ProtocolInfo::CURRENT_PROTOCOL => "",
		ProtocolInfo::PROTOCOL_1_21_90 => "-1.21.90",
		ProtocolInfo::PROTOCOL_1_21_80 => "-1.21.80",
		ProtocolInfo::PROTOCOL_1_21_70 => "-1.21.70",
		ProtocolInfo::PROTOCOL_1_21_60 => "-1.21.60",
		ProtocolInfo::PROTOCOL_1_21_50 => "-1.21.50",
		ProtocolInfo::PROTOCOL_1_21_40 => "-1.21.40",
		ProtocolInfo::PROTOCOL_1_21_30 => "-1.21.30",
		ProtocolInfo::PROTOCOL_1_21_20 => "-1.21.20",
		ProtocolInfo::PROTOCOL_1_21_2 => "-1.21.2",
		ProtocolInfo::PROTOCOL_1_21_0 => "-1.21.2",
		ProtocolInfo::PROTOCOL_1_20_80 => "-1.20.80",
		ProtocolInfo::PROTOCOL_1_20_70 => "-1.20.70",
		ProtocolInfo::PROTOCOL_1_20_60 => "-1.20.60",
		ProtocolInfo::PROTOCOL_1_20_50 => "-1.20.50",
		ProtocolInfo::PROTOCOL_1_20_40 => "-1.20.40",
		ProtocolInfo::PROTOCOL_1_20_30 => "-1.20.40",
		ProtocolInfo::PROTOCOL_1_20_10 => "-1.20.10",
		ProtocolInfo::PROTOCOL_1_20_0 => "-1.20.0",
	];

	public static function loadFromProtocolId(int $protocolId) : ItemTypeDictionary{
		return self::loadFromString(Filesystem::fileGetContents(str_replace(".json", self::PATHS[$protocolId] . ".json", BedrockDataFiles::REQUIRED_ITEM_LIST_JSON)));
	}

	public static function loadFromString(string $data) : ItemTypeDictionary{
		$table = json_decode($data, true);
		if(!is_array($table)){
			throw new AssumptionFailedError("Invalid item list format");
		}

		$emptyNBT = new CacheableNbt(new CompoundTag());
		$nbtSerializer = new LittleEndianNbtSerializer();

		$params = [];
		foreach(Utils::promoteKeys($table) as $name => $entry){
			if(!is_array($entry) || !is_string($name) || !isset($entry["component_based"], $entry["runtime_id"]) || !is_bool($entry["component_based"]) || !is_int($entry["runtime_id"]) || !is_int($entry["version"] ?? 0) || !(is_string($componentNbt = $entry["component_nbt"] ?? null) || $componentNbt === null)){
				throw new AssumptionFailedError("Invalid item list format");
			}
			$params[] = new ItemTypeEntry($name, $entry["runtime_id"], $entry["component_based"], $entry["version"] ?? 2, $componentNbt === null ? $emptyNBT : new CacheableNbt($nbtSerializer->read(ErrorToExceptionHandler::trapAndRemoveFalse(fn() => base64_decode($componentNbt, true)))->mustGetCompoundTag()));
		}
		return new ItemTypeDictionary($params);
	}
}
