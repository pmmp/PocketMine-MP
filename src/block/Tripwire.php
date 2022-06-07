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

class Tripwire extends Flowable{

	protected bool $triggered = false;
	protected bool $suspended = false; //unclear usage, makes hitbox bigger if set
	protected bool $connected = false;
	protected bool $disarmed = false;

	protected function writeStateToMeta() : int{
		return ($this->triggered ? BlockLegacyMetadata::TRIPWIRE_FLAG_TRIGGERED : 0) |
			($this->suspended ? BlockLegacyMetadata::TRIPWIRE_FLAG_SUSPENDED : 0) |
			($this->connected ? BlockLegacyMetadata::TRIPWIRE_FLAG_CONNECTED : 0) |
			($this->disarmed ? BlockLegacyMetadata::TRIPWIRE_FLAG_DISARMED : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->triggered = ($stateMeta & BlockLegacyMetadata::TRIPWIRE_FLAG_TRIGGERED) !== 0;
		$this->suspended = ($stateMeta & BlockLegacyMetadata::TRIPWIRE_FLAG_SUSPENDED) !== 0;
		$this->connected = ($stateMeta & BlockLegacyMetadata::TRIPWIRE_FLAG_CONNECTED) !== 0;
		$this->disarmed = ($stateMeta & BlockLegacyMetadata::TRIPWIRE_FLAG_DISARMED) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function isTriggered() : bool{ return $this->triggered; }

	/** @return $this */
	public function setTriggered(bool $triggered) : self{
		$this->triggered = $triggered;
		return $this;
	}

	public function isSuspended() : bool{ return $this->suspended; }

	/** @return $this */
	public function setSuspended(bool $suspended) : self{
		$this->suspended = $suspended;
		return $this;
	}

	public function isConnected() : bool{ return $this->connected; }

	/** @return $this */
	public function setConnected(bool $connected) : self{
		$this->connected = $connected;
		return $this;
	}

	public function isDisarmed() : bool{ return $this->disarmed; }

	/** @return $this */
	public function setDisarmed(bool $disarmed) : self{
		$this->disarmed = $disarmed;
		return $this;
	}
}
