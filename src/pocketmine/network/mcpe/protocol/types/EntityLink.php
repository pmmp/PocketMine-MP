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

class EntityLink{

    public const TYPE_REMOVE = 0;
    public const TYPE_RIDER = 1;
    public const TYPE_PASSENGER = 0;

    /** @var int */
    public $riddenId;
    /** @var int */
    public $riderId;
    /** @var int */
    public $type;
    /** @var bool */
    public $immediate; //for dismounting on mount death

    public function __construct(?int $riddenId = null, ?int $riderId = null, ?int $type = null, bool $immediate = false){
        $this->riddenId = $riddenId;
        $this->riderId = $riderId;
        $this->type = $type;
        $this->immediate = $immediate;
    }
}