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

namespace pocketmine\math;

class AxisAlignedBB{

	/** @var float */
	public $minX;
	/** @var float */
	public $minY;
	/** @var float */
	public $minZ;
	/** @var float */
	public $maxX;
	/** @var float */
	public $maxY;
	/** @var float */
	public $maxZ;

	public function __construct(float $minX, float $minY, float $minZ, float $maxX, float $maxY, float $maxZ){
		$this->minX = $minX;
		$this->minY = $minY;
		$this->minZ = $minZ;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->maxZ = $maxZ;
	}

	public function setBounds(float $minX, float $minY, float $minZ, float $maxX, float $maxY, float $maxZ){
		$this->minX = $minX;
		$this->minY = $minY;
		$this->minZ = $minZ;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->maxZ = $maxZ;

		return $this;
	}

	/**
	 * Returns a new AxisAlignedBB extended by the specified X, Y and Z.
	 * If each of X, Y and Z are positive, the relevant max bound will be increased. If negative, the relevant min
	 * bound will be decreased.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 *
	 * @return AxisAlignedBB
	 */
	public function addCoord(float $x, float $y, float $z) : AxisAlignedBB{
		$minX = $this->minX;
		$minY = $this->minY;
		$minZ = $this->minZ;
		$maxX = $this->maxX;
		$maxY = $this->maxY;
		$maxZ = $this->maxZ;

		if($x < 0){
			$minX += $x;
		}elseif($x > 0){
			$maxX += $x;
		}

		if($y < 0){
			$minY += $y;
		}elseif($y > 0){
			$maxY += $y;
		}

		if($z < 0){
			$minZ += $z;
		}elseif($z > 0){
			$maxZ += $z;
		}

		return new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);
	}

	/**
	 * Returns a new AxisAlignedBB with bounds outset by the specified X, Y and Z.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 *
	 * @return AxisAlignedBB
	 */
	public function grow(float $x, float $y, float $z) : AxisAlignedBB{
		return new AxisAlignedBB($this->minX - $x, $this->minY - $y, $this->minZ - $z, $this->maxX + $x, $this->maxY + $y, $this->maxZ + $z);
	}

	/**
	 * Performs the same operation as grow() but operates on itself instead of returning a new object.
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 *
	 * @return $this
	 */
	public function expand(float $x, float $y, float $z){
		$this->minX -= $x;
		$this->minY -= $y;
		$this->minZ -= $z;
		$this->maxX += $x;
		$this->maxY += $y;
		$this->maxZ += $z;

		return $this;
	}

	/**
	 * Performs the same operation as getOffsetBoundingBox(), but operates on itself instead of returning a new object.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 *
	 * @return $this
	 */
	public function offset(float $x, float $y, float $z){
		$this->minX += $x;
		$this->minY += $y;
		$this->minZ += $z;
		$this->maxX += $x;
		$this->maxY += $y;
		$this->maxZ += $z;

		return $this;
	}

	/**
	 * Returns a new AxisAlignedBB with bounds inset by the specified X, Y and Z.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 *
	 * @return AxisAlignedBB
	 */
	public function shrink(float $x, float $y, float $z) : AxisAlignedBB{
		return new AxisAlignedBB($this->minX + $x, $this->minY + $y, $this->minZ + $z, $this->maxX - $x, $this->maxY - $y, $this->maxZ - $z);
	}

	/**
	 * Performs the same operation as shrink(), but operates on itself instead of returning a new object.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 *
	 * @return $this
	 */
	public function contract(float $x, float $y, float $z){
		$this->minX += $x;
		$this->minY += $y;
		$this->minZ += $z;
		$this->maxX -= $x;
		$this->maxY -= $y;
		$this->maxZ -= $z;

		return $this;
	}

	/**
	 * Sets the bounding box's bounds from another AxisAlignedBB, and returns itself
	 *
	 * @param AxisAlignedBB $bb
	 * @return $this
	 */
	public function setBB(AxisAlignedBB $bb){
		$this->minX = $bb->minX;
		$this->minY = $bb->minY;
		$this->minZ = $bb->minZ;
		$this->maxX = $bb->maxX;
		$this->maxY = $bb->maxY;
		$this->maxZ = $bb->maxZ;
		return $this;
	}

	/**
	 * Returns a new AxisAlignedBB shifted by the specified X, Y and Z
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 *
	 * @return AxisAlignedBB
	 */
	public function getOffsetBoundingBox(float $x, float $y, float $z) : AxisAlignedBB{
		return new AxisAlignedBB($this->minX + $x, $this->minY + $y, $this->minZ + $z, $this->maxX + $x, $this->maxY + $y, $this->maxZ + $z);
	}

	public function calculateXOffset(AxisAlignedBB $bb, float $x) : float{
		if($bb->maxY <= $this->minY or $bb->minY >= $this->maxY){
			return $x;
		}
		if($bb->maxZ <= $this->minZ or $bb->minZ >= $this->maxZ){
			return $x;
		}
		if($x > 0 and $bb->maxX <= $this->minX){
			$x1 = $this->minX - $bb->maxX;
			if($x1 < $x){
				$x = $x1;
			}
		}elseif($x < 0 and $bb->minX >= $this->maxX){
			$x2 = $this->maxX - $bb->minX;
			if($x2 > $x){
				$x = $x2;
			}
		}

		return $x;
	}

	public function calculateYOffset(AxisAlignedBB $bb, float $y) : float{
		if($bb->maxX <= $this->minX or $bb->minX >= $this->maxX){
			return $y;
		}
		if($bb->maxZ <= $this->minZ or $bb->minZ >= $this->maxZ){
			return $y;
		}
		if($y > 0 and $bb->maxY <= $this->minY){
			$y1 = $this->minY - $bb->maxY;
			if($y1 < $y){
				$y = $y1;
			}
		}elseif($y < 0 and $bb->minY >= $this->maxY){
			$y2 = $this->maxY - $bb->minY;
			if($y2 > $y){
				$y = $y2;
			}
		}

		return $y;
	}

	public function calculateZOffset(AxisAlignedBB $bb, float $z) : float{
		if($bb->maxX <= $this->minX or $bb->minX >= $this->maxX){
			return $z;
		}
		if($bb->maxY <= $this->minY or $bb->minY >= $this->maxY){
			return $z;
		}
		if($z > 0 and $bb->maxZ <= $this->minZ){
			$z1 = $this->minZ - $bb->maxZ;
			if($z1 < $z){
				$z = $z1;
			}
		}elseif($z < 0 and $bb->minZ >= $this->maxZ){
			$z2 = $this->maxZ - $bb->minZ;
			if($z2 > $z){
				$z = $z2;
			}
		}

		return $z;
	}

	/**
	 * Returns whether any part of the specified AABB is inside (intersects with) this one.
	 *
	 * @param AxisAlignedBB $bb
	 * @return bool
	 */
	public function intersectsWith(AxisAlignedBB $bb) : bool{
		if($bb->maxX > $this->minX and $bb->minX < $this->maxX){
			if($bb->maxY > $this->minY and $bb->minY < $this->maxY){
				return $bb->maxZ > $this->minZ and $bb->minZ < $this->maxZ;
			}
		}

		return false;
	}

	/**
	 * Returns whether the specified vector is within the bounds of this AABB on all axes.
	 *
	 * @param Vector3 $vector
	 * @return bool
	 */
	public function isVectorInside(Vector3 $vector) : bool{
		if($vector->x <= $this->minX or $vector->x >= $this->maxX){
			return false;
		}
		if($vector->y <= $this->minY or $vector->y >= $this->maxY){
			return false;
		}

		return $vector->z > $this->minZ and $vector->z < $this->maxZ;
	}

	/**
	 * Returns the mean average of the AABB's X, Y and Z lengths.
	 * @return float
	 */
	public function getAverageEdgeLength() : float{
		return ($this->maxX - $this->minX + $this->maxY - $this->minY + $this->maxZ - $this->minZ) / 3;
	}

	/**
	 * Returns whether the specified vector is within the Y and Z bounds of this AABB.
	 *
	 * @param Vector3 $vector
	 * @return bool
	 */
	public function isVectorInYZ(Vector3 $vector) : bool{
		return $vector->y >= $this->minY and $vector->y <= $this->maxY and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}

	/**
	 * Returns whether the specified vector is within the X and Z bounds of this AABB.
	 *
	 * @param Vector3 $vector
	 * @return bool
	 */
	public function isVectorInXZ(Vector3 $vector) : bool{
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}

	/**
	 * Returns whether the specified vector is within the X and Y bounds of this AABB.
	 *
	 * @param Vector3 $vector
	 * @return bool
	 */
	public function isVectorInXY(Vector3 $vector) : bool{
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->y >= $this->minY and $vector->y <= $this->maxY;
	}

	/**
	 * Performs a ray-trace and calculates the point on the AABB's edge nearest the start position that the ray-trace
	 * collided with. Returns a RayTraceResult with colliding vector closest to the start position.
	 * Returns null if no colliding point was found.
	 *
	 * @param Vector3 $pos1
	 * @param Vector3 $pos2
	 *
	 * @return RayTraceResult|null
	 */
	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2) : ?RayTraceResult{
		$v1 = $pos1->getIntermediateWithXValue($pos2, $this->minX);
		$v2 = $pos1->getIntermediateWithXValue($pos2, $this->maxX);
		$v3 = $pos1->getIntermediateWithYValue($pos2, $this->minY);
		$v4 = $pos1->getIntermediateWithYValue($pos2, $this->maxY);
		$v5 = $pos1->getIntermediateWithZValue($pos2, $this->minZ);
		$v6 = $pos1->getIntermediateWithZValue($pos2, $this->maxZ);

		if($v1 !== null and !$this->isVectorInYZ($v1)){
			$v1 = null;
		}

		if($v2 !== null and !$this->isVectorInYZ($v2)){
			$v2 = null;
		}

		if($v3 !== null and !$this->isVectorInXZ($v3)){
			$v3 = null;
		}

		if($v4 !== null and !$this->isVectorInXZ($v4)){
			$v4 = null;
		}

		if($v5 !== null and !$this->isVectorInXY($v5)){
			$v5 = null;
		}

		if($v6 !== null and !$this->isVectorInXY($v6)){
			$v6 = null;
		}

		$vector = null;
		$distance = PHP_INT_MAX;

		foreach([$v1, $v2, $v3, $v4, $v5, $v6] as $v){
			if($v !== null and ($d = $pos1->distanceSquared($v)) < $distance){
				$vector = $v;
				$distance = $d;
			}
		}

		if($vector === null){
			return null;
		}

		$f = -1;

		if($vector === $v1){
			$f = Vector3::SIDE_WEST;
		}elseif($vector === $v2){
			$f = Vector3::SIDE_EAST;
		}elseif($vector === $v3){
			$f = Vector3::SIDE_DOWN;
		}elseif($vector === $v4){
			$f = Vector3::SIDE_UP;
		}elseif($vector === $v5){
			$f = Vector3::SIDE_NORTH;
		}elseif($vector === $v6){
			$f = Vector3::SIDE_SOUTH;
		}

		return new RayTraceResult($this, $f, $vector);
	}

	public function __toString(){
		return "AxisAlignedBB({$this->minX}, {$this->minY}, {$this->minZ}, {$this->maxX}, {$this->maxY}, {$this->maxZ})";
	}
}
