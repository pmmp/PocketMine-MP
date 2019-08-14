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

namespace pocketmine\scoreboard;

use pocketmine\Player;

class ScoreboardManager
{
	/** @var Scoreboard[] */
	private $scoreboards = [];

	/**
	 * @var string
	 */
	private $baseClass = Scoreboard::class;

	/**
	 * @var
	 */
	private $currentClass = Scoreboard::class;

	/**
	 * Create scoreboard instance from player
	 *
	 * @param Player $player
	 * @param string|null $title
	 * @return Scoreboard
	 */
	public function create(Player $player, ?string $title = null): Scoreboard {
		if(isset($this->scoreboards[$player->getName()])) {
			$scoreboard = $this->scoreboards[$player->getName()];
		} else {
			$scoreboard = new $this->currentClass($player);
			$this->scoreboards[$player->getName()] =& $scoreboard;
		}

		if($title) {
			$scoreboard->create($title, true);
		}

		return $this->scoreboards[$player->getName()];
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function destroy(Player $player): bool {
		if(!isset($this->scoreboards[$player->getName()])) {
			return false;
		}

		$this->scoreboards[$player->getName()]->destroy();
		return true;
	}

	/**
	 * @return string
	 */
	public function getScoreboardClass(): string {
		return $this->currentClass;
	}

	/**
	 * @param $class
	 */
	public function setScoreboardClass($class) {
		if(!is_a($class, $this->baseClass, true)) {
			throw new \RuntimeException("Class $class must extend" . $this->baseClass);
		}

		$this->currentClass = $class;
	}
}