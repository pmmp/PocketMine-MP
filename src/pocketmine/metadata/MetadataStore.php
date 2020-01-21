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

/**
 * Saves extra data on runtime for different items
 */
namespace pocketmine\metadata;

use pocketmine\plugin\Plugin;

abstract class MetadataStore{
	/** @var \SplObjectStorage[]|MetadataValue[][] */
	private $metadataMap;

	/**
	 * Adds a metadata value to an object.
	 *
	 * @return void
	 */
	protected function setMetadataInternal(string $key, MetadataValue $newMetadataValue){
		$owningPlugin = $newMetadataValue->getOwningPlugin();

		if(!isset($this->metadataMap[$key])){
			$entry = new \SplObjectStorage();
			$this->metadataMap[$key] = $entry;
		}else{
			$entry = $this->metadataMap[$key];
		}
		$entry[$owningPlugin] = $newMetadataValue;
	}

	/**
	 * Returns all metadata values attached to an object. If multiple
	 * have attached metadata, each will value will be included.
	 *
	 * @return MetadataValue[]
	 */
	protected function getMetadataInternal(string $key){
		if(isset($this->metadataMap[$key])){
			return $this->metadataMap[$key];
		}else{
			return [];
		}
	}

	/**
	 * Tests to see if a metadata attribute has been set on an object.
	 */
	protected function hasMetadataInternal(string $key) : bool{
		return isset($this->metadataMap[$key]);
	}

	/**
	 * Removes a metadata item owned by a plugin from a subject.
	 *
	 * @return void
	 */
	protected function removeMetadataInternal(string $key, Plugin $owningPlugin){
		if(isset($this->metadataMap[$key])){
			unset($this->metadataMap[$key][$owningPlugin]);
			if($this->metadataMap[$key]->count() === 0){
				unset($this->metadataMap[$key]);
			}
		}
	}

	/**
	 * Invalidates all metadata in the metadata store that originates from the
	 * given plugin. Doing this will force each invalidated metadata item to
	 * be recalculated the next time it is accessed.
	 *
	 * @return void
	 */
	public function invalidateAll(Plugin $owningPlugin){
		/** @var \SplObjectStorage|MetadataValue[] $values */
		foreach($this->metadataMap as $values){
			if(isset($values[$owningPlugin])){
				$values[$owningPlugin]->invalidate();
			}
		}
	}
}
