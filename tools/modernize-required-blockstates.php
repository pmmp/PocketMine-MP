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

namespace pocketmine\tools\modernize_required_blockstates;

use pocketmine\nbt\BaseNbtSerializer;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\convert\R12ToCurrentBlockMapEntry;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializerContext;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Utils;
use Webmozart\PathUtil\Path;
use function count;
use function defined;
use function dirname;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function is_numeric;
use function strlen;
use function usort;
use const PHP_BINARY;

require dirname(__DIR__) . '/vendor/autoload.php';

function makeVersion(int $major, int $minor, int $patch) : int{
	return ($patch << 8) | ($minor << 16) | ($major << 24);
}

/**
 * @param class-string<BaseNbtSerializer> $nbtSerializer
 */
function readNbt(PacketSerializer $stream, string $nbtSerializer) : TreeRoot{
	$offset = $stream->getOffset();
	try{
		return (new $nbtSerializer())->read($stream->getBuffer(), $offset, 512);
	}finally{
		$stream->setOffset($offset);
	}
}

/**
 * @param R12ToCurrentBlockMapEntry[] $blockMapEntries
 */
function writeNewFormat(array $blockMapEntries) : string{
	$stream = new BinaryStream();
	$nbtWriter = new NetworkNbtSerializer();
	foreach($blockMapEntries as $entry){
		$stream->putUnsignedVarInt(strlen($entry->getId()));
		$stream->put($entry->getId());

		$stream->putLShort($entry->getMeta());
		$stream->put($nbtWriter->write(new TreeRoot($entry->getBlockState())));
	}

	return $stream->getBuffer();
}

/**
 * @param string[] $argv
 */
function main(array $argv) : int{
	if(!isset($argv[1])){
		echo "Usage: " . PHP_BINARY . " " . __FILE__ . " <path to 'required_block_states.nbt' file> [<version (1.12.0)>]\n";
		return 1;
	}
	$file = $argv[1];
	$reader = PacketSerializer::decoder(
		Utils::assumeNotFalse(file_get_contents($file), "Missing required resource file"),
		0,
		new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary(GlobalItemTypeDictionary::getDictionaryProtocol(0))),
		0
	);
	$isLegacy = false;
	$newContents = "";

	try{
		$entriesRoot = readNbt($reader, BigEndianNbtSerializer::class);
		$isLegacy = true;
	}catch(NbtDataException $_){
		$entriesRoot = readNbt($reader, NetworkNbtSerializer::class);
	}

	$list = $entriesRoot->getTag();
	if($list instanceof CompoundTag) {
		$entries = $list->getListTag("Palette");
		if(!($entries instanceof ListTag)) {
			throw new NbtDataException("Legacy: Expected 'Palette' to be TAG_List NBT");
		}
		$isLegacy = true;
	}else{
		$entries = $list;
	}

	$version = null;
	if($isLegacy) {
		if(!isset($argv[2])){
			echo "This is a legacy file, please include the version as 'major.minor.patch'.\n";
			echo "Usage: " . PHP_BINARY . " " . __FILE__ . " <path to 'required_block_states.nbt' file> [<version (1.12.0)>]\n";
			return 1;
		}
		$versionString = $argv[2];
		$numbers = explode(".", $versionString);
		if(count($numbers) !== 3){
			echo "This is a legacy file, please include the version as 'major.minor.patch'.\n";
			echo "Usage: " . PHP_BINARY . " " . __FILE__ . " <path to 'required_block_states.nbt' file> [<version (1.12.0)>]\n";
			return 1;
		}
		$major = $numbers[0];
		$minor = $numbers[1];
		$patch = $numbers[2];
		if(!is_numeric($major) || !is_numeric($minor) || !is_numeric($patch)){
			echo "This is a legacy file, please include the version as 'major.minor.patch'.\n";
			echo "Usage: " . PHP_BINARY . " " . __FILE__ . " <path to 'required_block_states.nbt' file> [<version (1.12.0)>]\n";
			return 1;
		}

		$version = makeVersion((int) $major, (int) $minor, (int) $patch);
	}

	if(!($entries instanceof ListTag)) {
		throw new NbtDataException("Expected TAG_List NBT root");
	}
	$rootPath = Path::getDirectory($file);

	$blockStates = [];
	$blockMap = [];
	foreach($entries->getValue() as $entry) {
		if(!($entry instanceof CompoundTag)) {
			throw new NbtDataException("Expected TAG_Compound NBT entry");
		}

		$blockTag = $entry->getCompoundTag("block");
		if($blockTag === null){
			continue;
		}
		if($isLegacy && $version !== null) {
			$blockTag->setInt("version", $version);

			$name = $blockTag->getString("name");
			$meta = $entry->getShort("meta");
			$blockMap[] = new R12ToCurrentBlockMapEntry($name, $meta, $blockTag);
		}

		$blockStates[] = $blockTag;
	}
	usort($blockStates, fn(CompoundTag $a, CompoundTag $b) => $a <=> $b);

	$nbtWriter = new NetworkNbtSerializer();
	foreach($blockStates as $entry){
		$newContents .= $nbtWriter->write(new TreeRoot($entry));
	}

	file_put_contents(Path::join($rootPath, "canonical_block_states.nbt"), $newContents);
	if(count($blockMap) > 0) {
		file_put_contents(Path::join($rootPath, "r12_to_current_block_map.bin"), writeNewFormat($blockMap));
	}
	return 0;
}

if(!defined('pocketmine\_PHPSTAN_ANALYSIS')){
	exit(main($argv));
}
