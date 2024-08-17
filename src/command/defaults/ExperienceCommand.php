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
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;
use function abs;
use function max;
use function str_ends_with;
use function substr;

class ExperienceCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"xp",
			"Adds or removes player experience",
			"/xp <experience> [player]"
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_EXPERIENCE_SELF,
			DefaultPermissionNames::COMMAND_EXPERIENCE_OTHER
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) < 1){
			throw new InvalidCommandSyntaxException();
		}
		$player = $this->fetchPermittedPlayerTarget($sender, $args[1] ?? null, DefaultPermissionNames::COMMAND_EXPERIENCE_SELF, DefaultPermissionNames::COMMAND_EXPERIENCE_OTHER);
		if($player === null){
			return true;
		}
		$xpManager = $player->getXpManager();
		if(str_ends_with($args[0], "L")){
			$xpAttr = $player->getAttributeMap()->get(Attribute::EXPERIENCE_LEVEL) ?? throw new AssumptionFailedError();
			$maxXp = (int) $xpAttr->getMaxValue();
			$xp = $this->getInteger($sender, substr($args[0], 0, -1), -$maxXp, $maxXp);
			if($xp >= 0){
				$xpManager->setXpLevel(min($xpManager->getXpLevel() + $xp, $maxXp));
				$sender->sendMessage("Gave $xp experience levels to " . $player->getName());
			}else{
				$xp = abs($xp);
				$xpManager->setXpLevel(max($xpManager->getXpLevel() - $xp, 0));
				$sender->sendMessage("Taken $xp levels from " . $sender->getName());
			}
			return true;
		}
		$xp = $this->getInteger($sender, $args[0], Limits::INT32_MIN, Limits::INT32_MAX);
		if($xp < 0){
			$sender->sendMessage(TextFormat::RED . "Cannot give player negative experience points");
		}else{
			$xpManager->addXp($xp, false);
			$sender->sendMessage("Gave $xp experience to " . $player->getName());
		}
		return true;
	}
}
