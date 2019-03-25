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

namespace pocketmine\event\block;

use pocketmine\block\Chest;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

/**
 * Called when a chest pairs with another chest.
 */
class ChestPairEvent extends BlockEvent implements Cancellable{
    use CancellableTrait;
    
    /** @var Chest */
    private $chest;
    
    /** @var Chest */
    private $pair;
    
    /**
     * @param Chest $chest
     * @param Chest $pair
     */
    public function __construct(Chest $chest, Chest $pair){
        parent::__construct($chest);
        $this->chest = $chest;
        $this->pair = $pair;
    }
    
    /**
     * Returns the chest that's about to pair with the chest that's already placed.
     *
     * @return Chest
     */
    public function getChest() : Chest{
        return $this->chest;
    }
    
    /**
     * Returns the chest that's already placed.
     *
     * @return Chest
     */
    public function getPair() : Chest{
        return $this->pair;
    }
}
