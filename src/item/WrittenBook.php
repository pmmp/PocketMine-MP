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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Utils;

class WrittenBook extends WritableBookBase{

	public const GENERATION_ORIGINAL = 0;
	public const GENERATION_COPY = 1;
	public const GENERATION_COPY_OF_COPY = 2;
	public const GENERATION_TATTERED = 3;

	public const TAG_GENERATION = "generation"; //TAG_Int
	public const TAG_AUTHOR = "author"; //TAG_String
	public const TAG_TITLE = "title"; //TAG_String

	/** @var int */
	private $generation = self::GENERATION_ORIGINAL;
	/** @var string */
	private $author = "";
	/** @var string */
	private $title = "";

	public function getMaxStackSize() : int{
		return 16;
	}

	/**
	 * Returns the generation of the book.
	 * Generations higher than 1 can not be copied.
	 */
	public function getGeneration() : int{
		return $this->generation;
	}

	/**
	 * Sets the generation of a book.
	 *
	 * @return $this
	 */
	public function setGeneration(int $generation) : self{
		if($generation < 0 or $generation > 3){
			throw new \InvalidArgumentException("Generation \"$generation\" is out of range");
		}

		$this->generation = $generation;
		return $this;
	}

	/**
	 * Returns the author of this book.
	 * This is not a reliable way to get the name of the player who signed this book.
	 * The author can be set to anything when signing a book.
	 */
	public function getAuthor() : string{
		return $this->author;
	}

	/**
	 * Sets the author of this book.
	 *
	 * @return $this
	 */
	public function setAuthor(string $authorName) : self{
		Utils::checkUTF8($authorName);
		$this->author = $authorName;
		return $this;
	}

	/**
	 * Returns the title of this book.
	 */
	public function getTitle() : string{
		return $this->title;
	}

	/**
	 * Sets the author of this book.
	 *
	 * @return $this
	 */
	public function setTitle(string $title) : self{
		Utils::checkUTF8($title);
		$this->title = $title;
		return $this;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);
		$this->generation = $tag->getInt(self::TAG_GENERATION, $this->generation);
		$this->author = $tag->getString(self::TAG_AUTHOR, $this->author);
		$this->title = $tag->getString(self::TAG_TITLE, $this->title);
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);
		$tag->setInt(self::TAG_GENERATION, $this->generation);
		$tag->setString(self::TAG_AUTHOR, $this->author);
		$tag->setString(self::TAG_TITLE, $this->title);
	}
}
