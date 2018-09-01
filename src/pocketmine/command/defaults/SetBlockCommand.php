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

use pocketmine\command\CommandEnumValues;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\ItemFactory;
use pocketmine\lang\TranslationContainer;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SetBlockCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "Changes a block to another block.",
            "/setblock <position: x y z> <tileName: string> [tileData: int] [oldBlockHandling: string]",
            [],
            [[
                new CommandParameter("position", CommandParameter::ARG_TYPE_POSITION, false),
                new CommandParameter("tileName", CommandParameter::ARG_TYPE_STRING, false, CommandEnumValues::getBlock()),
                new CommandParameter("tileData", CommandParameter::ARG_TYPE_INT),
                new CommandParameter("mode", CommandParameter::ARG_TYPE_STRING, false, CommandEnumValues::getSetBlockMode())
            ]]
        );
        $this->setPermission("altay.command.setblock");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender) or !($sender instanceof Player)){
            return true;
        }

        if(count($args) < 4){
            throw new InvalidCommandSyntaxException();
        }

        $level = $sender->level;
        $pos = [(int) $args[0], (int) $args[1], (int) $args[2]];
        if(!$level->isInWorld(...$pos)){
            $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.setblock.outOfWorld"));
            return true;
        }

        $pos = new Vector3(...$pos);

        $block = ItemFactory::fromString($args[3]);
        $block->setDamage($args[4] ?? 0);

        $handling = $args[5] ?? "replace";
        $block = $block->getBlock();

        $place = true;
        switch($handling){
            case "destroy":
                $level->useBreakOn($pos);
                break;
            case "keep":
                $place = $level->getBlockAt($pos->x, $pos->y, $pos->z)->getId() === 0;
                break;
            case "replace":
                break;
            default:
                throw new InvalidCommandSyntaxException();
        }

        if($place){
            $level->setBlock($pos, $block);
            $sender->sendMessage(new TranslationContainer(TextFormat::GREEN . "%commands.setblock.success"));
        }

        return true;
    }
}
