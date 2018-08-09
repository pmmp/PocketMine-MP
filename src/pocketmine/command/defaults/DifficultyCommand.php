<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandEnumValues;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\types\CommandParameter;

class DifficultyCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.difficulty.description",
			"%commands.difficulty.usage",
            [],
            [
                [
                    new CommandParameter("difficulty", CommandParameter::ARG_TYPE_STRING, false, CommandEnumValues::getDifficulty())
                ],
                [
                    new CommandParameter("difficulty", CommandParameter::ARG_TYPE_INT, false)
                ]
            ]
		);
		$this->setPermission("pocketmine.command.difficulty");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) !== 1){
			throw new InvalidCommandSyntaxException();
		}

		$difficulty = Level::getDifficultyFromString($args[0]);

		if($sender->getServer()->isHardcore()){
			$difficulty = Level::DIFFICULTY_HARD;
		}

		if($difficulty !== -1){
			$sender->getServer()->setConfigInt("difficulty", $difficulty);

			//TODO: add per-world support
			foreach($sender->getServer()->getLevels() as $level){
				$level->setDifficulty($difficulty);
			}

			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.difficulty.success", [$difficulty]));
		}else{
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
