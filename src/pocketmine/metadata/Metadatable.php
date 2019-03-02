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

namespace pocketmine\metadata;

use pocketmine\plugin\Plugin;

interface Metadatable{

	/**
	 * Sets a metadata value in the implementing object's metadata store.
	 *
	 * @param string        $metadataKey
	 * @param MetadataValue $newMetadataValue
	 */
	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue) : void;

	/**
	 * Returns a list of previously set metadata values from the implementing
	 * object's metadata store.
	 *
	 * @param string $metadataKey
	 *
	 * @return MetadataValue[]
	 */
	public function getMetadata(string $metadataKey);

	/**
	 * Tests to see whether the implementing object contains the given
	 * metadata value in its metadata store.
	 *
	 * @param string $metadataKey
	 *
	 * @return bool
	 */
	public function hasMetadata(string $metadataKey) : bool;

	/**
	 * Removes the given metadata value from the implementing object's
	 * metadata store.
	 *
	 * @param string $metadataKey
	 * @param Plugin $owningPlugin
	 */
	public function removeMetadata(string $metadataKey, Plugin $owningPlugin) : void;

}
