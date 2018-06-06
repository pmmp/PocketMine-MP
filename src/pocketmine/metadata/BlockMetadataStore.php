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

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\plugin\Plugin;

class BlockMetadataStore extends MetadataStore{
	/** @var Level */
	private $owningLevel;

	public function __construct(Level $owningLevel){
		$this->owningLevel = $owningLevel;
	}

	public function disambiguate(Metadatable $block, string $metadataKey) : string{
		if(!($block instanceof Block)){
			throw new \InvalidArgumentException("Argument must be a Block instance");
		}

		return $block->x . ":" . $block->y . ":" . $block->z . ":" . $metadataKey;
	}

	public function getMetadata(Metadatable $subject, string $metadataKey){
		if(!($subject instanceof Block)){
			throw new \InvalidArgumentException("Object must be a Block");
		}
		if($subject->getLevel() === $this->owningLevel){
			return parent::getMetadata($subject, $metadataKey);
		}else{
			throw new \InvalidStateException("Block does not belong to world " . $this->owningLevel->getName());
		}
	}

	public function hasMetadata(Metadatable $subject, string $metadataKey) : bool{
		if(!($subject instanceof Block)){
			throw new \InvalidArgumentException("Object must be a Block");
		}
		if($subject->getLevel() === $this->owningLevel){
			return parent::hasMetadata($subject, $metadataKey);
		}else{
			throw new \InvalidStateException("Block does not belong to world " . $this->owningLevel->getName());
		}
	}

	public function removeMetadata(Metadatable $subject, string $metadataKey, Plugin $owningPlugin){
		if(!($subject instanceof Block)){
			throw new \InvalidArgumentException("Object must be a Block");
		}
		if($subject->getLevel() === $this->owningLevel){
			parent::removeMetadata($subject, $metadataKey, $owningPlugin);
		}else{
			throw new \InvalidStateException("Block does not belong to world " . $this->owningLevel->getName());
		}
	}

	public function setMetadata(Metadatable $subject, string $metadataKey, MetadataValue $newMetadataValue){
		if(!($subject instanceof Block)){
			throw new \InvalidArgumentException("Object must be a Block");
		}
		if($subject->getLevel() === $this->owningLevel){
			parent::setMetadata($subject, $metadataKey, $newMetadataValue);
		}else{
			throw new \InvalidStateException("Block does not belong to world " . $this->owningLevel->getName());
		}
	}
}
