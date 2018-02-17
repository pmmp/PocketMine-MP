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

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\Player;

/**
 * Called when a sign is changed by a player.
 */
class SignChangeEvent extends BlockEvent implements Cancellable{
	public static $handlerList = null;

	/** @var Player */
	private $player;
	/** @var string[] */
	private $lines = [];

	/**
	 * @param Block    $theBlock
	 * @param Player   $thePlayer
	 * @param string[] $theLines
	 */
	public function __construct(Block $theBlock, Player $thePlayer, array $theLines){
		parent::__construct($theBlock);
		$this->player = $thePlayer;
		$this->setLines($theLines);
	}

	/**
	 * @return Player
	 */
	public function getPlayer() : Player{
		return $this->player;
	}

	/**
	 * @return string[]
	 */
	public function getLines() : array{
		return $this->lines;
	}

	/**
	 * @param int $index 0-3
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException if the index is out of bounds
	 */
	public function getLine(int $index) : string{
		if($index < 0 or $index > 3){
			throw new \InvalidArgumentException("Index must be in the range 0-3!");
		}

		return $this->lines[$index];
	}

	/**
	 * @param string[] $lines
	 *
	 * @throws \InvalidArgumentException if there are more or less than 4 lines in the passed array
	 */
	public function setLines(array $lines){
		if(count($lines) !== 4){
			throw new \InvalidArgumentException("Array size must be 4!");
		}
		$this->lines = $lines;
	}

	/**
	 * @param int    $index 0-3
	 * @param string $line
	 *
	 * @throws \InvalidArgumentException if the index is out of bounds
	 */
	public function setLine(int $index, string $line){
		if($index < 0 or $index > 3){
			throw new \InvalidArgumentException("Index must be in the range 0-3!");
		}
		$this->lines[$index] = $line;
	}
}
