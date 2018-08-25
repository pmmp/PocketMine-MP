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

namespace pocketmine\command;

use pocketmine\lang\TranslationContainer;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class FormattedCommandAlias extends Command{
	private $formatStrings = [];

	/**
	 * @param string   $alias
	 * @param string[] $formatStrings
	 */
	public function __construct(string $alias, array $formatStrings){
		parent::__construct($alias);
		$this->formatStrings = $formatStrings;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){

		$commands = [];
		$result = true;

		foreach($this->formatStrings as $formatString){
			try{
				$commands[] = $this->buildCommand($formatString, $args);
			}catch(\InvalidArgumentException $e){
				$sender->sendMessage(TextFormat::RED . $e->getMessage());
				return false;
			}catch(\Throwable $e){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.exception"));
				$sender->getServer()->getLogger()->logException($e);

				return false;
			}
		}

		foreach($commands as $command){
			$result &= Server::getInstance()->dispatchCommand($sender, $command, true);
		}

		return (bool) $result;
	}

	/**
	 * @param string $formatString
	 * @param array  $args
	 *
	 * @return string
	 */
	private function buildCommand(string $formatString, array $args) : string{
        $placeholders = [];
        preg_match_all('/\s(\$\d+)(?!\S)/', $formatString, $placeholders);

        // Remove duplicates since we replace every instance anyways
        $placeholders = array_unique($placeholders[1]);

        foreach($placeholders as $placeholder){
            $index = substr($placeholder, 1);
            var_dump($index);
            if(!isset($args[$index])){
                throw new \InvalidArgumentException('Not enough arguments provided!');
            }

            // Only match those that are words by themselves and add space to replacement
            // because this will match the space before the $
            $formatString = preg_replace('/\s\$'.$index.'(?!\S)/', " $args[$index]", $formatString);
        }

        return $formatString;
    }
}
