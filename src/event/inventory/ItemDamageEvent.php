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

namespace pocketmine\event\inventory;

use pocketmine\crafting\CraftingRecipe;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Utils;

class ItemDamageEvent extends Event implements Cancellable{
    use CancellableTrait;

    public function __construct(
        private Durable $item,
        private int $damage,
        private int $unbreakingDamageReduction = 0
    ){}

    /**
     * @return int
     */
    public function getDamage(): int
    {
        return $this->damage;
    }

    /**
     * @return Durable
     */
    public function getItem(): Durable
    {
        return $this->item;
    }

    /**
     * @return int
     */
    public function getUnbreakingDamageReduction(): int
    {
        return $this->unbreakingDamageReduction;
    }

    /**
     * @param int $damage
     */
    public function setDamage(int $damage): void
    {
        $this->damage = $damage;
    }

    /**
     * @param int $unbreakingDamageReduction
     */
    public function setUnbreakingDamageReduction(int $unbreakingDamageReduction): void
    {
        $this->unbreakingDamageReduction = $unbreakingDamageReduction;
    }
}
