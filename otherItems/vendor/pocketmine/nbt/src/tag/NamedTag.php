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

namespace pocketmine\nbt\tag;


use pocketmine\nbt\NBTStream;
use pocketmine\nbt\ReaderTracker;
use function get_class;
use function str_repeat;
use function strlen;

abstract class NamedTag{
	/** @var string */
	protected $__name;

	/**
	 * Used for recursive cloning protection when cloning tags with child tags.
	 * @var bool
	 */
	protected $cloning = false;

	/**
	 * @param string $name
	 * @throws \InvalidArgumentException if the name is too long
	 */
	public function __construct(string $name = ""){
		if(strlen($name) > 32767){
			throw new \InvalidArgumentException("Tag name cannot be more than 32767 bytes, got length " . strlen($name));
		}
		$this->__name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->__name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name) : void{
		$this->__name = $name;
	}

	abstract public function getValue();

	abstract public function getType() : int;

	abstract public function write(NBTStream $nbt) : void;

	abstract public function read(NBTStream $nbt, ReaderTracker $tracker) : void;

	public function __toString(){
		return $this->toString();
	}

	public function toString(int $indentation = 0) : string{
		return str_repeat("  ", $indentation) . get_class($this) . ": " . ($this->__name !== "" ? "name='$this->__name', " : "") . "value='" . (string) $this->getValue() . "'";
	}

	/**
	 * Clones this tag safely, detecting recursive dependencies which would otherwise cause an infinite cloning loop.
	 * Used for cloning tags in tags that have children.
	 *
	 * @return NamedTag
	 * @throws \RuntimeException if a recursive dependency was detected
	 */
	public function safeClone() : NamedTag{
		if($this->cloning){
			throw new \RuntimeException("Recursive NBT tag dependency detected");
		}
		$this->cloning = true;

		$retval = clone $this;

		$this->cloning = false;
		$retval->cloning = false;

		return $retval;
	}

	/**
	 * Compares this NamedTag to the given NamedTag and determines whether or not they are equal, based on name, type
	 * and value.
	 *
	 * @param NamedTag $that
	 *
	 * @return bool
	 */
	public function equals(NamedTag $that) : bool{
		return $this->__name === $that->__name and $this->equalsValue($that);
	}

	/**
	 * Compares this NamedTag to the given NamedTag and determines whether they are equal, based on type and value only.
	 * Complex tag types should override this to provide proper value comparison.
	 *
	 * @param NamedTag $that
	 *
	 * @return bool
	 */
	protected function equalsValue(NamedTag $that) : bool{
		return $that instanceof $this and $this->getValue() === $that->getValue();
	}
}
