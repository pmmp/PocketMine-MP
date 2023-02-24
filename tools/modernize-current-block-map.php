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

namespace pocketmine\tools\modernize_current_block_map;

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
use function defined;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function strlen;
use function usort;
use const PHP_BINARY;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @return R12ToCurrentBlockMapEntry[]
 */
function readOldFormat(PacketSerializer $in) : array{
	$entriesRoot = $in->getNbtRoot();
	$entries = $entriesRoot->getTag();
	if(!($entries instanceof ListTag)) {
		throw new NbtDataException("Expected TAG_List NBT root");
	}

	$newEntries = [];
	foreach($entries->getValue() as $entry) {
		if(!($entry instanceof CompoundTag)) {
			throw new NbtDataException("Expected TAG_Compound NBT entry");
		}
		$old = $entry->getCompoundTag("old");
		$new = $entry->getCompoundTag("new");
		if($old === null || $new === null) {
			throw new NbtDataException("Expected 'old' and 'new' TAG_Compound NBT entries");
		}

		$newEntries[] = new R12ToCurrentBlockMapEntry($old->getString("name"), $old->getShort("val"), $new);
	}
	return $newEntries;
}

/**
 * @param R12ToCurrentBlockMapEntry[] $blockMapEntries
 */
function writeNewFormat(array $blockMapEntries) : string{
	$stream = new BinaryStream();
	$nbtWriter = new NetworkNbtSerializer();
	foreach($blockMapEntries as $entry){
		$stream->putShort(strlen($entry->getId()));
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
		echo "Usage: " . PHP_BINARY . " " . __FILE__ . " <path to 'r12_to_current_block_map.nbt' file>\n";
		return 1;
	}
	$file = $argv[1];
	$reader = PacketSerializer::decoder(
		Utils::assumeNotFalse(file_get_contents($file), "Missing required resource file"),
		0,
		new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary(GlobalItemTypeDictionary::getDictionaryProtocol(0))),
		0
	);

	$newEntries = readOldFormat($reader);
	usort($newEntries, fn(R12ToCurrentBlockMapEntry $a, R12ToCurrentBlockMapEntry $b) => $a <=> $b);

	$rootPath = Path::getDirectory($file);
	file_put_contents(Path::join($rootPath, "r12_to_current_block_map.bin"), writeNewFormat($newEntries));

	return 0;
}

if(!defined('pocketmine\_PHPSTAN_ANALYSIS')){
	exit(main($argv));
}
