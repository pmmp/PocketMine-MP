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

namespace pocketmine\math;

/**
 * WARNING: This class is available on the PocketMine-MP Zephir project.
 * If this class is modified, remember to modify the PHP C extension.
 */
class AxisAlignedBB{
	public $minX;
	public $minY;
	public $minZ;
	public $maxX;
	public $maxY;
	public $maxZ;

	public function __construct($minX, $minY, $minZ, $maxX, $maxY, $maxZ){
		$this->minX = $minX;
		$this->minY = $minY;
		$this->minZ = $minZ;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->maxZ = $maxZ;
	}

	public function setBounds($minX, $minY, $minZ, $maxX, $maxY, $maxZ){
		$this->minX = $minX;
		$this->minY = $minY;
		$this->minZ = $minZ;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->maxZ = $maxZ;

		return $this;
	}

	public function addCoord($x, $y, $z){
		if($x < 0){
			$this->minX += $x;
		}elseif($x > 0){
			$this->maxX += $x;
		}

		if($y < 0){
			$this->minY += $y;
		}elseif($y > 0){
			$this->maxY += $y;
		}

		if($z < 0){
			$this->minZ += $z;
		}elseif($z > 0){
			$this->maxZ += $z;
		}

		return $this;
	}

	public function expand($x, $y, $z){
		$vec = clone $this;
		$vec->minX -= $x;
		$vec->minY -= $y;
		$vec->minZ -= $z;
		$vec->maxX += $x;
		$vec->maxY += $y;
		$vec->maxZ += $z;

		return $vec;
	}

	public function offset($x, $y, $z){
		$vec = clone $this;
		$vec->minX += $x;
		$vec->minY += $y;
		$vec->minZ += $z;
		$vec->maxX += $x;
		$vec->maxY += $y;
		$vec->maxZ += $z;

		return $vec;
	}

	public function contract($x, $y, $z){
		$vec = clone $this;
		$vec->minX += $x;
		$vec->minY += $y;
		$vec->minZ += $z;
		$vec->maxX -= $x;
		$vec->maxY -= $y;
		$vec->maxZ -= $z;

		return $vec;
	}

	public function setBB(AxisAlignedBB $bb){
		return new AxisAlignedBB(
			min($this->minX, $bb->minX),
			min($this->minY, $bb->minY),
			min($this->minZ, $bb->minZ),
			max($this->maxX, $bb->maxX),
			max($this->maxY, $bb->maxY),
			max($this->maxZ, $bb->maxZ)
		);
	}

	public function getOffsetBoundingBox($x, $y, $z){
		return new AxisAlignedBB($this->minX + $x, $this->minY + $y, $this->minZ + $z, $this->maxX + $x, $this->maxY + $y, $this->maxZ + $z);
	}

	public function calculateXOffset(AxisAlignedBB $bb, $x){
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
		}
		if($x < 0 and $bb->minX >= $this->maxX){
			$x2 = $this->maxX - $bb->minX;
			if($x2 > $x){
				$x = $x2;
			}
		}

		return $x;
	}

	public function calculateYOffset(AxisAlignedBB $bb, $y){
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
		}
		if($y < 0 and $bb->minY >= $this->maxY){
			$y2 = $this->maxY - $bb->minY;
			if($y2 > $y){
				$y = $y2;
			}
		}

		return $y;
	}

	public function calculateZOffset(AxisAlignedBB $bb, $z){
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
		}
		if($z < 0 and $bb->minZ >= $this->maxZ){
			$z2 = $this->maxZ - $bb->minZ;
			if($z2 > $z){
				$z = $z2;
			}
		}

		return $z;
	}

	public function intersectsWith(AxisAlignedBB $bb){
		if($bb->maxX > $this->minX and $bb->minX < $this->maxX){
			if($bb->maxY > $this->minY and $bb->minY < $this->maxY){
				return $bb->maxZ > $this->minZ and $bb->minZ < $this->maxZ;
			}
		}

		return false;
	}

	public function isVectorInside(Vector3 $vector){
		if($vector->x <= $this->minX or $vector->x >= $this->maxX){
			return false;
		}
		if($vector->y <= $this->minY or $vector->y >= $this->maxY){
			return false;
		}

		return $vector->z > $this->minZ and $vector->z < $this->maxZ;
	}

	public function getAverageEdgeLength(){
		return ($this->maxX - $this->minX + $this->maxY - $this->minY + $this->maxZ - $this->minZ) / 3;
	}

	public function isVectorInYZ(Vector3 $vector){
		return $vector->y >= $this->minY and $vector->y <= $this->maxY and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}

	public function isVectorInXZ(Vector3 $vector){
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}

	public function isVectorInXY(Vector3 $vector){
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->y >= $this->minY and $vector->y <= $this->maxY;
	}

	/*
	public function calculateIntercept(...){
	
	}
	*/
}