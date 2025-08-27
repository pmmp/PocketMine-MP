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
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandEnumConstraint;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\ServerProperties;
use pocketmine\world\World;
use ReflectionClass;
use function count;

class DifficultyCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"difficulty",
			KnownTranslationFactory::pocketmine_command_difficulty_description(),
			KnownTranslationFactory::commands_difficulty_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_DIFFICULTY);
	}

	/**
	 * @param CommandEnum[]           $hardcodedEnums
	 * @param CommandEnum[]           $softEnums
	 * @param CommandEnumConstraint[] $enumConstraints
	 * @return CommandOverload[]
	 */
	public function buildOverloads(array &$hardcodedEnums, array &$softEnums, array &$enumConstraints) : array{
		$worldConstants = array_keys((new ReflectionClass(World::class))->getConstants());
		$difficultyOptions = array_filter($worldConstants, fn(string $constant) => str_starts_with($constant, 'DIFFICULTY_'));
		$difficultyOptions = array_map(fn(string $difficultyString) => substr($difficultyString, strlen('DIFFICULTY_')), $difficultyOptions);
		$difficultyOptions = array_merge($difficultyOptions, array_map(fn(string $difficultyString) => $difficultyString[0], $difficultyOptions));
		$difficultyOptions = array_map(fn(string $difficultyString) => mb_strtolower($difficultyString), $difficultyOptions);
		$difficultyEnum = new CommandEnum('Difficulty', $difficultyOptions, false);

		return [
			new CommandOverload(chaining: false, parameters: [
				CommandParameter::enum("Difficulty", $difficultyEnum, 0, false),
			]),
			new CommandOverload(chaining: false, parameters: [
				CommandParameter::standard("Difficulty", AvailableCommandsPacket::ARG_TYPE_INT, 0, false),
			])
		];
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) !== 1){
			throw new InvalidCommandSyntaxException();
		}

		$difficulty = World::getDifficultyFromString($args[0]);

		if($sender->getServer()->isHardcore()){
			$difficulty = World::DIFFICULTY_HARD;
		}

		if($difficulty !== -1){
			$sender->getServer()->getConfigGroup()->setConfigInt(ServerProperties::DIFFICULTY, $difficulty);

			//TODO: add per-world support
			foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
				$world->setDifficulty($difficulty);
			}

			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_difficulty_success((string) $difficulty));
		}else{
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
