<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\NetworkBinaryStream;

/**
 * Apply a pattern to a banner using a loom.
 */
final class LoomStackRequestAction extends ItemStackRequestAction{

	public static function getTypeId() : int{
		return ItemStackRequestActionType::CRAFTING_LOOM;
	}

	public function __construct(
		private string $patternId
	){}

	public function getPatternId() : string{ return $this->patternId; }

	public static function read(NetworkBinaryStream $in) : self{
		return new self($in->getString());
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->patternId);
	}
}
