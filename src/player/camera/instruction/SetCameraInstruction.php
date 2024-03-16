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

namespace pocketmine\player\camera\instruction;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEase;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionRotation;

final class SetCameraInstruction extends CameraInstruction{

	public ?CameraPreset $cameraPreset = null;
	public ?CameraSetInstructionEase $ease = null;
	public ?Vector3 $cameraPosition = null;
	public ?CameraSetInstructionRotation $rotation = null;
	public ?Vector3 $facingPosition = null;

	public function setPreset(CameraPreset $cameraPreset) : void
	{
		$this->cameraPreset = $cameraPreset;
	}

	public function setEase(int $type, float $duration) : void
	{
		$this->ease = new CameraSetInstructionEase($type, $duration);
	}

	public function setCameraPostion(Vector3 $cameraPosition) : void
	{
		$this->cameraPosition = $cameraPosition;
	}

	public function setRotation(float $pitch, float $yaw) : void
	{
		$this->rotation = new CameraSetInstructionRotation($pitch, $yaw);
	}

	public function setFacingPosition(Vector3 $facingPosition) : void
	{
		$this->facingPosition = $facingPosition;
	}
}
