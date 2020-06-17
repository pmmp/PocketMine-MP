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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class BossEventPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::BOSS_EVENT_PACKET;

	/* S2C: Shows the boss-bar to the player. */
	public const TYPE_SHOW = 0;
	/* C2S: Registers a player to a boss fight. */
	public const TYPE_REGISTER_PLAYER = 1;
	/* S2C: Removes the boss-bar from the client. */
	public const TYPE_HIDE = 2;
	/* C2S: Unregisters a player from a boss fight. */
	public const TYPE_UNREGISTER_PLAYER = 3;
	/* S2C: Sets the bar percentage. */
	public const TYPE_HEALTH_PERCENT = 4;
	/* S2C: Sets title of the bar. */
	public const TYPE_TITLE = 5;
	/* S2C: Not sure on this. Includes color and overlay fields, plus an unknown short. TODO: check this */
	public const TYPE_UNKNOWN_6 = 6;
	/* S2C: Not implemented :( Intended to alter bar appearance, but these currently produce no effect on client-side whatsoever. */
	public const TYPE_TEXTURE = 7;

	/** @var int */
	public $bossEid;
	/** @var int */
	public $eventType;

	/** @var int (long) */
	public $playerEid;
	/** @var float */
	public $healthPercent;
	/** @var string */
	public $title;
	/** @var int */
	public $unknownShort;
	/** @var int */
	public $color;
	/** @var int */
	public $overlay;

	private static function base(int $bossEntityUniqueId, int $eventId) : self{
		$result = new self;
		$result->bossEid = $bossEntityUniqueId;
		$result->eventType = $eventId;
		return $result;
	}

	public static function show(int $bossEntityUniqueId, string $title, float $healthPercent, int $unknownShort = 0) : self{
		$result = self::base($bossEntityUniqueId, self::TYPE_SHOW);
		$result->title = $title;
		$result->healthPercent = $healthPercent;
		$result->unknownShort = $unknownShort;
		$result->color = 0; //hardcoded due to being useless
		$result->overlay = 0;
		return $result;
	}

	public static function hide(int $bossEntityUniqueId) : self{
		return self::base($bossEntityUniqueId, self::TYPE_HIDE);
	}

	public static function registerPlayer(int $bossEntityUniqueId, int $playerEntityUniqueId) : self{
		$result = self::base($bossEntityUniqueId, self::TYPE_REGISTER_PLAYER);
		$result->playerEid = $playerEntityUniqueId;
		return $result;
	}

	public static function unregisterPlayer(int $bossEntityUniqueId, int $playerEntityUniqueId) : self{
		$result = self::base($bossEntityUniqueId, self::TYPE_UNREGISTER_PLAYER);
		$result->playerEid = $playerEntityUniqueId;
		return $result;
	}

	public static function healthPercent(int $bossEntityUniqueId, float $healthPercent) : self{
		$result = self::base($bossEntityUniqueId, self::TYPE_HEALTH_PERCENT);
		$result->healthPercent = $healthPercent;
		return $result;
	}

	public static function title(int $bossEntityUniqueId, string $title) : self{
		$result = self::base($bossEntityUniqueId, self::TYPE_TITLE);
		$result->title = $title;
		return $result;
	}

	public static function unknown6(int $bossEntityUniqueId, int $unknownShort) : self{
		$result = self::base($bossEntityUniqueId, self::TYPE_UNKNOWN_6);
		$result->unknownShort = $unknownShort;
		$result->color = 0; //hardcoded due to being useless
		$result->overlay = 0;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->bossEid = $in->getEntityUniqueId();
		$this->eventType = $in->getUnsignedVarInt();
		switch($this->eventType){
			case self::TYPE_REGISTER_PLAYER:
			case self::TYPE_UNREGISTER_PLAYER:
				$this->playerEid = $in->getEntityUniqueId();
				break;
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::TYPE_SHOW:
				$this->title = $in->getString();
				$this->healthPercent = $in->getLFloat();
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::TYPE_UNKNOWN_6:
				$this->unknownShort = $in->getLShort();
			case self::TYPE_TEXTURE:
				$this->color = $in->getUnsignedVarInt();
				$this->overlay = $in->getUnsignedVarInt();
				break;
			case self::TYPE_HEALTH_PERCENT:
				$this->healthPercent = $in->getLFloat();
				break;
			case self::TYPE_TITLE:
				$this->title = $in->getString();
				break;
			default:
				break;
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putEntityUniqueId($this->bossEid);
		$out->putUnsignedVarInt($this->eventType);
		switch($this->eventType){
			case self::TYPE_REGISTER_PLAYER:
			case self::TYPE_UNREGISTER_PLAYER:
				$out->putEntityUniqueId($this->playerEid);
				break;
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::TYPE_SHOW:
				$out->putString($this->title);
				$out->putLFloat($this->healthPercent);
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::TYPE_UNKNOWN_6:
				$out->putLShort($this->unknownShort);
			case self::TYPE_TEXTURE:
				$out->putUnsignedVarInt($this->color);
				$out->putUnsignedVarInt($this->overlay);
				break;
			case self::TYPE_HEALTH_PERCENT:
				$out->putLFloat($this->healthPercent);
				break;
			case self::TYPE_TITLE:
				$out->putString($this->title);
				break;
			default:
				break;
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleBossEvent($this);
	}
}
