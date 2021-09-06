<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\permission\BanEntry;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use function array_shift;
use function count;
use function implode;

class TempBanCommand extends Command{
	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationKeys::POCKETMINE_COMMAND_TEMPBAN_PLAYER_DESCRIPTION,
			KnownTranslationKeys::POCKETMINE_COMMAND_TEMPBAN_PLAYER_USAGE
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_TEMPBAN_PLAYER);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$name = array_shift($args);

		try{
			$expiry = BanEntry::stringToDateTime(array_shift($args));
		}catch(\RuntimeException){
			throw new InvalidCommandSyntaxException();
		}

		$reason = implode(" ", $args);

		$sender->getServer()->getNameBans()->addBan($name, $reason, $expiry, $sender->getName());

		if(($player = $sender->getServer()->getPlayerExact($name)) instanceof Player){
			$player->kick($reason !== "" ? "Temporarily banned by admin. Reason: " . $reason : "Banned by admin.");
		}

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_commands_tempban_success($player !== null ? $player->getName() : $name));

		return true;
	}
}
