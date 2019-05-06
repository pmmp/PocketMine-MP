<?php

/*
    _____ _                 _        __  __ _____
  / ____| |               | |      |  \/  |  __ \
 | |    | | ___  _   _  __| |______| \  / | |__) |
 | |    | |/ _ \| | | |/ _` |______| |\/| |  ___/
 | |____| | (_) | |_| | (_| |      | |  | | |
  \_____|_|\___/ \__,_|\__,_|      |_|  |_|_|

     Make of Things.
 */

 declare(strict_types=1);

 namespace pocketmine\inventory;

 use pocketmine\entity\Villager;
 use pocketmine\nbt\NetworkLittleEndianNBTStream;
 use pocketmine\nbt\tag\CompoundTag;
 use pocketmine\network\mcpe\protocol\UpdateTradePacket;
 use pocketmine\Player;

 class TradeInventory extends FakeWindow{
 	/** @var Villager */
 	protected $holder;

 	public function __construct(Villager $holder){
 		$this->holder = $holder;
 		parent::__construct();
 	}

 	public function getName() : string{
 		return "Trade";
 	}

 	public function getDefaultSize() : int{
 		return 3;
 	}

 	public function onOpen(Player $who) : void{
 		parent::onOpen($who);

 		$this->holder->setTradingPlayer($playerEid = $who->getId());

 		$pk = new UpdateTradePacket();
 		$pk->windowId = $who->getWindowId($this);
 		$pk->varint1 = $pk->varint2 = 0;
 		$pk->isWilling = true;
 		$pk->traderEid = $this->holder->getId();
 		$pk->playerEid = $playerEid;
 		$pk->displayName = $this->holder->getTraderName();
 		$pk->offers = (new NetworkLittleEndianNBTStream())->write(new CompoundTag("", [
 			$this->holder->getRecipes()
 		]));
 		$who->sendDataPacket($pk);
 	}

 	public function onClose(Player $who) : void{
 		$this->holder->setTradingPlayer();
 		parent::onClose($who);
 	}
 }
