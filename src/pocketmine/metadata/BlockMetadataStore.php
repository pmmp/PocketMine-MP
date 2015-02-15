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

namespace pocketmine\metadata;

use pocketmine\Block\Block;
use pocketmine\level\Level;
use pocketmine\plugin\Plugin;

class BlockMetadataStore extends MetadataStore{
	/** @var Level */
	private $owningLevel;

	public function __construct(Level $owningLevel){
		$this->owningLevel = $owningLevel;
	}

	public function disambiguate(Metadatable $block, $metadataKey){
		if(!($block instanceof Block)){
			throw new \InvalidArgumentException("Argument must be a Block instance");
		}

		return $block->x . ":" . $block->y . ":" . $block->z . ":" . $metadataKey;
	}

	public function getMetadata($block, $metadataKey){
		if(!($block instanceof Block)){
			throw new \InvalidArgumentException("Object must be a Block");
		}
		if($block->getLevel() === $this->owningLevel){
			return parent::getMetadata($block, $metadataKey);
		}else{
			throw new \InvalidStateException("Block does not belong to world " . $this->owningLevel->getName());
		}
	}

	public function hasMetadata($block, $metadataKey){
		if(!($block instanceof Block)){
			throw new \InvalidArgumentException("Object must be a Block");
		}
		if($block->getLevel() === $this->owningLevel){
			return parent::hasMetadata($block, $metadataKey);
		}else{
			throw new \InvalidStateException("Block does not belong to world " . $this->owningLevel->getName());
		}
	}

	public function removeMetadata($block, $metadataKey, Plugin $owningPlugin){
		if(!($block instanceof Block)){
			throw new \InvalidArgumentException("Object must be a Block");
		}
		if($block->getLevel() === $this->owningLevel){
			parent::hasMetadata($block, $metadataKey, $owningPlugin);
		}else{
			throw new \InvalidStateException("Block does not belong to world " . $this->owningLevel->getName());
		}
	}

	public function setMetadata($block, $metadataKey, MetadataValue $newMetadatavalue){
		if(!($block instanceof Block)){
			throw new \InvalidArgumentException("Object must be a Block");
		}
		if($block->getLevel() === $this->owningLevel){
			parent::setMetadata($block, $metadataKey, $newMetadatavalue);
		}else{
			throw new \InvalidStateException("Block does not belong to world " . $this->owningLevel->getName());
		}
	}
}