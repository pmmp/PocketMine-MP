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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\tile\NoteBlock as TileNoteBlock;

class Noteblock extends Solid{

	// TODO: Redstone power

	protected $id = self::NOTE_BLOCK;

	public function __construct(){

	}

	public function getHardness() : float{
		return 0.8;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$tile = $this->level->getTile($this);
		if($tile instanceof TileNoteBlock){
			$tile->changePitch();

			return $tile->triggerNote();
		}

		return false;
	}

	public function getName() : string{
		return "Noteblock";
	}

	public function getFuelTime() : int{
		return 300;
	}
}