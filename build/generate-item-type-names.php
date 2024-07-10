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

namespace pocketmine\build\generate_item_serializer_ids;

use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\network\mcpe\convert\ItemTypeDictionaryFromDataHelper;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\utils\Utils;
use function asort;
use function count;
use function dirname;
use function explode;
use function fclose;
use function file_get_contents;
use function fopen;
use function fwrite;
use function strtoupper;
use const SORT_STRING;
use const STDERR;

require dirname(__DIR__) . '/vendor/autoload.php';

function constifyMcId(string $id) : string{
	return strtoupper(explode(":", $id, 2)[1]);
}

function generateItemIds(ItemTypeDictionary $dictionary, BlockItemIdMap $blockItemIdMap) : void{
	$ids = [];
	foreach($dictionary->getEntries() as $entry){
		if($entry->getStringId() === "minecraft:air" || $blockItemIdMap->lookupBlockId($entry->getStringId()) !== null){ //blockitems are serialized via BlockStateSerializer
			continue;
		}
		$ids[$entry->getStringId()] = $entry->getStringId();
	}
	asort($ids, SORT_STRING);

	$file = ErrorToExceptionHandler::trapAndRemoveFalse(fn() => fopen(dirname(__DIR__) . '/src/data/bedrock/item/ItemTypeNames.php', 'wb'));

	fwrite($file, <<<'HEADER'
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

/**
 * This class is generated automatically from the item type dictionary for the current version. Do not edit it manually.
 */
final class ItemTypeNames{

HEADER
	);

	foreach(Utils::stringifyKeys($ids) as $id){
		fwrite($file, "\tpublic const " . constifyMcId($id) . " = \"" . $id . "\";\n");
	}
	fwrite($file, "}\n");
	fclose($file);
}

if(count($argv) !== 2){
	fwrite(STDERR, "This script regenerates ItemTypeNames from a given item dictionary file\n");
	fwrite(STDERR, "Required argument: path to item type dictionary file\n");
	exit(1);
}

$raw = file_get_contents($argv[1]);
if($raw === false){
	fwrite(STDERR, "Failed to read item type dictionary file\n");
	exit(1);
}

$dictionary = ItemTypeDictionaryFromDataHelper::loadFromString($raw);
$blockItemIdMap = BlockItemIdMap::getInstance();
generateItemIds($dictionary, $blockItemIdMap);

echo "Done. Don't forget to run CS fixup after generating code.\n";
