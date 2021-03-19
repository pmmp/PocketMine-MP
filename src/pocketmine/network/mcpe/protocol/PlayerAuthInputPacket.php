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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\types\PlayMode;
use function assert;

class PlayerAuthInputPacket extends DataPacket/* implements ServerboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::PLAYER_AUTH_INPUT_PACKET;

	/** @var Vector3 */
	private $position;
	/** @var float */
	private $pitch;
	/** @var float */
	private $yaw;
	/** @var float */
	private $headYaw;
	/** @var float */
	private $moveVecX;
	/** @var float */
	private $moveVecZ;
	/** @var int */
	private $inputFlags;
	/** @var int */
	private $inputMode;
	/** @var int */
	private $playMode;
	/** @var Vector3|null */
	private $vrGazeDirection = null;
	/** @var int */
	private $tick;
	/** @var Vector3 */
	private $delta;

	/**
	 * @param int          $inputFlags @see InputFlags
	 * @param int          $inputMode @see InputMode
	 * @param int          $playMode @see PlayMode
	 * @param Vector3|null $vrGazeDirection only used when PlayMode::VR
	 */
	public static function create(Vector3 $position, float $pitch, float $yaw, float $headYaw, float $moveVecX, float $moveVecZ, int $inputFlags, int $inputMode, int $playMode, ?Vector3 $vrGazeDirection, int $tick, Vector3 $delta) : self{
		if($playMode === PlayMode::VR and $vrGazeDirection === null){
			//yuck, can we get a properly written packet just once? ...
			throw new \InvalidArgumentException("Gaze direction must be provided for VR play mode");
		}
		$result = new self;
		$result->position = $position->asVector3();
		$result->pitch = $pitch;
		$result->yaw = $yaw;
		$result->headYaw = $headYaw;
		$result->moveVecX = $moveVecX;
		$result->moveVecZ = $moveVecZ;
		$result->inputFlags = $inputFlags;
		$result->inputMode = $inputMode;
		$result->playMode = $playMode;
		if($vrGazeDirection !== null){
			$result->vrGazeDirection = $vrGazeDirection->asVector3();
		}
		$result->tick = $tick;
		$result->delta = $delta;
		return $result;
	}

	public function getPosition() : Vector3{
		return $this->position;
	}

	public function getPitch() : float{
		return $this->pitch;
	}

	public function getYaw() : float{
		return $this->yaw;
	}

	public function getHeadYaw() : float{
		return $this->headYaw;
	}

	public function getMoveVecX() : float{
		return $this->moveVecX;
	}

	public function getMoveVecZ() : float{
		return $this->moveVecZ;
	}

	/**
	 * @see PlayerAuthInputFlags
	 */
	public function getInputFlags() : int{
		return $this->inputFlags;
	}

	/**
	 * @see InputMode
	 */
	public function getInputMode() : int{
		return $this->inputMode;
	}

	/**
	 * @see PlayMode
	 */
	public function getPlayMode() : int{
		return $this->playMode;
	}

	public function getVrGazeDirection() : ?Vector3{
		return $this->vrGazeDirection;
	}

	public function getTick() : int{ return $this->tick; }

	public function getDelta() : Vector3{ return $this->delta; }

	protected function decodePayload() : void{
		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		$this->position = $this->getVector3();
		$this->moveVecX = $this->getLFloat();
		$this->moveVecZ = $this->getLFloat();
		$this->headYaw = $this->getLFloat();
		$this->inputFlags = $this->getUnsignedVarLong();
		$this->inputMode = $this->getUnsignedVarInt();
		$this->playMode = $this->getUnsignedVarInt();
		if($this->playMode === PlayMode::VR){
			$this->vrGazeDirection = $this->getVector3();
		}
		$this->tick = $this->getUnsignedVarLong();
		$this->delta = $this->getVector3();
	}

	protected function encodePayload() : void{
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		$this->putVector3($this->position);
		$this->putLFloat($this->moveVecX);
		$this->putLFloat($this->moveVecZ);
		$this->putLFloat($this->headYaw);
		$this->putUnsignedVarLong($this->inputFlags);
		$this->putUnsignedVarInt($this->inputMode);
		$this->putUnsignedVarInt($this->playMode);
		if($this->playMode === PlayMode::VR){
			assert($this->vrGazeDirection !== null);
			$this->putVector3($this->vrGazeDirection);
		}
		$this->putUnsignedVarLong($this->tick);
		$this->putVector3($this->delta);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handlePlayerAuthInput($this);
	}
}
