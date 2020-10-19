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

namespace pocketmine\command\parameter\defaults;

use pocketmine\command\CommandSender;
use pocketmine\lang\Language;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\player\IPlayer;
use pocketmine\player\Player;
use pocketmine\Server;
use function array_map;
use function mb_strpos;
use function preg_match;

class PlayerParameter extends EnumParameter{
	/** @var bool */
	protected $includeOffline = false;

	public function __construct(string $name, bool $optional = false, bool $exact = false, bool $caseSensitive = false, bool $includeOffline = false){
		parent::__construct($name, $optional, $exact, $caseSensitive);
		$this->includeOffline = $includeOffline;
	}

	public function setIncludeOffline(bool $includeOffline) : self{
		$this->includeOffline = $includeOffline;
		return $this;
	}

	public function isIncludeOffline() : bool{
		return $this->includeOffline;
	}

	public function canParse(CommandSender $sender, string $argument) : bool{
		if(!parent::canParse($sender, $argument)){
			return false;
		}
		return Player::isValidUserName($argument);
	}

	public function parse(CommandSender $sender, string $argument){
		return $this->parseSilent($sender, $argument);
	}

	public function getNetworkType() : int{
		return AvailableCommandsPacket::ARG_TYPE_TARGET;
	}

	public function getTargetName() : string{
		return "target";
	}

	public function getFailMessage(Language $language) : string{
		return $language->translateString("%commands.generic.player.notFound");
	}

	/**
	 * @return IPlayer|null
	 */
	public function parseSilent(CommandSender $sender, string $argument){
		/** @var string|null $value */
		$value = parent::parseSilent($sender, $argument);
		if($value !== null){
			if(!$this->isIncludeOffline()){
				return $sender->getServer()->getPlayer($value);
			}
			if($sender->getServer()->hasOfflinePlayerData($value)){
				return $sender->getServer()->getOfflinePlayer($value);
			}
		}
		return null;
	}

	public function prepare() : void{
		$this->enum = new CommandEnum("player", array_map(function(Player $player) : string{
			if(mb_strpos($player->getName(), " ") !== false){
				return "\"{$player->getName()}\"";
			}
			return $player->getName();
		}, Server::getInstance()->getOnlinePlayers()));
	}
}