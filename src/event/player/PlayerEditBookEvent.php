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

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\WritableBookBase;
use pocketmine\player\Player;

class PlayerEditBookEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	public const ACTION_REPLACE_PAGE = 0;
	public const ACTION_ADD_PAGE = 1;
	public const ACTION_DELETE_PAGE = 2;
	public const ACTION_SWAP_PAGES = 3;
	public const ACTION_SIGN_BOOK = 4;

	/** @var WritableBookBase */
	private $oldBook;
	/** @var int */
	private $action;
	/** @var WritableBookBase */
	private $newBook;
	/** @var int[] */
	private $modifiedPages;

	public function __construct(Player $player, WritableBookBase $oldBook, WritableBookBase $newBook, int $action, array $modifiedPages){
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
	 * @return WritableBookBase
	 */
	public function getOldBook() : WritableBookBase{
		return $this->oldBook;
	}

	/**
	 * Returns the book after it was modified.
	 * The new book may be a written book, if the book was signed.
	 *
	 * @return WritableBookBase
	 */
	public function getNewBook() : WritableBookBase{
		return $this->newBook;
	}

	/**
	 * Sets the new book as the given instance.
	 *
	 * @param WritableBookBase $book
	 */
	public function setNewBook(WritableBookBase $book) : void{
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
