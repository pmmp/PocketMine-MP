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

namespace pocketmine\event\item;

use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Called when an item gets damaged.
 */
class ItemDamageEvent extends ItemEvent implements Cancellable{

    /** @var Item */
    protected $item;
    /** @var int */
    protected $damage;

    public function __construct(Item $item, int $damage){
        parent::__construct($item);
        $this->item = $item;
        $this->damage = $damage;
    }

    /**
     * Returns the item damage is applied to.
     *
     * @return Item
     */
    public function getItem() : Item{
        return $this->item;
    }

    /**
     * Returns the amount of damage applied to the item.
     *
     * @return int
     */
    public function getAppliedDamage() : int{
        return $this->damage;
    }

    /**
     * Sets the amount of damaged applied to the item.
     *
     * @param int $damage
     */
    public function setAppliedDamage(int $damage) : void{
        $this->damage = $damage;
    }
}
