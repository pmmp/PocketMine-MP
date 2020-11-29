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
use pocketmine\utils\Config;
use pocketmine\utils\MutableBox;
use pocketmine\utils\ProjectionBox;
use pocketmine\utils\SimpleMutableBox;
use function array_keys;
use function date;
use function fclose;
use function file_get_contents;
use function fopen;
use function fwrite;
use function preg_match_all;
use const PREG_SET_ORDER;

final class PropertiesConfigSource implements MutableConfigSource{
	/** @var string */
	private $file;
	/** @var MutableBox<string, string> */
	private $box;

	/** @var array<string, string> */
	private $comments = [];

	public static function fromFile(string $file){
		$string = file_get_contents($file);
		preg_match_all(Config::PROPERTIES_REGEX, $string, $matches, PREG_SET_ORDER);
		$array = [];
		foreach($matches as [, $k, $v]){
			$array[$k] = $v;
		}

		return new self($file, new SimpleMutableBox($array));
	}

	private function __construct(string $file, MutableBox $box){
		$this->file = $file;
		$this->box = $box;
	}

	public function flush() : void{
		if(!$this->box->hasChanged()){
			return;
		}
		$handle = fopen($this->file, "wb");
		try{
			fwrite($handle, "#Properties Config file\r\n#" . date("D M j H:i:s T Y") . "\r\n");

			foreach($this->box->getValue() as $k => $v){
				fwrite($handle, $k . "=" . $v . "\r\n");
			}
		}finally{
			fclose($handle);
		}
	}

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
		if(!isset($this->box->getValue()[$key])){
			return null;
		}
		return new RichStringConfigSource(new ProjectionBox($this->box, $key), Closure::fromCallable([$this, "flush"]));
	}

	public function mapEntries() : ?Iterator{
		$keys = array_keys($this->box->getValue());
		foreach($keys as $key){
			yield new RichStringConfigSource(new ProjectionBox($this->box, $key), Closure::fromCallable([$this, "flush"]));
		}
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
		$value = $this->box->getValue();
		$value[$key] = "";
		$this->box->setValue($value);
		$this->comments[$key] = $comment;
		return new RichStringConfigSource(new ProjectionBox($this->box, $key), Closure::fromCallable([$this, "flush"]));
	}

	public function removeMapEntry(string $key) : ?bool{
		$value = $this->box->getValue();
		if(!isset($value[$key])){
			return false;
		}
		unset($value[$key]);
		unset($this->comments[$key]);
		return false;
	}

	public function setMapEntryComment(string $key, string $comment) : void{
		$this->comments[$key] = $comment;
	}

	public function addListElement() : ?MutableConfigSource{
		return null;
	}

	public function removeListElement(int $index) : ?bool{
		return null;
	}
}
