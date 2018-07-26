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

class MapTrackedObject{
	public const TYPE_ENTITY = 0;
	public const TYPE_BLOCK = 1;

	/** @var int */
	public $type;

	/** @var int Only set if is TYPE_ENTITY */
	public $entityUniqueId;

	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;

}