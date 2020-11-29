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
use function array_reverse;
use function count;
use function iterator_to_array;

final class Config implements MutableConfigSource{
	/** @var ConfigSource[] */
	private $sources = [];

	public function addSource(array $prefixPath, ConfigSource $source) : void{
		foreach(array_reverse($prefixPath) as $prefix){
			$source = new ConfigSourceSingletonGroup($prefix, $source);
		}
		$this->sources[] = $source;
	}

	// the top-level config must be a mapping

	public function string() : ?string{
		return null;
	}

	public function int() : ?int{
		return null;
	}

	public function float() : ?float{
		return null;
	}

	public function bool() : ?bool{
		return null;
	}

	public function mapEntry(string $key) : ?ConfigSource{
		$entries = [];
		foreach($this->sources as $source){
			$entry = $source->mapEntry($key);
			if($entry !== null){
				$entries[] = $entry;
			}
		}
		return AggregateConfigSource::fromArray($entries);
	}

	public function mapEntries() : ?Iterator{
		$iterators = [];
		foreach($this->sources as $source){
			$iterator = $source->mapEntries();
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
		return null;
	}

	public function listElements() : ?Iterator{
		return null;
	}

	public function setString(string $value) : bool{
		return false;
	}

	public function setInt(int $value) : bool{
		return false;
	}

	public function setFloat(float $value) : bool{
		return false;
	}

	public function setBool(bool $value) : bool{
		return false;
	}

	public function addMapEntry(string $key, ?string $comment = null) : ?MutableConfigSource{
		// need to traverse at least one level in
		return null;
	}

	public function setMapEntryComment(string $key, string $comment) : void{
		// this does not make sense
	}

	public function removeMapEntry(string $key) : ?bool{
		// need to traverse at least one level in
		return null;
	}

	public function addListElement() : ?MutableConfigSource{
		return null;
	}

	public function removeListElement(int $index) : ?bool{
		return null;
	}

	public function flush() : void{
		foreach($this->sources as $source){
			if($source instanceof MutableConfigSource){
				$source->flush();
			}
		}
	}


	public function getNestedSource(array $path) : ?ConfigSource{
		$source = $this;
		foreach($path as $component){
			$source = $source->mapEntry($component);
			if($source === null){
				return null;
			}
		}
		return $source;
	}

	/**
	 * @tmeplate       T
	 * @param string[] $path
	 * @param mixed    $default
	 * @return string|mixed
	 * @phpstan-param  T $default
	 * @phpstan-return string|T
	 */
	public function getNestedStringOr(array $path, $default){
		$source = $this->getNestedSource($path);
		if($source === null){
			return $default;
		}
		$value = $source->string();
		if($value === null){
			return $default;
		}
		return $value;
	}

	/**
	 * @param string[] $path
	 * @return string
	 * @throws MissingConfigException if the entry is missing or has the wrong type
	 */
	public function getNestedString(array $path) : string{
		$ret = $this->getNestedStringOr($path, null);
		if($ret === null){
			throw new MissingConfigException($path, "string");
		}
		return $ret;
	}

	/**
	 * @tmeplate       T
	 * @param string[] $path
	 * @param mixed    $default
	 * @return int|mixed
	 * @phpstan-param  T $default
	 * @phpstan-return int|T
	 */
	public function getNestedIntOr(array $path, $default){
		$source = $this->getNestedSource($path);
		if($source === null){
			return $default;
		}
		$value = $source->int();
		if($value === null){
			return $default;
		}
		return $value;
	}

	/**
	 * @param string[] $path
	 * @return int
	 * @throws MissingConfigException if the entry is missing or has the wrong type
	 */
	public function getNestedInt(array $path) : int{
		$ret = $this->getNestedIntOr($path, null);
		if($ret === null){
			throw new MissingConfigException($path, "int");
		}
		return $ret;
	}

	/**
	 * @tmeplate       T
	 * @param string[] $path
	 * @param mixed    $default
	 * @return float|mixed
	 * @phpstan-param  T $default
	 * @phpstan-return float|T
	 */
	public function getNestedFloatOr(array $path, $default){
		$source = $this->getNestedSource($path);
		if($source === null){
			return $default;
		}
		$value = $source->float();
		if($value === null){
			return $default;
		}
		return $value;
	}

	/**
	 * @param string[] $path
	 * @return float
	 * @throws MissingConfigException if the entry is missing or has the wrong type
	 */
	public function getNestedFloat(array $path) : float{
		$ret = $this->getNestedFloatOr($path, null);
		if($ret === null){
			throw new MissingConfigException($path, "float");
		}
		return $ret;
	}

	/**
	 * @tmeplate       T
	 * @param string[] $path
	 * @param mixed    $default
	 * @return bool|mixed
	 * @phpstan-param  T $default
	 * @phpstan-return bool|T
	 */
	public function getNestedBoolOr(array $path, $default){
		$source = $this->getNestedSource($path);
		if($source === null){
			return $default;
		}
		$value = $source->bool();
		if($value === null){
			return $default;
		}
		return $value;
	}

	/**
	 * @param string[] $path
	 * @return bool
	 * @throws MissingConfigException if the entry is missing or has the wrong type
	 */
	public function getNestedBool(array $path) : bool{
		$ret = $this->getNestedBoolOr($path, null);
		if($ret === null){
			throw new MissingConfigException($path, "bool");
		}
		return $ret;
	}
}
