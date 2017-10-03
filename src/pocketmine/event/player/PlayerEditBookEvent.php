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

declare(strict_types = 1);

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\item\WritableBook;
use pocketmine\Player;

class PlayerEditBookEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	const ACTION_EDIT_PAGE = 0;
	const ACTION_DELETE_PAGE = 1;
	const ACTION_SWAP_PAGES = 2;
	const ACTION_SIGN_BOOK = 3;

	/** @var WritableBook */
	private $book;
	/** @var int */
	private $action;

	public function __construct(Player $player, WritableBook $book, int $action){
		$this->player = $player;
		$this->book = $book;
		$this->action = $action;
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
	 * Returns the book that was modified.
	 *
	 * @return WritableBook
	 */
	public function getBook() : WritableBook{
		return $this->book;
	}
}