<?php

declare(strict_types=1);

namespace pocketmine\event\entity;

use pocketmine\entity\Location;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class PlaceArmorStandEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	private Location $location;

	public function __construct(Player $player, Location $location){
		$this->player = $player;
		$this->location = $location;
	}

	public function getLocation() : Location{
		return $this->location->asLocation();
	}
}