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

namespace pocketmine\block\utils;

use function array_fill;
use function array_pad;
use function array_slice;
use function count;
use function explode;
use function is_int;
use function strpos;

class SignText{
	public const LINE_COUNT = 4;

	/** @var string[] */
	private $lines;

	/**
	 * @param string[] $lines
	 * @throws \InvalidArgumentException
	 */
	public function __construct(?array $lines = null){
		$this->setLines($lines ?? array_fill(0, self::LINE_COUNT, ""));
	}

	/**
	 * Parses sign lines from the given string blob.
	 * TODO: add a strict mode for this
	 *
	 * @param string $blob
	 *
	 * @return SignText
	 * @throws \InvalidArgumentException if the text is not valid UTF-8
	 */
	public static function fromBlob(string $blob) : SignText{
		return new self(array_slice(array_pad(explode("\n", $blob), self::LINE_COUNT, ""), 0, self::LINE_COUNT));
	}

	/**
	 * Returns an array of lines currently on the sign.
	 *
	 * @return string[]
	 */
	public function getLines() : array{
		return $this->lines;
	}

	/**
	 * Sets the sign text.
	 *
	 * @param string[] $lines index-sensitive; omitting an index will leave it unchanged
	 *
	 * @throws \InvalidArgumentException if the array size is greater than 4
	 * @throws \InvalidArgumentException if invalid keys (out of bounds or string) are found in the array
	 */
	public function setLines(array $lines) : void{
		if(count($lines) > self::LINE_COUNT){
			throw new \InvalidArgumentException("Expected at most 4 lines, got " . count($lines));
		}
		foreach($lines as $k => $line){
			$this->checkLineIndex($k);
			$this->setLine($k, $line);
		}
	}

	private function checkLineIndex($index) : void{
		if(!is_int($index)){
			throw new \InvalidArgumentException("Index must be an integer");
		}
		if($index < 0 or $index >= self::LINE_COUNT){
			throw new \InvalidArgumentException("Line index is out of bounds");
		}
	}

	/**
	 * Returns the sign line at the given offset.
	 *
	 * @param int $index
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getLine(int $index) : string{
		$this->checkLineIndex($index);
		return $this->lines[$index];
	}

	/**
	 * Sets the line at the given offset.
	 *
	 * @param int    $index
	 * @param string $line
	 *
	 * @throws \InvalidArgumentException if the text is not valid UTF-8
	 * @throws \InvalidArgumentException if the text contains a newline
	 */
	public function setLine(int $index, string $line) : void{
		$this->checkLineIndex($index);
		if(!mb_check_encoding($line, 'UTF-8')){
			throw new \InvalidArgumentException("Line must be valid UTF-8 text");
		}
		if(strpos($line, "\n") !== false){
			throw new \InvalidArgumentException("Line must not contain newlines");
		}
		//TODO: add length checks
		$this->lines[$index] = $line;
	}
}
