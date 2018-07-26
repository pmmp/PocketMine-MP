<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\item\WritableBook;
use pocketmine\Player;

class PlayerEditBookEvent extends PlayerEvent implements Cancellable{
	public const ACTION_REPLACE_PAGE = 0;
	public const ACTION_ADD_PAGE = 1;
	public const ACTION_DELETE_PAGE = 2;
	public const ACTION_SWAP_PAGES = 3;
	public const ACTION_SIGN_BOOK = 4;

	/** @var WritableBook */
	private $oldBook;
	/** @var int */
	private $action;
	/** @var WritableBook */
	private $newBook;
	/** @var int[] */
	private $modifiedPages;

	public function __construct(Player $player, WritableBook $oldBook, WritableBook $newBook, int $action, array $modifiedPages){
		$this->player = $player;
		$this->oldBook = $oldBook;
		$this->newBook = $newBook;
		$this->action = $action;
		$this->modifiedPages = $modifiedPages;
	}

	/**
	 * Returns the action of the event.
	 *
	 * @return int
	 */
	public function getAction() : int{
		return $this->action;
	}

	/**
	 * Returns the book before it was modified.
	 *
	 * @return WritableBook
	 */
	public function getOldBook() : WritableBook{
		return $this->oldBook;
	}

	/**
	 * Returns the book after it was modified.
	 * The new book may be a written book, if the book was signed.
	 *
	 * @return WritableBook
	 */
	public function getNewBook() : WritableBook{
		return $this->newBook;
	}

	/**
	 * Sets the new book as the given instance.
	 *
	 * @param WritableBook $book
	 */
	public function setNewBook(WritableBook $book) : void{
		$this->newBook = $book;
	}

	/**
	 * Returns an array containing the page IDs of modified pages.
	 *
	 * @return int[]
	 */
	public function getModifiedPages() : array{
		return $this->modifiedPages;
	}
}