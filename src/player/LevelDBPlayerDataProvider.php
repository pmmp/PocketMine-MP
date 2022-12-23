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

namespace pocketmine\player;

use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use function strtolower;
use const LEVELDB_ZLIB_RAW_COMPRESSION;

/**
 * Stores player data in a zlib-compressed LevelDB database on the local disk.
 * This storage method is significantly more efficient than flat files due to the small size of most player.dat files.
 */
final class LevelDBPlayerDataProvider implements PlayerDataProvider{
	private const DATA = "\x01";
	private const BACKUP_DATA = "\x02";

	private \LevelDB $db;

	public function __construct(
		private string $path
	){
		$this->db = new \LevelDB($this->path, [
			"compression" => LEVELDB_ZLIB_RAW_COMPRESSION, //I'd rather use Snappy, but our binaries don't include it by default
		]);
	}

	private function getKey(string $name) : string{
		return self::DATA . strtolower($name);
	}

	private function getBackupKey(string $name) : string{
		return self::BACKUP_DATA . strtolower($name);
	}

	public function hasData(string $name) : bool{
		return $this->db->get($this->getKey($name)) !== false;
	}

	public function loadData(string $name) : ?CompoundTag{
		$key = $this->getKey($name);
		$raw = $this->db->get($key);
		if($raw === false){
			return null;
		}

		try{
			return (new BigEndianNbtSerializer())->read($raw)->mustGetCompoundTag();
		}catch(NbtDataException $e){ //corrupt data
			$this->db->put($this->getBackupKey($name), $raw);
			$this->db->delete($key);
			throw new PlayerDataLoadException("Failed to decode NBT data for \"$name\": " . $e->getMessage(), 0, $e);
		}
	}

	public function saveData(string $name, CompoundTag $data) : void{
		$this->db->put($this->getKey($name), (new BigEndianNbtSerializer())->write(new TreeRoot($data)));
	}
}
