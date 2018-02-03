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
use pocketmine\plugin\PluginException;

abstract class MetadataStore{
	/** @var \SplObjectStorage[] */
	private $metadataMap;

	/**
	 * Adds a metadata value to an object.
	 *
	 * @param Metadatable   $subject
	 * @param string        $metadataKey
	 * @param MetadataValue $newMetadataValue
	 */
	public function setMetadata(Metadatable $subject, string $metadataKey, MetadataValue $newMetadataValue){
		$owningPlugin = $newMetadataValue->getOwningPlugin();
		if($owningPlugin === null){
			throw new PluginException("Plugin cannot be null");
		}

		$key = $this->disambiguate($subject, $metadataKey);
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
	 * @param Metadatable $subject
	 * @param string      $metadataKey
	 *
	 * @return MetadataValue[]
	 */
	public function getMetadata(Metadatable $subject, string $metadataKey){
		$key = $this->disambiguate($subject, $metadataKey);
		if(isset($this->metadataMap[$key])){
			return $this->metadataMap[$key];
		}else{
			return [];
		}
	}

	/**
	 * Tests to see if a metadata attribute has been set on an object.
	 *
	 * @param Metadatable $subject
	 * @param string      $metadataKey
	 *
	 * @return bool
	 */
	public function hasMetadata(Metadatable $subject, string $metadataKey) : bool{
		return isset($this->metadataMap[$this->disambiguate($subject, $metadataKey)]);
	}

	/**
	 * Removes a metadata item owned by a plugin from a subject.
	 *
	 * @param Metadatable $subject
	 * @param string      $metadataKey
	 * @param Plugin      $owningPlugin
	 */
	public function removeMetadata(Metadatable $subject, string $metadataKey, Plugin $owningPlugin){
		$key = $this->disambiguate($subject, $metadataKey);
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
	 * @param Plugin $owningPlugin
	 */
	public function invalidateAll(Plugin $owningPlugin){
		/** @var $values MetadataValue[] */
		foreach($this->metadataMap as $values){
			if(isset($values[$owningPlugin])){
				$values[$owningPlugin]->invalidate();
			}
		}
	}

	/**
	 * Creates a unique name for the object receiving metadata by combining
	 * unique data from the subject with a metadataKey.
	 *
	 * @param Metadatable $subject
	 * @param string      $metadataKey
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	abstract public function disambiguate(Metadatable $subject, string $metadataKey) : string;
}
