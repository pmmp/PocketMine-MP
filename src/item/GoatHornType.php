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

namespace pocketmine\item;

use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

enum GoatHornType{

	case PONDER;
	case SING;
	case SEEK;
	case FEEL;
	case ADMIRE;
	case CALL;
	case YEARN;
	case DREAM;

	/**
	 * @phpstan-return LevelSoundEvent::*
	 */
	public function getSoundId() : int{
		return match($this){
			self::PONDER => LevelSoundEvent::HORN_CALL0,
			self::SING => LevelSoundEvent::HORN_CALL1,
			self::SEEK => LevelSoundEvent::HORN_CALL2,
			self::FEEL => LevelSoundEvent::HORN_CALL3,
			self::ADMIRE => LevelSoundEvent::HORN_CALL4,
			self::CALL => LevelSoundEvent::HORN_CALL5,
			self::YEARN => LevelSoundEvent::HORN_CALL6,
			self::DREAM => LevelSoundEvent::HORN_CALL7
		};
	}
}
