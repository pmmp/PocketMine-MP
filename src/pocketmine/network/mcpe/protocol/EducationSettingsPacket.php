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

use pocketmine\network\mcpe\NetworkSession;

class EducationSettingsPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::EDUCATION_SETTINGS_PACKET;

	/** @var string */
	private $codeBuilderDefaultUri;
	/** @var string */
	private $codeBuilderTitle;
	/** @var bool */
	private $canResizeCodeBuilder;
	/** @var string|null */
	private $codeBuilderOverrideUri;
	/** @var bool */
	private $hasQuiz;

	public static function create(string $codeBuilderDefaultUri, string $codeBuilderTitle, bool $canResizeCodeBuilder, ?string $codeBuilderOverrideUri, bool $hasQuiz) : self{
		$result = new self;
		$result->codeBuilderDefaultUri = $codeBuilderDefaultUri;
		$result->codeBuilderTitle = $codeBuilderTitle;
		$result->canResizeCodeBuilder = $canResizeCodeBuilder;
		$result->codeBuilderOverrideUri = $codeBuilderOverrideUri;
		$result->hasQuiz = $hasQuiz;
		return $result;
	}

	public function getCodeBuilderDefaultUri() : string{
		return $this->codeBuilderDefaultUri;
	}

	public function getCodeBuilderTitle() : string{
		return $this->codeBuilderTitle;
	}

	public function canResizeCodeBuilder() : bool{
		return $this->canResizeCodeBuilder;
	}

	public function getCodeBuilderOverrideUri() : ?string{
		return $this->codeBuilderOverrideUri;
	}

	public function getHasQuiz() : bool{
		return $this->hasQuiz;
	}

	protected function decodePayload() : void{
		$this->codeBuilderDefaultUri = $this->getString();
		$this->codeBuilderTitle = $this->getString();
		$this->canResizeCodeBuilder = $this->getBool();
		if($this->getBool()){
			$this->codeBuilderOverrideUri = $this->getString();
		}else{
			$this->codeBuilderOverrideUri = null;
		}
		$this->hasQuiz = $this->getBool();
	}

	protected function encodePayload() : void{
		$this->putString($this->codeBuilderDefaultUri);
		$this->putString($this->codeBuilderTitle);
		$this->putBool($this->canResizeCodeBuilder);
		$this->putBool($this->codeBuilderOverrideUri !== null);
		if($this->codeBuilderOverrideUri !== null){
			$this->putString($this->codeBuilderOverrideUri);
		}
		$this->putBool($this->hasQuiz);
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleEducationSettings($this);
	}
}
