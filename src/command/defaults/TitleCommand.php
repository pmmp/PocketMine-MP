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
use pocketmine\command\overload\RawParameter;
use pocketmine\command\overload\StringParameter;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\Limits;

final class TitleCommand{

	private const string SELF_PERM = DefaultPermissionNames::COMMAND_TITLE_SELF;
	private const string OTHER_PERM = DefaultPermissionNames::COMMAND_TITLE_OTHER;

	private const OVERLOAD_PERMS = [self::SELF_PERM, self::OTHER_PERM];

	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		$playerParameter = new StringParameter("playerName", "player name");
		$textParameter = new RawParameter("text", "text");
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make(commonParameters: [$playerParameter])
				->executor(["clear"], self::OVERLOAD_PERMS, self::clearTitles(...))
				->executor(["reset"], self::OVERLOAD_PERMS, self::resetTitles(...))
				->executor(["title", $textParameter], self::OVERLOAD_PERMS, self::sendTitle(...))
				->executor(["subtitle", $textParameter], self::OVERLOAD_PERMS, self::sendSubTitle(...))
				->executor(["actionbar", $textParameter], self::OVERLOAD_PERMS, self::sendActionBar(...))
				->executor([
					"times",
					new IntRangeParameter("fadeInTicks", "fade-in ticks", 0, Limits::INT32_MAX),
					new IntRangeParameter("stayTicks", "stay ticks", 0, Limits::INT32_MAX),
					new IntRangeParameter("fadeOutTicks", "fade-out ticks", 0, Limits::INT32_MAX)
				], self::OVERLOAD_PERMS, self::setTitleDuration(...))
				->build(),
			KnownTranslationFactory::pocketmine_command_title_description(),
		);
	}

	/**
	 * @phpstan-param \Closure(Player) : void $action
	 */
	private static function doTitleAction(CommandSender $sender, string $playerName, \Closure $action) : void{
		$player = Command::fetchPermittedPlayerTarget($sender, $playerName, self::SELF_PERM, self::OTHER_PERM);
		if($player === null){
			return;
		}
		$action($player);

		$sender->sendMessage(KnownTranslationFactory::commands_title_success());
	}

	private static function clearTitles(CommandSender $sender, string $playerName) : void{
		self::doTitleAction($sender, $playerName, static fn(Player $p) => $p->removeTitles());
	}

	private static function resetTitles(CommandSender $sender, string $playerName) : void{
		self::doTitleAction($sender, $playerName, static fn(Player $p) => $p->resetTitles());
	}

	private static function sendTitle(CommandSender $sender, string $playerName, string $text) : void{
		self::doTitleAction($sender, $playerName, static fn(Player $p) => $p->sendTitle($text));
	}

	private static function sendSubTitle(CommandSender $sender, string $playerName, string $text) : void{
		self::doTitleAction($sender, $playerName, static fn(Player $p) => $p->sendSubTitle($text));
	}

	private static function sendActionBar(CommandSender $sender, string $playerName, string $text) : void{
		self::doTitleAction($sender, $playerName, static fn(Player $p) => $p->sendActionBarMessage($text));
	}

	private static function setTitleDuration(CommandSender $sender, string $playerName, int $fadeInTicks, int $stayTicks, int $fadeOutTicks) : void{
		self::doTitleAction($sender, $playerName, static fn(Player $p) => $p->setTitleDuration($fadeInTicks, $stayTicks, $fadeOutTicks));
	}
}
