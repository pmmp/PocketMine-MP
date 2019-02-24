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

	/** @var bool */
	protected $triggered = false;
	/** @var bool */
	protected $suspended = false; //unclear usage, makes hitbox bigger if set
	/** @var bool */
	protected $connected = false;
	/** @var bool */
	protected $disarmed = false;

	protected function writeStateToMeta() : int{
		return ($this->triggered ? 0x01 : 0) | ($this->suspended ? 0x02 : 0) | ($this->connected ? 0x04 : 0) | ($this->disarmed ? 0x08 : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->triggered = ($stateMeta & 0x01) !== 0;
		$this->suspended = ($stateMeta & 0x02) !== 0;
		$this->connected = ($stateMeta & 0x04) !== 0;
		$this->disarmed = ($stateMeta & 0x08) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
