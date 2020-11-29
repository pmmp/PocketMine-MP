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
use function array_splice;
use function ctype_digit;
use function explode;
use function implode;
use function is_numeric;
use function strtolower;
use function substr;
use function substr_count;

final class RichStringConfigSource implements MutableConfigSource{
	/**
	 * @var MutableBox
	 * @phpstan-var MutableBox<string>
	 */
	private $box;
	/**
	 * @var Closure
	 * @phpstan-var Closure() : void
	 */
	private $flush;

	/**
	 * @param MutableBox $box
	 * @param Closure    $flush
	 * @phpstan-param MutableBox<string> $box
	 * @phpstan-param Closure() : void $flush
	 */
	public function __construct(MutableBox $box, Closure $flush){
		$this->box = $box;
		$this->flush = $flush;
	}

	public function string() : ?string{
		return $this->box->getValue();
	}

	public function int() : ?int{
		$numeric = $this->box->getValue();
		if(!ctype_digit(substr($numeric, $numeric[0] === "-" ? 1 : 0))){
			return null;
		}
		return (int) $numeric;
	}

	public function float() : ?float{
		$numeric = $this->box->getValue();
		if(!is_numeric($numeric)){
			return null;
		}
		return (float) $numeric;
	}

	public function bool() : ?bool{
		$s = strtolower($this->box->getValue());
		if($s === "y" || $s === "yes" || $s === "true" || $s === "on" || $s === "1"){
			return true;
		}
		if($s === "n" || $s === "no" || $s === "false" || $s === "off" || $s === "0"){
			return false;
		}
		return null;
	}

	public function mapEntry(string $key) : ?ConfigSource{
		return null;
	}

	public function mapEntries() : ?Iterator{
		return null;
	}

	public function listElement(int $index) : ?ConfigSource{
		$pieces = explode(";", $this->box->getValue());
		return isset($pieces[$index]) ? new RichStringConfigSource(new RichStringSplitBox($this->box, $index), $this->flush) : null;
	}

	public function listElements() : ?Iterator{
		$pieces = substr_count($this->box->getValue(), ";") + 1;
		for($i = 0; $i < $pieces; $i++){
			yield new RichStringConfigSource(new RichStringSplitBox($this->box, $i), $this->flush);
		}
	}

	public function setString(string $value) : bool{
		$this->box->setValue($value);
		return true;
	}

	public function setInt(int $value) : bool{
		$this->box->setValue((string) $value);
		return true;
	}

	public function setFloat(float $value) : bool{
		$this->box->setValue((string) $value);
		return true;
	}

	public function setBool(bool $value) : bool{
		$this->box->setValue($value ? "on" : "off");
		return true;
	}

	public function addMapEntry(string $key, ?string $comment = null) : ?MutableConfigSource{
		return null;
	}

	public function removeMapEntry(string $key) : ?bool{
		return null;
	}

	public function setMapEntryComment(string $key, string $comment) : void{
	}

	public function addListElement() : ?MutableConfigSource{
		$value = $this->box->getValue();
		if($value !== ""){
			$value = ";";
		}
		$this->box->setValue($value);
		$index = substr_count($value, ";");
		return new RichStringConfigSource(new RichStringSplitBox($this->box, $index), $this->flush);
	}

	public function removeListElement(int $index) : ?bool{
		$value = $this->box->getValue();
		$pieces = explode(";", $value);
		if(!isset($pieces[$index])){
			return false;
		}
		array_splice($pieces, $index, 1);
		$this->box->setValue(implode(";", $pieces));
		return true;
	}

	public function flush() : void{
		($this->flush)();
	}
}
