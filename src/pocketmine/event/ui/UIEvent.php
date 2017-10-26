<?php

namespace pocketmine\event\ui;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

abstract class UIEvent extends PluginEvent{

	public static $handlerList = null;

	/** @var DataPacket|ModalFormResponsePacket $packet */
	protected $packet;
	/** @var Player */
	protected $player;

	public function __construct(Plugin $plugin, DataPacket $packet, Player $player){
		$this->packet = $packet;
		$this->player = $player;
		parent::__construct($plugin);
	}

	public function getPacket(): DataPacket{
		return $this->packet;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function getID(): int{
		return $this->packet->formId;
	}

}