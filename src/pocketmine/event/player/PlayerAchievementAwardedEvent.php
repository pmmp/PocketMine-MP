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

use pocketmine\command\CommandSender;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\lang\TextContainer;
use pocketmine\player\Player;

/**
 * Called when a player is awarded an achievement
 */
class PlayerAchievementAwardedEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	/** @var string */
	protected $achievement;
	/** @var TextContainer|null */
	private $message;
	/** @var CommandSender[] */
	private $broadcastRecipients;

	/**
	 * @param Player             $player
	 * @param string             $achievementId
	 * @param TextContainer|null $message
	 * @param CommandSender[]    $messageRecipients
	 */
	public function __construct(Player $player, string $achievementId, ?TextContainer $message, array $messageRecipients){
		$this->player = $player;
		$this->achievement = $achievementId;
		$this->message = $message;
		$this->broadcastRecipients = $messageRecipients;
	}

	/**
	 * @return string
	 */
	public function getAchievement() : string{
		return $this->achievement;
	}

	/**
	 * @return TextContainer|null
	 */
	public function getMessage() : ?TextContainer{
		return $this->message;
	}

	/**
	 * @param TextContainer|null $message
	 */
	public function setMessage(?TextContainer $message) : void{
		$this->message = $message;
	}

	/**
	 * @return CommandSender[]
	 */
	public function getBroadcastRecipients() : array{
		return $this->broadcastRecipients;
	}

	/**
	 * @param CommandSender[] $broadcastRecipients
	 */
	public function setBroadcastRecipients(array $broadcastRecipients) : void{
		$this->broadcastRecipients = $broadcastRecipients;
	}
}
