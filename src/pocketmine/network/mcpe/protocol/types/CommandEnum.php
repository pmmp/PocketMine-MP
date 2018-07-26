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

class CommandEnum{

    /** @var string */
    public $enumName;
    /** @var string[] */
    public $enumValues = [];

    public function __construct(string $enumName, array $enumValues = []){
        $this->enumName = $enumName;
        $this->enumValues = $enumValues;
    }

    public function setName(string $enumName) : CommandEnum{
        $this->enumName = $enumName;

        return $this;
    }

    public function setValues(array $enumValues) : CommandEnum{
        $this->enumValues = $enumValues;

        return $this;
    }
}