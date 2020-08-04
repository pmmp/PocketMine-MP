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

namespace pocketmine\utils;

/**
 * @internal
 * @see TextFormat::toJSON()
 */
final class TextFormatJsonObject implements \JsonSerializable{
	/** @var string|null */
	public $text = null;
	/** @var string|null */
	public $color = null;
	/** @var bool|null */
	public $bold = null;
	/** @var bool|null */
	public $italic = null;
	/** @var bool|null */
	public $underlined = null;
	/** @var bool|null */
	public $strikethrough = null;
	/** @var bool|null */
	public $obfuscated = null;
	/**
	 * @var TextFormatJsonObject[]|null
	 * @phpstan-var array<int, TextFormatJsonObject>|null
	 */
	public $extra = null;

	public function jsonSerialize(){
		$result = (array) $this;
		foreach($result as $k => $v){
			if($v === null){
				unset($result[$k]);
			}
		}
		return $result;
	}
}
