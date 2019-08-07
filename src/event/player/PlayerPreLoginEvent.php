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

namespace pocketmine\event\player;

use pocketmine\event\Event;
use pocketmine\player\PlayerInfo;
use function array_keys;

/**
 * Called when a player connects to the server, prior to authentication taking place.
 * Cancelling this event will cause the player to be disconnected with the kick message set.
 *
 * This event should be used to decide if the player may continue to login to the server. Do things like checking
 * bans, whitelisting, server-full etc here.
 *
 * WARNING: Any information about the player CANNOT be trusted at this stage, because they are not authenticated and
 * could be a hacker posing as another player.
 */
class PlayerPreLoginEvent extends Event{
	public const KICK_REASON_PLUGIN = 0;
	public const KICK_REASON_SERVER_FULL = 1;
	public const KICK_REASON_SERVER_WHITELISTED = 2;
	public const KICK_REASON_BANNED = 3;

	public const KICK_REASON_PRIORITY = [
		self::KICK_REASON_PLUGIN, //Plugin reason should always take priority over anything else
		self::KICK_REASON_SERVER_FULL,
		self::KICK_REASON_SERVER_WHITELISTED,
		self::KICK_REASON_BANNED
	];

	/** @var PlayerInfo */
	private $playerInfo;
	/** @var string */
	private $ip;
	/** @var int */
	private $port;
	/** @var bool */
	protected $authRequired;

	/** @var string[] reason const => associated message */
	protected $kickReasons = [];

	/**
	 * @param PlayerInfo $playerInfo
	 * @param string     $ip
	 * @param int        $port
	 * @param bool       $authRequired
	 */
	public function __construct(PlayerInfo $playerInfo, string $ip, int $port, bool $authRequired){
		$this->playerInfo = $playerInfo;
		$this->ip = $ip;
		$this->port = $port;
		$this->authRequired = $authRequired;
	}

	/**
	 * Returns an object containing self-proclaimed information about the connecting player.
	 * WARNING: THE PLAYER IS NOT VERIFIED DURING THIS EVENT. At this point, it's unknown if the player is real or a
	 * hacker.
	 *
	 * @return PlayerInfo
	 */
	public function getPlayerInfo() : PlayerInfo{
		return $this->playerInfo;
	}

	/**
	 * @return string
	 */
	public function getIp() : string{
		return $this->ip;
	}

	/**
	 * @return int
	 */
	public function getPort() : int{
		return $this->port;
	}

	/**
	 * @return bool
	 */
	public function isAuthRequired() : bool{
		return $this->authRequired;
	}

	/**
	 * @param bool $v
	 */
	public function setAuthRequired(bool $v) : void{
		$this->authRequired = $v;
	}

	/**
	 * Returns an array of kick reasons currently assigned.
	 *
	 * @return int[]
	 */
	public function getKickReasons() : array{
		return array_keys($this->kickReasons);
	}

	/**
	 * Returns whether the given kick reason is set for this event.
	 *
	 * @param int $flag
	 *
	 * @return bool
	 */
	public function isKickReasonSet(int $flag) : bool{
		return isset($this->kickReasons[$flag]);
	}

	/**
	 * Sets a reason to disallow the player to continue continue authenticating, with a message.
	 * This can also be used to change kick messages for already-set flags.
	 *
	 * @param int    $flag
	 * @param string $message
	 */
	public function setKickReason(int $flag, string $message) : void{
		$this->kickReasons[$flag] = $message;
	}

	/**
	 * Clears a specific kick flag if it was set. This allows fine-tuned kick reason removal without impacting other
	 * reasons (for example, a ban can be bypassed without accidentally allowing a player to join a full server).
	 *
	 * @param int $flag Specific flag to clear.
	 */
	public function clearKickReason(int $flag) : void{
		unset($this->kickReasons[$flag]);
	}

	/**
	 * Clears all pre-assigned kick reasons, allowing the player to continue logging in.
	 */
	public function clearAllKickReasons() : void{
		$this->kickReasons = [];
	}

	/**
	 * Returns whether the player is allowed to continue logging in.
	 *
	 * @return bool
	 */
	public function isAllowed() : bool{
		return empty($this->kickReasons);
	}

	/**
	 * Returns the kick message provided for the given kick flag, or null if not set.
	 *
	 * @param int $flag
	 *
	 * @return string|null
	 */
	public function getKickMessage(int $flag) : ?string{
		return $this->kickReasons[$flag] ?? null;
	}

	/**
	 * Returns the final kick message which will be shown on the disconnect screen.
	 *
	 * Note: Only one message (the highest priority one) will be shown. See priority order to decide how to set your
	 * messages.
	 *
	 * @see PlayerPreLoginEvent::KICK_REASON_PRIORITY
	 *
	 * @return string
	 */
	public function getFinalKickMessage() : string{
		foreach(self::KICK_REASON_PRIORITY as $p){
			if(isset($this->kickReasons[$p])){
				return $this->kickReasons[$p];
			}
		}

		return "";
	}
}
