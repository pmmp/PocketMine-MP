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

namespace pocketmine\entity\behavior;

final class BehaviorEntry{
	/** @var int */
	protected $priority;
	/** @var Behavior */
	protected $behavior;

	public function __construct(int $priority, Behavior $behavior){
		$this->priority = $priority;
		$this->behavior = $behavior;
	}
	
	public function getPriority() : int{
		return $this->priority;
	}
	
	public function getBehavior() : Behavior{
		return $this->behavior;
	}
}