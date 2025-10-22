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

namespace pocketmine\build\generate_block_type_ids;

use pocketmine\block\VanillaBlocks;
use function array_keys;
use function fclose;
use function fopen;
use function fwrite;
use function sort;
use function strtolower;
use const SORT_STRING;
use const STDERR;

require __DIR__ . '/../vendor/autoload.php';

$constants = array_keys(VanillaBlocks::getAll());
sort($constants, SORT_STRING);

$blockTypeIds = fopen(__DIR__ . '/../src/block/BlockTypeIds.php', 'wb');
if($blockTypeIds === false){
	fwrite(STDERR, "Unable to open BlockTypeIds.php\n");
	exit(1);
}
fwrite($blockTypeIds, <<<'HEADER'
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

/**
 * Every block in {@link VanillaBlocks} has a corresponding constant in this class. These constants can be used to
 * identify and compare block types efficiently using {@link Block::getTypeId()}.
 *
 * Type ID is also used internally as part of block state ID, which is used to store blocks and their simple properties
 * in a memory-efficient way in chunks at runtime.
 *
 * WARNING: These are NOT a replacement for Minecraft legacy IDs. Do **NOT** hardcode their values, or store them in
 * configs or databases. They will change without warning.
 */
final class BlockTypeIds{

	private function __construct(){
		//NOOP
	}

	public const PREFIX = "pocketmine:";


HEADER
);
foreach($constants as $name){
	fwrite($blockTypeIds, "\tpublic const $name = self::PREFIX . \"" . strtolower($name) . "\";\n");
}
fwrite($blockTypeIds, "}\n");
fclose($blockTypeIds);
echo "Done generating BlockTypeIds.\n";
