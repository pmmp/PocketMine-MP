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
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_shift;
use function count;
use function implode;

class TellCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationKeys::POCKETMINE_COMMAND_TELL_DESCRIPTION,
			KnownTranslationKeys::COMMANDS_MESSAGE_USAGE,
			["w", "msg"]
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_TELL);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender->getServer()->getPlayerByPrefix(array_shift($args));

		if($player === $sender){
			$sender->sendMessage(KnownTranslationFactory::commands_message_sameTarget()->prefix(TextFormat::RED));
			return true;
		}

		if($player instanceof Player){
			$message = implode(" ", $args);
			$sender->sendMessage(KnownTranslationFactory::commands_message_display_outgoing($player->getDisplayName(), $message)->prefix(TextFormat::GRAY . TextFormat::ITALIC));
			$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
			$player->sendMessage(KnownTranslationFactory::commands_message_display_incoming($name, $message)->prefix(TextFormat::GRAY . TextFormat::ITALIC));
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_message_display_outgoing($player->getDisplayName(), $message), false);
		}else{
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
		}

		return true;
	}
}
