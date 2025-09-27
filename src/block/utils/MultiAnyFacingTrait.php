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

namespace pocketmine\block\utils;

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\Facing;
use function spl_object_id;

/**
 * Used by blocks that can have multiple target faces in the area of one solid block, such as covering three sides of a corner.
 */
trait MultiAnyFacingTrait{

	/** @var Facing[] */
	protected array $faces = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->enumSet($this->faces, Facing::cases());
	}

	/** @return Facing[] */
	public function getFaces() : array{ return $this->faces; }

	public function hasFace(Facing $face) : bool{
		return isset($this->faces[spl_object_id($face)]);
	}

	/**
	 * @param Facing[] $faces
	 * @return $this
	 */
	public function setFaces(array $faces) : self{
		$uniqueFaces = [];
		foreach($faces as $face){
			$uniqueFaces[spl_object_id($face)] = $face;
		}
		$this->faces = $uniqueFaces;
		return $this;
	}

	/** @return $this */
	public function setFace(Facing $face, bool $value) : self{
		if($value){
			$this->faces[spl_object_id($face)] = $face;
		}else{
			unset($this->faces[spl_object_id($face)]);
		}
		return $this;
	}
}
