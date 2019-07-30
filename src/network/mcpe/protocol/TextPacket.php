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
use function count;

class TextPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::TEXT_PACKET;

	public const TYPE_RAW = 0;
	public const TYPE_CHAT = 1;
	public const TYPE_TRANSLATION = 2;
	public const TYPE_POPUP = 3;
	public const TYPE_JUKEBOX_POPUP = 4;
	public const TYPE_TIP = 5;
	public const TYPE_SYSTEM = 6;
	public const TYPE_WHISPER = 7;
	public const TYPE_ANNOUNCEMENT = 8;
	public const TYPE_JSON = 9;

	/** @var int */
	public $type;
	/** @var bool */
	public $needsTranslation = false;
	/** @var string */
	public $sourceName;
	/** @var string */
	public $message;
	/** @var string[] */
	public $parameters = [];
	/** @var string */
	public $xboxUserId = "";
	/** @var string */
	public $platformChatId = "";

	private static function messageOnly(int $type, string $message) : self{
		$result = new self;
		$result->type = $type;
		$result->message = $message;
		return $result;
	}

	private static function baseTranslation(int $type, string $key, array $parameters) : self{
		$result = new self;
		$result->type = $type;
		$result->needsTranslation = true;
		$result->message = $key;
		$result->parameters = $parameters;
		return $result;
	}

	public static function raw(string $message) : self{
		return self::messageOnly(self::TYPE_RAW, $message);
	}

	/**
	 * @param string    $key
	 * @param string[]  $parameters
	 *
	 * @return TextPacket
	 */
	public static function translation(string $key, array $parameters = []) : self{
		return self::baseTranslation(self::TYPE_TRANSLATION, $key, $parameters);
	}

	public static function popup(string $message) : self{
		return self::messageOnly(self::TYPE_POPUP, $message);
	}

	/**
	 * @param string   $key
	 * @param string[] $parameters
	 *
	 * @return TextPacket
	 */
	public static function translatedPopup(string $key, array $parameters = []) : self{
		return self::baseTranslation(self::TYPE_POPUP, $key, $parameters);
	}

	/**
	 * @param string   $key
	 * @param string[] $parameters
	 *
	 * @return TextPacket
	 */
	public static function jukeboxPopup(string $key, array $parameters = []) : self{
		return self::baseTranslation(self::TYPE_JUKEBOX_POPUP, $key, $parameters);
	}

	public static function tip(string $message) : self{
		return self::messageOnly(self::TYPE_TIP, $message);
	}

	protected function decodePayload() : void{
		$this->type = $this->getByte();
		$this->needsTranslation = $this->getBool();
		switch($this->type){
			case self::TYPE_CHAT:
			case self::TYPE_WHISPER:
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::TYPE_ANNOUNCEMENT:
				$this->sourceName = $this->getString();
			case self::TYPE_RAW:
			case self::TYPE_TIP:
			case self::TYPE_SYSTEM:
			case self::TYPE_JSON:
				$this->message = $this->getString();
				break;

			case self::TYPE_TRANSLATION:
			case self::TYPE_POPUP:
			case self::TYPE_JUKEBOX_POPUP:
				$this->message = $this->getString();
				$count = $this->getUnsignedVarInt();
				for($i = 0; $i < $count; ++$i){
					$this->parameters[] = $this->getString();
				}
				break;
		}

		$this->xboxUserId = $this->getString();
		$this->platformChatId = $this->getString();
	}

	protected function encodePayload() : void{
		$this->putByte($this->type);
		$this->putBool($this->needsTranslation);
		switch($this->type){
			case self::TYPE_CHAT:
			case self::TYPE_WHISPER:
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::TYPE_ANNOUNCEMENT:
				$this->putString($this->sourceName);
			case self::TYPE_RAW:
			case self::TYPE_TIP:
			case self::TYPE_SYSTEM:
			case self::TYPE_JSON:
				$this->putString($this->message);
				break;

			case self::TYPE_TRANSLATION:
			case self::TYPE_POPUP:
			case self::TYPE_JUKEBOX_POPUP:
				$this->putString($this->message);
				$this->putUnsignedVarInt(count($this->parameters));
				foreach($this->parameters as $p){
					$this->putString($p);
				}
				break;
		}

		$this->putString($this->xboxUserId);
		$this->putString($this->platformChatId);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleText($this);
	}
}
