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

/**
 * Block related events
 */
namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Event;

abstract class BlockEvent extends Event{
	/** @var Block */
	protected $block;

	/**
	 * @param Block $block
	 */
	public function __construct(Block $block){
		$this->block = $block;
	}

	/**
	 * @return Block
	 */
	public function getBlock() : Block{
		return $this->block;
	}
}
