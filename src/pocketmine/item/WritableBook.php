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
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class WritableBook extends Item{

	public function __construct(int $meta = 0){
		parent::__construct(self::WRITABLE_BOOK, $meta, "Book & Quill");
	}

	/**
	 * Returns whether the given page exists in this book.
	 *
	 * @param int $pageId
	 *
	 * @return bool
	 */
	public function pageExists(int $pageId) : bool{
		return isset($this->getNamedTag()->pages->{$pageId});
	}

	/**
	 * Returns a string containing the content of a page (which could be empty), or null if the page doesn't exist.
	 *
	 * @param int $pageId
	 *
	 * @return string|null
	 */
	public function getPageText(int $pageId) : ?string{
		if(!$this->pageExists($pageId)){
			return null;
		}
		return $this->getNamedTag()->pages->{$pageId}->text->getValue();
	}

	/**
	 * Sets the text of a page in the book. Adds the page if the page does not yet exist.
	 *
	 * @param int    $pageId
	 * @param string $pageText
	 *
	 * @return bool indicating whether the page was created or not.
	 */
	public function setPageText(int $pageId, string $pageText) : bool{
		$created = false;
		if(!$this->pageExists($pageId)){
			$this->addPage($pageId);
			$created = true;
		}

		$namedTag = $this->getNamedTag();
		$namedTag->pages->{$pageId}->text->setValue($pageText);
		$this->setNamedTag($namedTag);

		return $created;
	}

	/**
	 * Adds a new page with the given page ID.
	 * Creates a new page for every page between the given ID and existing pages that doesn't yet exist.
	 *
	 * @param int $pageId
	 */
	public function addPage(int $pageId) : void{
		if($pageId < 0){
			throw new \InvalidArgumentException("Page number \"$pageId\" is out of range");
		}
		$namedTag = $this->getNamedTag();

		if(!isset($namedTag->pages) or !($namedTag->pages instanceof ListTag)){
			$namedTag->pages = new ListTag("pages", []);
		}

		for($id = 0; $id <= $pageId; $id++){
			if(!$this->pageExists($id)){
				$namedTag->pages->{$id} = new CompoundTag("", [
					new StringTag("text", ""),
					new StringTag("photoname", "")
				]);
			}
		}

		$this->setNamedTag($namedTag);
	}

	/**
	 * Deletes an existing page with the given page ID.
	 *
	 * @param int $pageId
	 *
	 * @return bool indicating success
	 */
	public function deletePage(int $pageId) : bool{
		if(!$this->pageExists($pageId)){
			return false;
		}

		$namedTag = $this->getNamedTag();
		unset($namedTag->pages->{$pageId});
		$this->pushPages($pageId, $namedTag);
		$this->setNamedTag($namedTag);

		return true;
	}

	/**
	 * Inserts a new page with the given text and moves other pages upwards.
	 *
	 * @param int $pageId
	 * @param string $pageText
	 *
	 * @return bool indicating success
	 */
	public function insertPage(int $pageId, string $pageText = "") : bool{
		$namedTag = $this->getNamedTag();
		if(!isset($namedTag->pages) or !($namedTag->pages instanceof ListTag)){
			$namedTag->pages = new ListTag("pages", []);
		}
		$this->pushPages($pageId, $namedTag, false);

		$namedTag->pages->{$pageId}->text->setValue($pageText);
		$this->setNamedTag($namedTag);
		return true;
	}

	/**
	 * Switches the text of two pages with each other.
	 *
	 * @param int $pageId1
	 * @param int $pageId2
	 *
	 * @return bool indicating success
	 */
	public function swapPages(int $pageId1, int $pageId2) : bool{
		if(!$this->pageExists($pageId1) or !$this->pageExists($pageId2)){
			return false;
		}

		$pageContents1 = $this->getPageText($pageId1);
		$pageContents2 = $this->getPageText($pageId2);
		$this->setPageText($pageId1, $pageContents2);
		$this->setPageText($pageId2, $pageContents1);

		return true;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	/**
	 * @param int         $pageId
	 * @param CompoundTag $namedTag
	 * @param bool        $downwards
	 *
	 * @return bool
	 */
	private function pushPages(int $pageId, CompoundTag $namedTag, bool $downwards = true) : bool{
		$pages = $this->getPages();
		if(empty($pages)){
			return false;
		}

		$type = $downwards ? -1 : 1;
		foreach($pages as $key => $page){
			if(($key <= $pageId and $downwards) or ($key < $pageId and !$downwards)){
				continue;
			}

			if($downwards){
				unset($namedTag->pages->{$key});
			}
			$namedTag->pages->{$key + $type} = new CompoundTag("", [
				new StringTag("text", $page->text->getValue()),
				new StringTag("photoname", "")
			]);
		}
		return true;
	}

	/**
	 * Returns an array containing all pages of this book.
	 *
	 * @return CompoundTag[]
	 */
	public function getPages() : array{
		$namedTag = $this->getNamedTag();
		if(!isset($namedTag->pages)){
			return [];
		}

		return array_filter((array) $namedTag->pages, function(string $key){
			return is_numeric($key);
		}, ARRAY_FILTER_USE_KEY);
	}
}