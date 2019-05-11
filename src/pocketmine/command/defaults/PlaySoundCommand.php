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

namespace pocketmine\command\defaults;

use pocketmine\command\{
	CommandSender, defaults\VanillaCommand
};
use pocketmine\lang\TranslationContainer;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\Server;

class PlaySoundCommand extends VanillaCommand {
	public function __construct($name){
		parent::__construct(
			$name,
			"Plays a sound",
			"/playsound <sound> <player> [x] [y] [z] [volume] [pitch]"
		);
		$this->setPermission("pocketmine.command.playsound");
	}

	/**
	 * @param CommandSender $sender
	 * @param string $currentAlias
	 * @param array $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(!isset($args[0]) || !isset($args[1])){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$server = Server::getInstance();
		$player = $server->getPlayer($args[1]);

		if($player instanceof Player === false){
			$sender->sendMessage("Cannot find Player.");

			return false;
		}

		$sound = $args[0] ?? "";
		$x = $args[2] ?? $player->getX();
		$y = $args[3] ?? $player->getY();
		$z = $args[4] ?? $player->getZ();
		$volume = $args[5] ?? 500;
		$pitch = $args[6] ?? 1;

		$pk = new PlaySoundPacket();
		$pk->soundName = $sound;
		$pk->x = $x;
		$pk->y = $y;
		$pk->z = $z;
		$pk->volume = $volume;
		$pk->pitch = $pitch;

		$server->broadcastPacket($player->getLevel()->getPlayers(), $pk);
		$sender->sendMessage("Playing " . $sound . " to " . $player->getName());

		return true;
	}
}
