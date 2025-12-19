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
use pocketmine\command\overload\IntRangeParameter;
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\StringParameter;
use pocketmine\entity\Attribute;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;
use function abs;
use function max;
use function min;

final class XpCommand{

	private const SELF_PERM = DefaultPermissionNames::COMMAND_XP_SELF;
	private const OTHER_PERM = DefaultPermissionNames::COMMAND_XP_OTHER;

	private const OVERLOAD_PERMS = [self::SELF_PERM, self::OTHER_PERM];

	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make()
				->executor([
					new IntRangeParameter("xp", "xp", 0, Limits::INT32_MAX),
					new StringParameter("playerName", "player name"),
				], self::OVERLOAD_PERMS, self::addXp(...))
				->executor([
					new IntRangeParameter("xpLevels", "xp levels", Limits::INT32_MIN, Limits::INT32_MAX, "L"),
					new StringParameter("playerName", "player name"),
				], self::OVERLOAD_PERMS, self::addXpLevels(...))
				->build(),
			KnownTranslationFactory::pocketmine_command_xp_description()
		);
	}

	private static function addXp(CommandSender $sender, int $xp, ?string $playerName = null) : void{
		$player = Command::fetchPermittedPlayerTarget($sender, $playerName, self::SELF_PERM, self::OTHER_PERM);
		if($player === null){
			return;
		}

		if($xp < 0){
			$sender->sendMessage(KnownTranslationFactory::commands_xp_failure_widthdrawXp()->prefix(TextFormat::RED));
		}else{
			$player->getXpManager()->addXp($xp, false);
			$sender->sendMessage(KnownTranslationFactory::commands_xp_success((string) $xp, $player->getName()));
		}
	}

	private static function addXpLevels(CommandSender $sender, int $xpLevels, ?string $playerName = null) : void{
		$player = Command::fetchPermittedPlayerTarget($sender, $playerName, self::SELF_PERM, self::OTHER_PERM);
		if($player === null){
			return;
		}

		$xpManager = $player->getXpManager();
		$xpLevelAttr = $player->getAttributeMap()->get(Attribute::EXPERIENCE_LEVEL) ?? throw new AssumptionFailedError();
		$maxXpLevel = (int) $xpLevelAttr->getMaxValue();
		$currentXpLevel = $xpManager->getXpLevel();
		$xpLevels = max(-$currentXpLevel, min($maxXpLevel - $currentXpLevel, $xpLevels));
		if($xpLevels >= 0){
			$xpManager->addXpLevels($xpLevels, false);
			$sender->sendMessage(KnownTranslationFactory::commands_xp_success_levels((string) $xpLevels, $player->getName()));
		}else{
			$xpLevels = abs($xpLevels);
			$xpManager->subtractXpLevels($xpLevels);
			$sender->sendMessage(KnownTranslationFactory::commands_xp_success_negative_levels((string) $xpLevels, $player->getName()));
		}
	}
}
