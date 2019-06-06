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

namespace pocketmine\entity\helper;

use pocketmine\entity\Mob;

class EntityJumpHelper{

	protected $isJumping = false;
	/** @var Mob */
	protected $entity;

	public function __construct(Mob $mob){
		$this->entity = $mob;
	}

	/**
	 * @return bool
	 */
	public function isJumping() : bool{
		return $this->isJumping;
	}

	/**
	 * @param bool $isJumping
	 */
	public function setJumping(bool $isJumping) : void{
		$this->isJumping = $isJumping;
	}

	public function doJump() : void{
		$this->entity->setJumping($this->isJumping);
		$this->isJumping = false;
	}
}