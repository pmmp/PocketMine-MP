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

namespace pocketmine\inventory;

use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;

class EnchantInventory extends ContainerInventory{

	/** @var Position */
	protected $holder;

	public function __construct(Position $pos){
		parent::__construct($pos->asPosition());
	}

	public function getNetworkType() : int{
		return WindowTypes::ENCHANTMENT;
	}

	public function getName() : string{
		return "Enchantment Table";
	}

	public function getDefaultSize() : int{
		return 2; //1 input, 1 lapis
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Position
	 */
	public function getHolder(){
		return $this->holder;
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);

		$this->dropContents($this->holder->getLevel(), $this->holder->add(0.5, 0.5, 0.5));
	}
}
