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

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class WrittenBook extends WritableBook{

	const GENERATION_ORIGINAL = 0;
	const GENERATION_COPY = 1;
	const GENERATION_COPY_OF_COPY = 2;
	const GENERATION_TATTERED = 3;

	public function __construct(int $meta = 0){
		Item::__construct(self::WRITTEN_BOOK, $meta, "Written Book");
	}

	public function getMaxStackSize() : int{
		return 16;
	}

	/**
	 * Returns the generation of the book.
	 * Generations higher than 1 can not be copied.
	 *
	 * @return int
	 */
	public function getGeneration() : int{
		if(!isset($this->getNamedTag()->generation)) {
			return -1;
		}
		return $this->getNamedTag()->generation->getValue();
	}

	/**
	 * Sets the generation of a book.
	 *
	 * @param int $generation
	 */
	public function setGeneration(int $generation) : void{
		if($generation < 0 or $generation > 3){
			throw new \InvalidArgumentException("Generation \"$generation\" is out of range");
		}
		$namedTag = $this->getCorrectedNamedTag();

		if(isset($namedTag->generation)){
			$namedTag->generation->setValue($generation);
		}else{
			$namedTag->generation = new IntTag("generation", $generation);
		}
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the author of this book.
	 * This is not a reliable way to get the name of the player who signed this book.
	 * The author can be set to anything when signing a book.
	 *
	 * @return string
	 */
	public function getAuthor() : string{
		if(!isset($this->getNamedTag()->author)){
			return "";
		}
		return $this->getNamedTag()->author->getValue();
	}

	/**
	 * Sets the author of this book.
	 *
	 * @param string $authorName
	 */
	public function setAuthor(string $authorName) : void{
		$namedTag = $this->getCorrectedNamedTag();
		if(isset($namedTag->author)){
			$namedTag->author->setValue($authorName);
		}else{
			$namedTag->author = new StringTag("author", $authorName);
		}
		$this->setNamedTag($namedTag);
	}

	/**
	 * Returns the title of this book.
	 *
	 * @return string
	 */
	public function getTitle() : string{
		if(!isset($this->getNamedTag()->title)){
			return "";
		}
		return $this->getNamedTag()->title->getValue();
	}

	/**
	 * Sets the author of this book.
	 *
	 * @param string $title
	 */
	public function setTitle(string $title) : void{
		$namedTag = $this->getCorrectedNamedTag();
		if(isset($namedTag->title)){
			$namedTag->title->setValue($title);
		}else{
			$namedTag->title = new StringTag("title", $title);
		}
		$this->setNamedTag($namedTag);
	}
}