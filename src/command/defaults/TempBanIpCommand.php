<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use DateTime;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\permission\BanEntry;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;

class TempBanIpCommand extends Command{
	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationKeys::POCKETMINE_COMMAND_TEMP_BAN_IP_DESCRIPTION,
			KnownTranslationKeys::COMMANDS_TEMP_BANIP_USAGE
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_TEMP_BAN_IP);
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
		}catch(\RuntimeException | AssumptionFailedError){
			throw new InvalidCommandSyntaxException();
		}

		$reason = implode(" ", $args);

		if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $value)){
			$this->processIPBan($value, $sender, $reason, $expiry);

			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_temp_banip_success($value));
		}else{
			if(($player = $sender->getServer()->getPlayerByPrefix($value)) instanceof Player){
				$ip = $player->getNetworkSession()->getIp();
				$this->processIPBan($ip, $sender, $reason, $expiry);

				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_temp_banip_success_players($ip, $player->getName()));
			}else{
				$sender->sendMessage(KnownTranslationFactory::commands_temp_banip_invalid());

				return false;
			}
		}

		return true;
	}

	private function processIPBan(string $ip, CommandSender $sender, string $reason, DateTime $expiry) : void{
		$sender->getServer()->getIPBans()->addBan($ip, $reason, $expiry, $sender->getName());

		foreach($sender->getServer()->getOnlinePlayers() as $player){
			if($player->getNetworkSession()->getIp() === $ip){
				$player->kick("Temporarily banned by admin. Reason: " . ($reason !== "" ? $reason : "IP banned."));
			}
		}

		$sender->getServer()->getNetwork()->blockAddress($ip, -1);
	}
}
