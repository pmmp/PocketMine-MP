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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\entity\Attribute;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;
use function abs;
use function count;
use function str_ends_with;
use function substr;

class XpCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"xp",
			KnownTranslationFactory::pocketmine_command_xp_description(),
			KnownTranslationFactory::pocketmine_command_xp_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_XP_SELF,
			DefaultPermissionNames::COMMAND_XP_OTHER
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) < 1){
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->fetchPermittedPlayerTarget($sender, $args[1] ?? null, DefaultPermissionNames::COMMAND_XP_SELF, DefaultPermissionNames::COMMAND_XP_OTHER);
		if($player === null){
			return true;
		}

		$xpManager = $player->getXpManager();
		if(str_ends_with($args[0], "L")){
			$xpLevelAttr = $player->getAttributeMap()->get(Attribute::EXPERIENCE_LEVEL) ?? throw new AssumptionFailedError();
			$maxXpLevel = (int) $xpLevelAttr->getMaxValue();
			$currentXpLevel = $xpManager->getXpLevel();
			$xpLevels = $this->getInteger($sender, substr($args[0], 0, -1), -$currentXpLevel, $maxXpLevel - $currentXpLevel);
			if($xpLevels >= 0){
				$xpManager->addXpLevels($xpLevels, false);
				$sender->sendMessage(KnownTranslationFactory::commands_xp_success_levels((string) $xpLevels, $player->getName()));
			}else{
				$xpLevels = abs($xpLevels);
				$xpManager->subtractXpLevels($xpLevels);
				$sender->sendMessage(KnownTranslationFactory::commands_xp_success_negative_levels((string) $xpLevels, $player->getName()));
			}
		}else{
			$xp = $this->getInteger($sender, $args[0], max: Limits::INT32_MAX);
			if($xp < 0){
				$sender->sendMessage(KnownTranslationFactory::commands_xp_failure_widthdrawXp()->prefix(TextFormat::RED));
			}else{
				$xpManager->addXp($xp, false);
				$sender->sendMessage(KnownTranslationFactory::commands_xp_success((string) $xp, $player->getName()));
			}
		}

		return true;
	}
}
