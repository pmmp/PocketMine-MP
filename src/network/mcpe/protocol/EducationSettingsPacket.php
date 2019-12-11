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

use pocketmine\network\mcpe\handler\PacketHandler;

class EducationSettingsPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::EDUCATION_SETTINGS_PACKET;

	/** @var string */
	private $codeBuilderDefaultUri;
	/** @var bool */
	private $hasQuiz;

	public static function create(string $codeBuilderDefaultUri, bool $hasQuiz) : self{
		$result = new self;
		$result->codeBuilderDefaultUri = $codeBuilderDefaultUri;
		$result->hasQuiz = $hasQuiz;
		return $result;
	}

	public function getCodeBuilderDefaultUri() : string{
		return $this->codeBuilderDefaultUri;
	}

	public function getHasQuiz() : bool{
		return $this->hasQuiz;
	}

	protected function decodePayload() : void{
		$this->codeBuilderDefaultUri = $this->getString();
		$this->hasQuiz = $this->getBool();
	}

	protected function encodePayload() : void{
		$this->putString($this->codeBuilderDefaultUri);
		$this->putBool($this->hasQuiz);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleEducationSettings($this);
	}
}
