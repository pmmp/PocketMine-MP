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

namespace pocketmine\data\runtime;

/**
 * This class is auto-generated. Do not edit it manually.
 * @see build/generate-runtime-enum-serializers.php
 */
trait RuntimeEnumSizeCalculatorTrait{

	abstract protected function addBits(int $bits) : void;

	public function bellAttachmentType(\pocketmine\block\utils\BellAttachmentType &$value) : void{
		$this->addBits(2);
	}

	public function copperOxidation(\pocketmine\block\utils\CopperOxidation &$value) : void{
		$this->addBits(2);
	}

	public function coralType(\pocketmine\block\utils\CoralType &$value) : void{
		$this->addBits(3);
	}

	public function dirtType(\pocketmine\block\utils\DirtType &$value) : void{
		$this->addBits(2);
	}

	public function dyeColor(\pocketmine\block\utils\DyeColor &$value) : void{
		$this->addBits(4);
	}

	public function froglightType(\pocketmine\block\utils\FroglightType &$value) : void{
		$this->addBits(2);
	}

	public function leverFacing(\pocketmine\block\utils\LeverFacing &$value) : void{
		$this->addBits(3);
	}

	public function medicineType(\pocketmine\item\MedicineType &$value) : void{
		$this->addBits(2);
	}

	public function mobHeadType(\pocketmine\block\utils\MobHeadType &$value) : void{
		$this->addBits(3);
	}

	public function mushroomBlockType(\pocketmine\block\utils\MushroomBlockType &$value) : void{
		$this->addBits(4);
	}

	public function potionType(\pocketmine\item\PotionType &$value) : void{
		$this->addBits(6);
	}

	public function slabType(\pocketmine\block\utils\SlabType &$value) : void{
		$this->addBits(2);
	}

	public function suspiciousStewType(\pocketmine\item\SuspiciousStewType &$value) : void{
		$this->addBits(4);
	}

}
