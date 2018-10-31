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


namespace pocketmine\resourcepacks;


interface ResourcePack{

	/**
	 * Returns the path to the resource pack. This might be a file or a directory, depending on the type of pack.
	 * @return string
	 */
	public function getPath() : string;

	/**
	 * Returns the human-readable name of the resource pack
	 * @return string
	 */
	public function getPackName() : string;

	/**
	 * Returns the pack's UUID as a human-readable string
	 * @return string
	 */
	public function getPackId() : string;

	/**
	 * Returns the size of the pack on disk in bytes.
	 * @return int
	 */
	public function getPackSize() : int;

	/**
	 * Returns a version number for the pack in the format major.minor.patch
	 * @return string
	 */
	public function getPackVersion() : string;

	/**
	 * Returns the raw SHA256 sum of the compressed resource pack zip. This is used by clients to validate pack downloads.
	 * @return string byte-array length 32 bytes
	 */
	public function getSha256() : string;

	/**
	 * Returns a chunk of the resource pack zip as a byte-array for sending to clients.
	 *
	 * Note that resource packs must **always** be in zip archive format for sending.
	 * A folder resource loader may need to perform on-the-fly compression for this purpose.
	 *
	 * @param int $start Offset to start reading the chunk from
	 * @param int $length Maximum length of data to return.
	 *
	 * @return string byte-array
	 */
	public function getPackChunk(int $start, int $length) : string;
}
