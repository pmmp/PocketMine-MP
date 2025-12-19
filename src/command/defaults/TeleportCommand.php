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
use pocketmine\command\overload\FloatRangeParameter;
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\RelativeXYZ;
use pocketmine\command\overload\RelativeXYZParameter;
use pocketmine\command\overload\StringParameter;
use pocketmine\entity\Location;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function round;

final class TeleportCommand{
	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make()
				->branch(
					[new StringParameter("teleportedPlayerName", "player to teleport")],
					fn(OverloadBuilder $childBuilder) => self::buildOverloads(
						$childBuilder,
						DefaultPermissionNames::COMMAND_TELEPORT_OTHER,
						self::tpOtherToPlayer(...),
						self::tpOtherCoords(...)
					)
				)
				->branch(
					[],
					fn(OverloadBuilder $childBuilder) => self::buildOverloads(
						$childBuilder,
						DefaultPermissionNames::COMMAND_TELEPORT_SELF,
						self::tpSelfToPlayer(...),
						self::tpSelfCoords(...)
					)
				)
				->build(),
			KnownTranslationFactory::pocketmine_command_tp_description()
		);
	}

	/**
	 * @phpstan-param anyClosure $tpToPlayer
	 * @phpstan-param anyClosure $tpToCoords
	 */
	private static function buildOverloads(OverloadBuilder $childBuilder, string $permission, \Closure $tpToPlayer, \Closure $tpToCoords) : void{
		$childBuilder
			->executor([
				new StringParameter("destinationPlayerName", "destination player")
			], $permission, $tpToPlayer)
			->executor([
				new RelativeXYZParameter("coordinates", "coordinates"),
				new FloatRangeParameter("yaw", "yaw", 0, 360),
				new FloatRangeParameter("pitch", "pitch", -90, 90)
			], $permission, $tpToCoords);
	}

	private static function tpCoords(
		CommandSender $sender,
		Player $subject,
		RelativeXYZ $coordinates,
		float $yaw,
		float $pitch
	) : void{
		$base = $subject->getLocation();

		$pos = $coordinates->resolve($base);
		$targetLocation = new Location($pos->x, $pos->y, $pos->z, $base->getWorld(), $yaw, $pitch);

		$subject->teleport($targetLocation);
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_tp_success_coordinates(
			$subject->getName(),
			(string) round($targetLocation->x, 2),
			(string) round($targetLocation->y, 2),
			(string) round($targetLocation->z, 2)
		));
	}

	private static function tpSelfCoords(
		CommandSender $sender,
		RelativeXYZ $coordinates,
		float $yaw = 0.0,
		float $pitch = 0.0
	) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_playerUserOnly()->prefix(TextFormat::RED));
		}

		self::tpCoords($sender, $sender, $coordinates, $yaw, $pitch);
	}

	private static function tpOtherCoords(
		CommandSender $sender,
		string $teleportedPlayerName,
		RelativeXYZ $coordinates,
		float $yaw = 0.0,
		float $pitch = 0.0
	) : void{
		$subject = Command::fetchPermittedPlayerTarget($sender, $teleportedPlayerName, DefaultPermissionNames::COMMAND_TELEPORT_SELF, DefaultPermissionNames::COMMAND_TELEPORT_OTHER);
		if($subject === null){
			return;
		}

		self::tpCoords($sender, $subject, $coordinates, $yaw, $pitch);
	}

	private static function tpToPlayer(CommandSender $sender, Player $teleportedPlayer, string $destinationPlayerName) : void{
		$destination = $sender->getServer()->getPlayerByPrefix($destinationPlayerName);
		if($destination === null){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_playerNotFound($destinationPlayerName)->prefix(TextFormat::RED));
			return;
		}

		$teleportedPlayer->teleport($destination->getLocation());
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_tp_success($teleportedPlayer->getName(), $destination->getName()));
	}

	private static function tpSelfToPlayer(CommandSender $sender, string $destinationPlayer) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_playerUserOnly()->prefix(TextFormat::RED));
			return;
		}

		self::tpToPlayer($sender, $sender, $destinationPlayer);
	}

	private static function tpOtherToPlayer(CommandSender $sender, string $teleportedPlayerName, string $destinationPlayerName) : void{
		$subject = Command::fetchPermittedPlayerTarget($sender, $teleportedPlayerName, DefaultPermissionNames::COMMAND_TELEPORT_SELF, DefaultPermissionNames::COMMAND_TELEPORT_OTHER);
		if($subject === null){
			return;
		}

		self::tpToPlayer($sender, $subject, $destinationPlayerName);
	}
}
