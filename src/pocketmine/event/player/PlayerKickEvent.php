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
use pocketmine\lang\TextContainer;
use pocketmine\Player;

/**
 * Called when a player leaves the server
 */
class PlayerKickEvent extends PlayerEvent implements Cancellable{
	/** @var TextContainer|string */
	protected $quitMessage;

	/** @var string */
	protected $reason;

	/**
	 * PlayerKickEvent constructor.
	 *
	 * @param TextContainer|string $quitMessage
	 */
	public function __construct(Player $player, string $reason, $quitMessage){
		$this->player = $player;
		$this->quitMessage = $quitMessage;
		$this->reason = $reason;
	}

	public function setReason(string $reason) : void{
		$this->reason = $reason;
	}

	public function getReason() : string{
		return $this->reason;
	}

	/**
	 * @param TextContainer|string $quitMessage
	 */
	public function setQuitMessage($quitMessage) : void{
		$this->quitMessage = $quitMessage;
	}

	/**
	 * @return TextContainer|string
	 */
	public function getQuitMessage(){
		return $this->quitMessage;
	}
}
