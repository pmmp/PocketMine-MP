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

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class WritableBook extends Item{

	public const TAG_PAGES = "pages"; //TAG_List<TAG_Compound>
	public const TAG_PAGE_TEXT = "text"; //TAG_String
	public const TAG_PAGE_PHOTONAME = "photoname"; //TAG_String - TODO

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
		return isset($this->getPages()[$pageId]);
	}

	/**
	 * Returns a string containing the content of a page (which could be empty), or null if the page doesn't exist.
	 *
	 * @param int $pageId
	 *
	 * @return string|null
	 */
	public function getPageText(int $pageId) : ?string{
		$pages = $this->getNamedTag()->getListTag(self::TAG_PAGES);
		if($pages === null){
			return null;
		}

		$page = $pages[$pageId] ?? null;
		if($page instanceof CompoundTag){
			return $page->getString(self::TAG_PAGE_TEXT, "");
		}

		return null;
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
		/** @var CompoundTag[]|ListTag $pages */
		$pages = $namedTag->getListTag(self::TAG_PAGES);
		assert($pages instanceof ListTag);
		$pages[$pageId]->setString(self::TAG_PAGE_TEXT, $pageText);

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

		$pages = $this->getPages();

		for($id = 0; $id <= $pageId; $id++){
			if(!isset($pages[$id])){
				$pages[$id] = new CompoundTag("", [
					new StringTag(self::TAG_PAGE_TEXT, ""),
					new StringTag(self::TAG_PAGE_PHOTONAME, "")
				]);
			}
		}

		$this->setPages($pages);
	}

	/**
	 * Deletes an existing page with the given page ID.
	 *
	 * @param int $pageId
	 *
	 * @return bool indicating success
	 */
	public function deletePage(int $pageId) : bool{
		$pages = $this->getPages();
		unset($pages[$pageId]);

		$this->setPages(array_values($pages));

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
		$pages = $this->getPages();

		$this->setPages(array_merge(
			array_slice($pages, 0, $pageId),
			[new CompoundTag("", [
				new StringTag(self::TAG_PAGE_TEXT, $pageText),
				new StringTag(self::TAG_PAGE_PHOTONAME, "")
			])],
			array_slice($pages, $pageId)
		));

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
	 * Returns an array containing all pages of this book.
	 *
	 * @return CompoundTag[]
	 */
	public function getPages() : array{
		$pages = $this->getNamedTag()->getListTag(self::TAG_PAGES);
		if($pages === null){
			return [];
		}

		return $pages->getValue();
	}

	/**
	 *
	 * @param CompoundTag[] $pages
	 */
	public function setPages(array $pages) : void{
		$nbt = $this->getNamedTag();
		$nbt->setTag(new ListTag(self::TAG_PAGES, $pages, NBT::TAG_Compound));
		$this->setNamedTag($nbt);
	}
}
