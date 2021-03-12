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

class ClientboundDebugRendererPacket extends DataPacket/* implements ClientboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_DEBUG_RENDERER_PACKET;

	public const TYPE_CLEAR = 1;
	public const TYPE_ADD_CUBE = 2;

	/** @var int */
	private $type;

	//TODO: if more types are added, we'll probably want to make a separate data type and interfaces
	/** @var string */
	private $text;
	/** @var Vector3 */
	private $position;
	/** @var float */
	private $red;
	/** @var float */
	private $green;
	/** @var float */
	private $blue;
	/** @var float */
	private $alpha;
	/** @var int */
	private $durationMillis;

	private static function base(int $type) : self{
		$result = new self;
		$result->type = $type;
		return $result;
	}

	public static function clear() : self{ return self::base(self::TYPE_CLEAR); }

	public static function addCube(string $text, Vector3 $position, float $red, float $green, float $blue, float $alpha, int $durationMillis) : self{
		$result = self::base(self::TYPE_ADD_CUBE);
		$result->text = $text;
		$result->position = $position;
		$result->red = $red;
		$result->green = $green;
		$result->blue = $blue;
		$result->alpha = $alpha;
		$result->durationMillis = $durationMillis;
		return $result;
	}

	public function getType() : int{ return $this->type; }

	public function getText() : string{ return $this->text; }

	public function getPosition() : Vector3{ return $this->position; }

	public function getRed() : float{ return $this->red; }

	public function getGreen() : float{ return $this->green; }

	public function getBlue() : float{ return $this->blue; }

	public function getAlpha() : float{ return $this->alpha; }

	public function getDurationMillis() : int{ return $this->durationMillis; }

	protected function decodePayload() : void{
		$this->type = $this->getLInt();

		switch($this->type){
			case self::TYPE_CLEAR:
				//NOOP
				break;
			case self::TYPE_ADD_CUBE:
				$this->text = $this->getString();
				$this->position = $this->getVector3();
				$this->red = $this->getLFloat();
				$this->green = $this->getLFloat();
				$this->blue = $this->getLFloat();
				$this->alpha = $this->getLFloat();
				$this->durationMillis = $this->getLLong();
				break;
			default:
				throw new \UnexpectedValueException("Unknown type " . $this->type);
		}
	}

	protected function encodePayload() : void{
		$this->putLInt($this->type);

		switch($this->type){
			case self::TYPE_CLEAR:
				//NOOP
				break;
			case self::TYPE_ADD_CUBE:
				$this->putString($this->text);
				$this->putVector3($this->position);
				$this->putLFloat($this->red);
				$this->putLFloat($this->green);
				$this->putLFloat($this->blue);
				$this->putLFloat($this->alpha);
				$this->putLLong($this->durationMillis);
				break;
			default:
				throw new \InvalidArgumentException("Unknown type " . $this->type);
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleClientboundDebugRenderer($this);
	}
}
