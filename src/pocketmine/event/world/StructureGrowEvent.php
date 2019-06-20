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

namespace pocketmine\event\world;

use pocketmine\block\utils\TreeType;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;

/**
 * Event that is called when an organic structure attempts to grow (Sapling -> Tree), (Mushroom -> Huge Mushroom),
 * naturally or using fertilizer (bonemeal).
 */
class StructureGrowEvent extends WorldEvent implements Cancellable{
    use CancellableTrait;

    /**@var Vector3 */
    private $vector3;
    /**@var TreeType */
    private $species;
    /**@var boolean */
    private $fertilizer;
    /**@var Player|null */
    private $player;

    /**
     * @param World $world
     * @param Vector3 $vector3
     * @param TreeType $species
     * @param bool $fertilizer
     * @param Player|null $player
     */
    public function __construct(World $world, Vector3 $vector3, TreeType $species, bool $fertilizer, ?Player $player = null){ //TODO: Gets a list of all blocks associated with the structure.
        parent::__construct($world);
        $this->vector3 = $vector3;
        $this->species = $species;
        $this->fertilizer = $fertilizer;
        $this->player = $player;
    }

    /**
     * Returns the structure coordinates.
     * @return Vector3
     */
    public function getVector3(): Vector3{
        return $this->vector3;
    }

    /**
     * @return TreeType
     */
    public function getSpecies(): TreeType{
        return $this->species;
    }

    /**
     * @return Player|null
     */
    public function getPlayer(): ?Player{
        return $this->player;
    }

    /**
     * @return bool
     */
    public function isFromFertilizer(): bool{
        return $this->fertilizer;
    }
}