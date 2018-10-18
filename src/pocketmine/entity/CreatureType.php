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

namespace pocketmine\entity;

class CreatureType{

	/** @var string */
	protected $creatureClass;
	/** @var int */
	protected $maxSpawn;
	/** @var int */
	protected $materialIn;
	/** @var bool */
	protected $peacefulCreature = false;

	public function __construct(string $creatureClass, int $maxSpawn, int $materialIn, bool $peacefulCreature){
		$this->creatureClass = $creatureClass;
		$this->maxSpawn = $maxSpawn;
		$this->materialIn = $materialIn;
		$this->peacefulCreature = $peacefulCreature;
	}

	/**
	 * @return string
	 */
	public function getCreatureClass() : string{
		return $this->creatureClass;
	}

	/**
	 * @return int
	 */
	public function getMaxSpawn() : int{
		return $this->maxSpawn;
	}

	/**
	 * @return int
	 */
	public function getMaterialIn() : int{
		return $this->materialIn;
	}

	/**
	 * @return bool
	 */
	public function isPeacefulCreature() : bool{
		return $this->peacefulCreature;
	}
}
