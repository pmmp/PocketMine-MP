<?php

namespace pocketmine\event\block;

use pocketmine\block\Anvil;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class AnvilUseEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		private Anvil $anvil,
		private bool $takeDamage = false
	){
		parent::__construct($this->anvil);
	}

	public function getAnvil() : Anvil{
		return $this->anvil;
	}

	public function shouldTakeDamage() : bool{
		return $this->takeDamage;
	}

	public function setTakeDamage(bool $takeDamage) : void{
		$this->takeDamage = $takeDamage;
	}
}