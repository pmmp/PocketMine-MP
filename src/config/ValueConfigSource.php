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

use Closure;
use Iterator;
use pocketmine\utils\MutableBox;
use pocketmine\utils\ProjectionBox;
use pocketmine\utils\SimpleMutableBox;
use pocketmine\utils\Utils;
use function array_keys;
use function array_splice;
use function count;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
use function yaml_emit;
use function yaml_parse;

final class ValueConfigSource implements MutableConfigSource{
	/** @var MutableBox<mixed> */
	private $box;
	/**
	 * @var Closure
	 * @phpstan-var Closure() : void
	 */
	private $flush;

	/**
	 * Creates a ConfigSource that reads from and writes to `$value`
	 * and calls `$flush` when the source is flushed.
	 *
	 * @param MutableBox $value
	 * @param Closure    $flush
	 * @phpstan-param MutableBox<mixed> $value
	 * @phpstan-param Closure() : void $flush
	 */
	public function __construct(MutableBox $value, Closure $flush){
		$this->box = new SimpleMutableBox($value);
		$this->flush = $flush;
	}

	public function flush() : void{
		if($this->box->hasChanged()){
			($this->flush)();
		}
	}

	public static function fromYamlFile(string $file) : self{
		$value = yaml_parse(file_get_contents($file));
		$box = new SimpleMutableBox($value);
		$flush = static function() use ($file, $box) : void{
			$value = $box->getValue();
			file_put_contents($file, yaml_emit($value));
		};
		return new self($box, $flush);
	}

	public static function fromJsonFile(string $file) : self{
		$value = json_decode(file_get_contents($file));
		$box = new SimpleMutableBox($value);
		$flush = static function() use ($file, $box) : void{
			$value = $box->getValue();
			file_put_contents($file, json_encode($value));
		};
		return new self($box, $flush);
	}

	public function string() : ?string{
		$value = $this->box->getValue();
		return is_string($value) ? $value : null;
	}

	public function int() : ?int{
		$value = $this->box->getValue();
		return is_int($value) ? $value : null;
	}

	public function float() : ?float{
		$value = $this->box->getValue();
		return is_int($value) || is_float($value) ? $value : null;
	}

	public function bool() : ?bool{
		$value = $this->box->getValue();
		return is_bool($value) ? $value : null;
	}

	public function mapEntry(string $key) : ?ConfigSource{
		$value = $this->box->getValue();
		if(!is_array($value) || Utils::isArrayLinear($value) || !isset($value[$key])){
			return null;
		}
		return new ValueConfigSource(new ProjectionBox($this->box, $key), $this->flush);
	}

	public function mapEntries() : ?Iterator{
		$value = $this->box->getValue();
		if(!is_array($value) || Utils::isArrayLinear($value)){
			return null;
		}
		$keys = array_keys($value);
		$flush = $this->flush;
		return (static function() use ($keys, $flush){
			foreach($keys as $k){
				yield $k => new ValueConfigSource(new ProjectionBox($this->box, $k), $flush);
			}
		})();
	}

	public function listElement(int $index) : ?ConfigSource{
		$value = $this->box->getValue();
		if(!is_array($value) || !Utils::isArrayLinear($value) || !isset($value[$index])){
			return null;
		}
		return new ValueConfigSource(new ProjectionBox($this->box, $index), $this->flush);
	}

	public function listElements() : ?Iterator{
		$value = $this->box->getValue();
		if(!is_array($value) || !Utils::isArrayLinear($value)){
			return null;
		}
		$keys = array_keys($value);
		$flush = $this->flush;
		return (static function() use ($keys, $flush){
			foreach($keys as $k){
				yield $k => new ValueConfigSource(new ProjectionBox($this->box, $k), $flush);
			}
		})();
	}

	public function setString(string $value) : bool{
		if(!is_string($this->box->getValue())){
			return false;
		}
		$this->box->setValue($value);
		return true;
	}

	public function setInt(int $value) : bool{
		if(!is_int($this->box->getValue())){
			return false;
		}
		$this->box->setValue($value);
		return true;
	}

	public function setFloat(float $value) : bool{
		if(!is_float($this->box->getValue())){
			return false;
		}
		$this->box->setValue($value);
		return true;
	}

	public function setBool(bool $value) : bool{
		if(!is_bool($this->box->getValue())){
			return false;
		}
		$this->box->setValue($value);
		return true;
	}

	public function addMapEntry(string $key, ?string $comment = null) : ?MutableConfigSource{
		$value = $this->box->getValue();
		if(!is_array($value) || Utils::isArrayLinear($value) || isset($value[$key])){
			return null;
		}

		$value[$key] = null;
		$this->box->setValue($value);
		return new ValueConfigSource(new ProjectionBox($this->box, $key), $this->flush);
	}

	public function setMapEntryComment(string $key, string $comment) : void{
		// unimplemented
	}

	public function removeMapEntry(string $key) : ?bool{
		$value = $this->box->getValue();
		if(!is_array($value) || Utils::isArrayLinear($value)){
			return null;
		}

		if(!isset($value[$key])){
			return false;
		}

		unset($value[$key]);
		return true;
	}

	public function addListElement() : ?MutableConfigSource{
		$value = $this->box->getValue();
		if(!is_array($value) || !Utils::isArrayLinear($value)){
			return null;
		}

		$index = count($value);
		$value[] = null;
		$this->box->setValue($value);
		return new ValueConfigSource(new ProjectionBox($this->box, $index), $this->flush);
	}

	public function removeListElement(int $index) : ?bool{
		$value = $this->box->getValue();
		if(!is_array($value) || !Utils::isArrayLinear($value)){
			return null;
		}

		if($index >= count($value)){
			return false;
		}

		array_splice($value, $index, 1);
		$this->box->setValue($value);
		return true;
	}
}
