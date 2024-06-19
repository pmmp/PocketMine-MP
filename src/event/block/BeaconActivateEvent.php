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
use pocketmine\entity\effect\Effect;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

/**
 * Called when a player activates a beacon with the interface.
 */
class BeaconActivateEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	protected Effect $primaryEffect;
	protected ?Effect $secondaryEffect;

	public function __construct(Block $block, Effect $primaryEffect, ?Effect $secondaryEffect = null){
		parent::__construct($block);
		$this->primaryEffect = $primaryEffect;
		$this->secondaryEffect = $secondaryEffect;
	}

	public function getPrimaryEffect() : Effect{
		return $this->primaryEffect;
	}

	public function getSecondaryEffect() : ?Effect{
		return $this->secondaryEffect;
	}

	public function setPrimaryEffect(Effect $primary) : void{
		$this->primaryEffect = $primary;
	}

	public function setSecondaryEffect(?Effect $secondary) : void{
		$this->secondaryEffect = $secondary;
	}
}
