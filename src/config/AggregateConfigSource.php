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


namespace pocketmine\config;

use ArrayIterator;
use Iterator;
use function count;
use function iterator_to_array;

final class AggregateConfigSource implements MutableConfigSource{
	/** @var ConfigSource[] */
	private $children;

	/**
	 * @param ConfigSource[] $children
	 */
	public static function fromArray(array $children) : ?ConfigSource{
		if(count($children) > 1){
			return new self($children);
		}
		if(count($children) === 1){
			return $children[0];
		}
		return null;
	}

	/**
	 * @param ConfigSource[] $children
	 */
	private function __construct(array $children){
		$this->children = $children;
	}

	public function string() : ?string{
		foreach($this->children as $child){
			$value = $child->string();
			if($value !== null){
				return $value;
			}
		}
		return null;
	}

	public function int() : ?int{
		foreach($this->children as $child){
			$value = $child->int();
			if($value !== null){
				return $value;
			}
		}
		return null;
	}

	public function float() : ?float{
		foreach($this->children as $child){
			$value = $child->float();
			if($value !== null){
				return $value;
			}
		}
		return null;
	}

	public function bool() : ?bool{
		foreach($this->children as $child){
			$value = $child->bool();
			if($value !== null){
				return $value;
			}
		}
		return null;
	}

	public function mapEntry(string $key) : ?ConfigSource{
		$entries = [];
		foreach($this->children as $child){
			$entry = $child->mapEntry($key);
			if($entry !== null){
				$entries[] = $entry;
			}
		}
		return AggregateConfigSource::fromArray($entries);
	}

	public function mapEntries() : ?Iterator{
		$iterators = [];
		foreach($this->children as $child){
			$iterator = $child->mapEntries();
			if($iterator !== null){
				$iterators[] = $iterator;
			}
		}
		if(count($iterators) > 1){
			$sum = [];
			foreach($iterators as $iterator){
				$sum += iterator_to_array($iterator);
			}
			return new ArrayIterator($sum);
		}
		return $iterator[0] ?? null;
	}

	public function listElement(int $index) : ?ConfigSource{
		$i = 0;
		$iter = $this->listElements();
		if($iter === null){
			return null;
		}
		foreach($iter as $element){
			if($i++ === $index){
				return $element;
			}
		}
		return null;
	}

	public function listElements() : ?Iterator{
		$iterators = [];
		foreach($this->children as $child){
			$iterator = $child->mapEntries();
			if($iterator !== null){
				$iterators[] = $iterator;
			}
		}

		if(count($iterators) <= 1){
			return $iterators[0] ?? null;
		}

		return (static function() use ($iterators){
			foreach($iterators as $iter){
				// don't use yield from, otherwise keys are wrong
				foreach($iter as $item){
					yield $item;
				}
			}

		})();
	}

	public function setString(string $value) : bool{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				if($child->setString($value)){
					return true;
				}
			}
		}
		return false;
	}

	public function setInt(int $value) : bool{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				if($child->setInt($value)){
					return true;
				}
			}
		}
		return false;
	}

	public function setFloat(float $value) : bool{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				if($child->setFloat($value)){
					return true;
				}
			}
		}
		return false;
	}

	public function setBool(bool $value) : bool{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				if($child->setBool($value)){
					return true;
				}
			}
		}
		return false;
	}

	public function addMapEntry(string $key, ?string $comment = null) : ?MutableConfigSource{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				$entry = $child->addMapEntry($key, $comment);
				if($entry !== null){
					return $entry;
				}
			}
		}
		return null;
	}

	public function setMapEntryComment(string $key, string $comment) : void{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				$child->setMapEntryComment($key, $comment);
			}
		}
	}

	public function removeMapEntry(string $key) : ?bool{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				if($child->removeMapEntry($key)){
					return true;
				}
			}
		}
		return false;
	}

	public function addListElement() : ?MutableConfigSource{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				$element = $child->addListElement();
				if($element !== null){
					return $element;
				}
			}
		}
		return null;
	}

	public function removeListElement(int $index) : ?bool{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				if($child->removeListElement($index)){
					return true;
				}
			}
		}
		return false;
	}

	public function flush() : void{
		foreach($this->children as $child){
			if($child instanceof MutableConfigSource){
				$child->flush();
			}
		}
	}
}
