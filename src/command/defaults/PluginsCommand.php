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

use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use function array_map;
use function count;
use function implode;
use function sort;
use const SORT_STRING;

class PluginsCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"plugins",
			KnownTranslationFactory::pocketmine_command_plugins_description(),
			null,
			["pl"]
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_PLUGINS);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$list = array_map(function(Plugin $plugin) : string{
			return ($plugin->isEnabled() ? TextFormat::GREEN : TextFormat::RED) . $plugin->getDescription()->getFullName();
		}, $sender->getServer()->getPluginManager()->getPlugins());
		sort($list, SORT_STRING);

		$sender->sendMessage(KnownTranslationFactory::pocketmine_command_plugins_success((string) count($list), implode(TextFormat::RESET . ", ", $list)));
		return true;
	}
}
