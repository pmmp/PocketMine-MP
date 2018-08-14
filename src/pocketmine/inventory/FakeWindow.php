<?php

declare(strict_types=1);

namespace pocketmine\inventory;


use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\Player;

abstract class FakeWindow extends BaseInventory{

	public function onClose(Player $who) : void{
		$pk = new ContainerClosePacket();
		$pk->windowId = $who->getWindowId($this);
		$who->sendDataPacket($pk);
		parent::onClose($who);
	}
}
