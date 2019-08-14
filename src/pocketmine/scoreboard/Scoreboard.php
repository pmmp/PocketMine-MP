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


use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;

class Scoreboard
{
	/**
	 * @var Player
	 */
	protected $player;

	/**
	 * @var bool
	 */
	protected $created = false;

	/**
	 * @var string[]
	 */
	protected $lines = [];

	/**
	 * Scoreboard constructor.
	 * @param Player $player
	 */
	public function __construct(Player $player)
	{
		$this->player = $player;
	}

	/**
	 * @return bool
	 */
	public function isCreated(): bool {
		return $this->created;
	}

	/**
	 * @param string $title
	 * @param bool $oldRemove
	 */
	public final function create(string $title, bool $oldRemove = false) {
		if($this->isCreated() && $oldRemove) {
			$this->destroy();
		}

		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = "sidebar";
		$pk->objectiveName = $this->player->getName();
		$pk->displayName = $title;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;
		$this->player->sendDataPacket($pk);
		$this->created = true;
	}

	public final function destroy() {
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $this->player->getName();
		$this->player->sendDataPacket($pk);
		$this->created = false;
	}

	/**
	 * @param string[] $lines
	 */
	public function setLines(array $lines) {
		if(($linesCount = count($lines)) > 15) {
			throw new \InvalidArgumentException("The number of rows of the scoreboard can be no more than 15. Current value: " . $linesCount);
		}

		$this->lines = $lines;
		$entries = [];
		foreach($lines as $score => $line) {
			$entry = new ScorePacketEntry();
			$entry->objectiveName = $this->player->getName();
			$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
			$entry->customName = $line;
			$entry->score = $score;
			$entry->scoreboardId = $score;
			$entries[] = $entry;
		}
		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_CHANGE;
		$pk->entries = $entries;
		$this->player->sendDataPacket($pk);
	}
}