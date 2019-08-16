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

namespace pocketmine\block;

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use function gmp_add;
use function gmp_and;
use function gmp_intval;
use function gmp_mul;
use function gmp_xor;

class Bamboo extends Transparent{

	public const NO_LEAVES = 0;
	public const SMALL_LEAVES = 1;
	public const LARGE_LEAVES = 2;

	/** @var bool */
	protected $thick = false; //age in PC, but this is 0/1
	/** @var bool */
	protected $ready = false;
	/** @var int */
	protected $leafSize = self::NO_LEAVES;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->thick = ($stateMeta & BlockLegacyMetadata::BAMBOO_FLAG_THICK) !== 0;
		$this->leafSize = BlockDataSerializer::readBoundedInt("leafSize", ($stateMeta >> BlockLegacyMetadata::BAMBOO_LEAF_SIZE_SHIFT) & BlockLegacyMetadata::BAMBOO_LEAF_SIZE_MASK, self::NO_LEAVES, self::LARGE_LEAVES);
		$this->ready = ($stateMeta & BlockLegacyMetadata::BAMBOO_FLAG_READY) !== 0;
	}

	public function writeStateToMeta() : int{
		return ($this->thick ? BlockLegacyMetadata::BAMBOO_FLAG_THICK : 0) | ($this->leafSize << BlockLegacyMetadata::BAMBOO_LEAF_SIZE_SHIFT) | ($this->ready ? BlockLegacyMetadata::BAMBOO_FLAG_READY : 0);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		//this places the BB at the northwest corner, not the center
		$inset = 1 - (($this->thick ? 3 : 2) / 16);
		return [AxisAlignedBB::one()->trim(Facing::SOUTH, $inset)->trim(Facing::EAST, $inset)];
	}

	private static function getOffsetSeed(int $x, int $y, int $z) : int{
		$p1 = gmp_mul($z, 0x6ebfff5);
		$p2 = gmp_mul($x, 0x2fc20f);
		$p3 = $y;

		$xord = gmp_xor(gmp_xor($p1, $p2), $p3);

		$fullResult = gmp_mul(gmp_add(gmp_mul($xord, 0x285b825), 0xb), $xord);
		return gmp_intval(gmp_and($fullResult, 0xffffffff));
	}

	public function getPosOffset() : ?Vector3{
		$seed = self::getOffsetSeed($this->pos->getFloorX(), 0, $this->pos->getFloorZ());
		$retX = (($seed % 12) + 1) / 16;
		$retZ = ((($seed >> 8) % 12) + 1) / 16;
		return new Vector3($retX, 0, $retZ);
	}
}
