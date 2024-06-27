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
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function is_numeric;
use function substr;

abstract class VanillaCommand extends Command{
	public const MAX_COORD = 30000000;
	public const MIN_COORD = -30000000;

	protected function fetchPermittedPlayerTarget(CommandSender $sender, ?string $target, string $selfPermission, string $otherPermission) : ?Player{
		if($target !== null){
			$player = $sender->getServer()->getPlayerByPrefix($target);
		}elseif($sender instanceof Player){
			$player = $sender;
		}else{
			throw new InvalidCommandSyntaxException();
		}

		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return null;
		}
		if(
			($player === $sender && $this->testPermission($sender, $selfPermission)) ||
			($player !== $sender && $this->testPermission($sender, $otherPermission))
		){
			return $player;
		}
		return null;
	}

	protected function getInteger(CommandSender $sender, string $value, int $min = self::MIN_COORD, int $max = self::MAX_COORD) : int{
		$i = (int) $value;

		if($i < $min){
			$i = $min;
		}elseif($i > $max){
			$i = $max;
		}

		return $i;
	}

	protected function getRelativeDouble(float $original, CommandSender $sender, string $input, float $min = self::MIN_COORD, float $max = self::MAX_COORD) : float{
		if($input[0] === "~"){
			$value = $this->getDouble($sender, substr($input, 1));

			return $original + $value;
		}

		return $this->getDouble($sender, $input, $min, $max);
	}

	protected function getDouble(CommandSender $sender, string $value, float $min = self::MIN_COORD, float $max = self::MAX_COORD) : float{
		$i = (double) $value;

		if($i < $min){
			$i = $min;
		}elseif($i > $max){
			$i = $max;
		}

		return $i;
	}

	protected function getBoundedInt(CommandSender $sender, string $input, int $min, int $max) : ?int{
		if(!is_numeric($input)){
			throw new InvalidCommandSyntaxException();
		}

		$v = (int) $input;
		if($v > $max){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_num_tooBig($input, (string) $max)->prefix(TextFormat::RED));
			return null;
		}
		if($v < $min){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_num_tooSmall($input, (string) $min)->prefix(TextFormat::RED));
			return null;
		}

		return $v;
	}
}
