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

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\timings\TimingsHandler;
use pocketmine\YmlServerProperties;

use function count;
use function strtolower;

class TimingsCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"timings",
			KnownTranslationFactory::pocketmine_command_timings_description(),
			KnownTranslationFactory::pocketmine_command_timings_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_TIMINGS);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) !== 1){
			throw new InvalidCommandSyntaxException();
		}

		$mode = strtolower($args[0]);

		if($mode === "on"){
			if(TimingsHandler::isEnabled()){
				$sender->sendMessage(KnownTranslationFactory::pocketmine_command_timings_alreadyEnabled());
				return true;
			}
			TimingsHandler::setEnabled();
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_enable());

			return true;
		}elseif($mode === "off"){
			TimingsHandler::setEnabled(false);
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_disable());
			return true;
		}

		if(!TimingsHandler::isEnabled()){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_timings_timingsDisabled());

			return true;
		}

		$paste = $mode === "paste";

		if($mode === "reset"){
			TimingsHandler::reload();
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_reset());
		}elseif($mode === "merged" || $mode === "report" || $paste){
			$host = $sender->getServer()->getConfigGroup()->getPropertyString(YmlServerProperties::TIMINGS_HOST, "timings.pmmp.io");
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_collect());

			if($paste){
				TimingsHandler::uploadReport($sender, $host);
			}else{
				TimingsHandler::createReportFile($sender)->onCompletion(
					function(string $timingsFile) use ($sender) : void{
						Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_timingsWrite($timingsFile));
					},
					fn() => throw new \AssertionError("This promise is not expected to be rejected")
				);
			}
		}else{
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
