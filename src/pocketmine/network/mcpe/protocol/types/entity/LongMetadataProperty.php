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

namespace pocketmine\network\mcpe\protocol\types\entity;

use pocketmine\network\mcpe\serializer\NetworkBinaryStream;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

final class LongMetadataProperty implements MetadataProperty{
	use IntegerishMetadataProperty;

	protected function min() : int{
		return PHP_INT_MIN;
	}

	protected function max() : int{
		return PHP_INT_MAX;
	}

	public static function id() : int{
		return EntityMetadataTypes::LONG;
	}

	public static function read(NetworkBinaryStream $in) : self{
		return new self($in->getVarLong());
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVarLong($this->value);
	}
}
