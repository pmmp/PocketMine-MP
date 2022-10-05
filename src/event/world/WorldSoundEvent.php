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

namespace pocketmine\event\world;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\Sound;
use pocketmine\world\World;

/**
 * Called when a sound is played in a world using World->playSound()
 * @see World::playSound()
 */
class WorldSoundEvent extends WorldEvent implements Cancellable{
	use CancellableTrait;

	/**
	 * @param Player[]|null $players
	 */
	public function __construct(
		World $world,
		private Vector3 $position,
		private Sound $sound,
		private ?array $players = null
	){
		parent::__construct($world);
	}

	public function getPosition() : Vector3{
		return $this->position;
	}

	public function setPosition(Vector3 $position) : void{
		$this->position = $position;
	}

	public function getSound() : Sound{
		return $this->sound;
	}

	public function setSound(Sound $sound) : void{
		$this->sound = $sound;
	}

	/**
	 * @return Player[]|null
	 */
	public function getPlayers() : ?array{
		return $this->players;
	}

	/**
	 * @param Player[]|null $players
	 */
	public function setPlayers(?array $players) : void{
		$this->players = $players;
	}
}
