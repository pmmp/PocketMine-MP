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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\NetworkBinaryStream;

final class EducationUriResource{
	private string $buttonName;
	private string $linkUri;

	public function __construct(string $buttonName, string $linkUri){
		$this->buttonName = $buttonName;
		$this->linkUri = $linkUri;
	}

	public function getButtonName() : string{ return $this->buttonName; }

	public function getLinkUri() : string{ return $this->linkUri; }

	public static function read(NetworkBinaryStream $in) : self{
		$buttonName = $in->getString();
		$linkUri = $in->getString();
		return new self($buttonName, $linkUri);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->buttonName);
		$out->putString($this->linkUri);
	}
}
