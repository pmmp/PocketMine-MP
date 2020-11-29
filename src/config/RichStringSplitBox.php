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

use pocketmine\utils\MutableBox;
use function explode;
use function implode;

final class RichStringSplitBox implements MutableBox{
	/** @var MutableBox<string> */
	private $string;
	/** @var int */
	private $index;

	/**
	 * @param MutableBox $string
	 * @param int        $index
	 * @phpstan-param MutableBox<string> $string
	 */
	public function __construct(MutableBox $string, int $index){
		$this->string = $string;
		$this->index = $index;
	}


	public function getValue(){
		return explode(";", $this->string->getValue())[$this->index];
	}

	public function setValue($value){
		$string = $this->string->getValue();
		$pieces = explode(";", $string);
		$pieces[$this->index] = $value;
		$this->string->setValue(implode(";", $pieces));
	}

	public function hasChanged() : bool{
		return $this->string->hasChanged();
	}

	public function setUnchanged() : void{
		$this->string->setUnchanged();
	}
}
