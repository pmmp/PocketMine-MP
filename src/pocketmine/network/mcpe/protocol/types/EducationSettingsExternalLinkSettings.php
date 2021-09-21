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

final class EducationSettingsExternalLinkSettings{

	private string $displayName;
	private string $url;

	public function __construct(string $url, string $displayName){
		$this->displayName = $displayName;
		$this->url = $url;
	}

	public function getUrl() : string{ return $this->url; }

	public function getDisplayName() : string{ return $this->displayName; }

	public static function read(NetworkBinaryStream $in) : self{
		$url = $in->getString();
		$displayName = $in->getString();
		return new self($displayName, $url);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->url);
		$out->putString($this->displayName);
	}
}
