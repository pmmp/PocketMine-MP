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
use pocketmine\command\CommandoCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\args\TargetArgument;
use pocketmine\command\args\RawStringArgument;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class KickCommand extends CommandoCommand{

	public function __construct(){
		parent::__construct(
			"kick",
			"Kicks a player from the server",
			"/kick <player> [reason ...]"
		);
	}

	protected function prepare(): void {
		$this->setPermission(DefaultPermissionNames::COMMAND_KICK);
		$this->registerArgument(0, new TargetArgument("player", false));
		$this->registerArgument(1, new RawStringArgument("reason", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$name = $args["player"];
		$reason = $args["reason"] ?? "";

		if(($player = $sender->getServer()->getPlayerByPrefix($name)) instanceof Player){
			$player->kick($reason !== "" ? KnownTranslationFactory::pocketmine_disconnect_kick($reason) : KnownTranslationFactory::pocketmine_disconnect_kick_noReason());
			if($reason !== ""){
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_kick_success_reason($player->getName(), $reason));
			}else{
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_kick_success($player->getName()));
			}
		}else{
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
		}
	}
}
