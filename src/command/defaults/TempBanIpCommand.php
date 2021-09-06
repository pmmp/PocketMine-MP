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
use function preg_match;

class TempBanIpCommand extends Command{
	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationKeys::POCKETMINE_COMMAND_TEMPBAN_IP_DESCRIPTION,
			KnownTranslationKeys::POCKETMINE_COMMAND_TEMPBAN_IP_USAGE
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_TEMPBAN_IP);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$value = array_shift($args);

		try{
			$expiry = BanEntry::stringToDateTime(array_shift($args));
		}catch(\RuntimeException){
			throw new InvalidCommandSyntaxException();
		}

		$reason = implode(" ", $args);

		if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $value)){
			$this->processIPBan($value, $sender, $reason, $expiry);

			Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_commands_tempban_success($value));
		}else{
			if(($player = $sender->getServer()->getPlayerByPrefix($value)) instanceof Player){
				$ip = $player->getNetworkSession()->getIp();
				$this->processIPBan($ip, $sender, $reason, $expiry);

				Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_commands_tempbanip_success_players($ip, $player->getName()));
			}else{
				$sender->sendMessage(KnownTranslationFactory::pocketmine_commands_tempbanip_invalid());

				return false;
			}
		}

		return true;
	}

	private function processIPBan(string $ip, CommandSender $sender, string $reason, \DateTime $expiry) : void{
		$sender->getServer()->getIPBans()->addBan($ip, $reason, $expiry, $sender->getName());

		foreach($sender->getServer()->getOnlinePlayers() as $player){
			if($player->getNetworkSession()->getIp() === $ip){
				$player->kick("Temporarily banned by admin. Reason: " . ($reason !== "" ? $reason : "IP banned."));
			}
		}

		$sender->getServer()->getNetwork()->blockAddress($ip, $expiry->diff(new \DateTime('now'))->s);
	}
}
