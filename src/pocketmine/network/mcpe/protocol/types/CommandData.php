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

namespace pocketmine\network\mcpe\protocol\types;

class CommandData{

    /** @var string */
    public $commandName;
    /** @var string */
    public $commandDescription;
    /** @var int */
    public $flags;
    /** @var int */
    public $permission;
    /** @var CommandEnum|null */
    public $aliases;
    /** @var CommandParameter[][] */
    public $overloads = [];

    /**
     * CommandData constructor.
     * @param string               $name
     * @param string               $description
     * @param int                  $flags
     * @param int                  $permission
     * @param CommandEnum|null     $aliases
     * @param CommandParameter[][] $overloads
     */
    public function __construct(string $name, string $description, int $flags, int $permission, ?CommandEnum $aliases = null, array $overloads = []){
        $this->commandName = $name;
        $this->commandDescription = $description;
        $this->flags = $flags;
        $this->permission = $permission;
        $this->aliases = $aliases;
        $this->overloads = $overloads;
    }

}