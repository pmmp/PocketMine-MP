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

final class ItemUseResult{
	/** @var ItemUseResult */
	private static $NONE;
	/** @var ItemUseResult */
	private static $FAILED;
	/** @var ItemUseResult */
	private static $SUCCEEDED;

	/**
	 * No action attempted to take place. Does nothing.
	 *
	 * @return ItemUseResult
	 */
	public static function NONE() : ItemUseResult{
		return self::$NONE ?? (self::$NONE = new self());
	}

	/**
	 * An action attempted to take place, but failed due to cancellation. This triggers rollback behaviour for a remote
	 * player.
	 *
	 * @return ItemUseResult
	 */
	public static function FAIL() : ItemUseResult{
		return self::$FAILED ?? (self::$FAILED = new self());
	}

	/**
	 * An action took place successfully. Changes inventory contents if appropriate.
	 *
	 * @return ItemUseResult
	 */
	public static function SUCCESS() : ItemUseResult{
		return self::$SUCCEEDED ?? (self::$SUCCEEDED = new self());
	}

	private function __construct(){
		//NOOP
	}
}
