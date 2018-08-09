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

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\tile\Hopper;

class HopperInventory extends ContainerInventory{
    /** @var Hopper */
    protected $holder;

    public function __construct(Hopper $holder){
        parent::__construct($holder);
    }

    public function getName(): string{
        return "Hopper";
    }

    public function getDefaultSize(): int{
        return 5;
    }

    public function getNetworkType(): int{
        return WindowTypes::HOPPER;
    }

    /**
     * @return Hopper
     */
    public function getHolder(){
        return $this->holder;
    }

    public function firstItem() : ?Item{
        foreach($this->slots as $slot){
            if($slot !== null and !$slot->isNull()){
                return $slot;
            }
        }

        return null;
    }
}