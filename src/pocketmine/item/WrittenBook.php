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

namespace pocketmine\item;

class WrittenBook extends WritableBook{

	public const GENERATION_ORIGINAL = 0;
	public const GENERATION_COPY = 1;
	public const GENERATION_COPY_OF_COPY = 2;
	public const GENERATION_TATTERED = 3;

	public const TAG_GENERATION = "generation"; //TAG_Int
	public const TAG_AUTHOR = "author"; //TAG_String
	public const TAG_TITLE = "title"; //TAG_String

	public function __construct(int $meta = 0){
		Item::__construct(self::WRITTEN_BOOK, $meta, "Written Book");
	}

	public function getMaxStackSize() : int{
		return 16;
	}

	/**
	 * Returns the generation of the book.
	 * Generations higher than 1 can not be copied.
	 */
	public function getGeneration() : int{
		return $this->getNamedTag()->getInt(self::TAG_GENERATION, -1);
	}

	/**
	 * Sets the generation of a book.
	 */
	public function setGeneration(int $generation) : void{
		if($generation < 0 or $generation > 3){
			throw new \InvalidArgumentException("Generation \"$generation\" is out of range");
		}
		$namedTag = $this->getNamedTag();
		$namedTag->setInt(self::TAG_GENERATION, $generation);
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the author of this book.
	 * This is not a reliable way to get the name of the player who signed this book.
	 * The author can be set to anything when signing a book.
	 */
	public function getAuthor() : string{
		return $this->getNamedTag()->getString(self::TAG_AUTHOR, "");
	}

	/**
	 * Sets the author of this book.
	 */
	public function setAuthor(string $authorName) : void{
		$namedTag = $this->getNamedTag();
		$namedTag->setString(self::TAG_AUTHOR, $authorName);
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the title of this book.
	 */
	public function getTitle() : string{
		return $this->getNamedTag()->getString(self::TAG_TITLE, "");
	}

	/**
	 * Sets the author of this book.
	 */
	public function setTitle(string $title) : void{
		$namedTag = $this->getNamedTag();
		$namedTag->setString(self::TAG_TITLE, $title);
		$this->setNamedTag($namedTag);
	}
}
