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

namespace pocketmine\tools\generate_block_break_info;

use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use function count;
use function dirname;
use function fclose;
use function file_get_contents;
use function fopen;
use function fwrite;
use function is_array;
use function is_float;
use function json_decode;
use function round;
use function str_replace;
use function strtoupper;
use const STDERR;
use const STDOUT;

require dirname(__DIR__) . '/vendor/autoload.php';

if(count($argv) !== 2){
	fwrite(STDERR, "Required arguments: path to Bedrock Data block properties table JSON.\n");
	exit(1);
}

$jsonRaw = file_get_contents($argv[1]);
if($jsonRaw === false){
	fwrite(STDERR, "Unable to read block properties table.\n");
	exit(1);
}

$table = json_decode($jsonRaw, true);
if(!is_array($table)){
	fwrite(STDERR, "Failed to decode block properties table, expected a JSON object.\n");
	exit(1);
}

$hardnessOutput = fopen(dirname(__DIR__) . "/src/data/bedrock/block/BlockHardnessValues.php", "wb");
$blastResistanceOutput = fopen(dirname(__DIR__) . "/src/data/bedrock/block/BlockBlastResistanceValues.php", "wb");

if($hardnessOutput === false || $blastResistanceOutput === false){
	throw new \RuntimeException("Failed to open output files");
}

fwrite($hardnessOutput, <<<'CODE'
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

namespace pocketmine\data\bedrock\block;

/**
 * This class is generated automatically from BedrockData block properties table. Do not edit it manually.
 * @see build/generate-block-break-info.php
 */
final class BlockHardnessValues{
	private function __construct(){
		//NOOP
	}


CODE);

fwrite($blastResistanceOutput, <<<'CODE'
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

namespace pocketmine\data\bedrock\block;

/**
 * This class is generated automatically from BedrockData block properties table. Do not edit it manually.
 * @see build/generate-block-break-info.php
 */
final class BlockBlastResistanceValues{
	private function __construct(){
		//NOOP
	}


CODE);

foreach(Utils::promoteKeys($table) as $blockName => $blockProperties){
	if(!is_array($blockProperties)){
		throw new AssumptionFailedError("Block properties must be an array");
	}
	if(!isset($blockProperties["hardness"]) || !is_float($blockProperties["hardness"])){
		throw new AssumptionFailedError("Hardness property must always exist and be representable as a float value");
	}
	if(!isset($blockProperties["blastResistance"]) || !is_float($blockProperties["blastResistance"])){
		throw new AssumptionFailedError("Blast resistance property must always exist and be representable as a float value");
	}
	$constantName = strtoupper(str_replace("minecraft:", "", (string) $blockName));
	$hardness = round($blockProperties["hardness"], 5);
	$blastResistance = round($blockProperties["blastResistance"], 5) * 5;

	fwrite($hardnessOutput, "\tpublic const $constantName = $hardness;\n");
	fwrite($blastResistanceOutput, "\tpublic const $constantName = $blastResistance;\n");
}

fwrite($hardnessOutput, "}\n");
fwrite($blastResistanceOutput, "}\n");

fclose($hardnessOutput);
fclose($blastResistanceOutput);

fwrite(STDOUT, "Successfully regenerated block hardness & blast resistance values.\n");
