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

namespace pocketmine\level\particle;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class DestroyBlockParticle extends Particle {

	/** @var int */
    protected $data;

    public function __construct(Vector3 $pos, Block $b){
        parent::__construct($pos->x, $pos->y, $pos->z);
        $this->data = BlockFactory::toStaticRuntimeId($b->getId(), $b->getDamage());
    }

    public function encode() {
		$pk = new LevelEventPacket;
        $pk->evid = LevelEventPacket::EVENT_PARTICLE_DESTROY;
        $pk->position = $this->asVector3();
        $pk->data = $this->data;
        return $pk;
    }
}