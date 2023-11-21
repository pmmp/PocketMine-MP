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
use pocketmine\lang\Translatable;
use pocketmine\player\PlayerInfo;
use function array_keys;
use function count;

/**
 * Called when a player connects to the server, prior to authentication taking place.
 * Set a kick reason to cancel the event and disconnect the player with the kick message set.
 *
 * This event should be used to decide if the player may continue to login to the server. Do things like checking
 * bans, whitelisting, server-full etc here.
 *
 * WARNING: Any information about the player CANNOT be trusted at this stage, because they are not authenticated and
 * could be a hacker posing as another player.
 */
class PlayerPreLoginEvent extends Event{
	public const KICK_FLAG_PLUGIN = 0;
	public const KICK_FLAG_SERVER_FULL = 1;
	public const KICK_FLAG_SERVER_WHITELISTED = 2;
	public const KICK_FLAG_BANNED = 3;

	public const KICK_FLAG_PRIORITY = [
		self::KICK_FLAG_PLUGIN, //Plugin reason should always take priority over anything else
		self::KICK_FLAG_SERVER_FULL,
		self::KICK_FLAG_SERVER_WHITELISTED,
		self::KICK_FLAG_BANNED
	];

	/** @var Translatable[]|string[] reason const => associated message */
	protected array $disconnectReasons = [];
	/** @var Translatable[]|string[] */
	protected array $disconnectScreenMessages = [];

	public function __construct(
		private PlayerInfo $playerInfo,
		private string $ip,
		private int $port,
		protected bool $authRequired
	){}

	/**
	 * Returns an object containing self-proclaimed information about the connecting player.
	 * WARNING: THE PLAYER IS NOT VERIFIED DURING THIS EVENT. At this point, this could be a hacker posing as another
	 * player.
	 */
	public function getPlayerInfo() : PlayerInfo{
		return $this->playerInfo;
	}

	public function getIp() : string{
		return $this->ip;
	}

	public function getPort() : int{
		return $this->port;
	}

	public function isAuthRequired() : bool{
		return $this->authRequired;
	}

	public function setAuthRequired(bool $v) : void{
		$this->authRequired = $v;
	}

	/**
	 * Returns an array of kick flags currently assigned.
	 *
	 * @return int[]
	 */
	public function getKickFlags() : array{
		return array_keys($this->disconnectReasons);
	}

	/**
	 * Returns whether the given kick flag is set for this event.
	 */
	public function isKickFlagSet(int $flag) : bool{
		return isset($this->disconnectReasons[$flag]);
	}

	/**
	 * Sets a reason to disallow the player to continue authenticating, with a message.
	 * This can also be used to change kick messages for already-set flags.
	 *
	 * @param Translatable|string      $disconnectReason        Shown in the server log - this should be a short one-line message
	 * @param Translatable|string|null $disconnectScreenMessage Shown on the player's disconnection screen (null will use the reason)
	 */
	public function setKickFlag(int $flag, Translatable|string $disconnectReason, Translatable|string|null $disconnectScreenMessage = null) : void{
		$this->disconnectReasons[$flag] = $disconnectReason;
		$this->disconnectScreenMessages[$flag] = $disconnectScreenMessage ?? $disconnectReason;
	}

	/**
	 * Clears a specific kick flag if it was set. This allows fine-tuned kick reason removal without impacting other
	 * reasons (for example, a ban can be bypassed without accidentally allowing a player to join a full server).
	 *
	 * @param int $flag Specific flag to clear.
	 */
	public function clearKickFlag(int $flag) : void{
		unset($this->disconnectReasons[$flag], $this->disconnectScreenMessages[$flag]);
	}

	/**
	 * Clears all pre-assigned kick reasons, allowing the player to continue logging in.
	 */
	public function clearAllKickFlags() : void{
		$this->disconnectReasons = [];
		$this->disconnectScreenMessages = [];
	}

	/**
	 * Returns whether the player is allowed to continue logging in.
	 */
	public function isAllowed() : bool{
		return count($this->disconnectReasons) === 0;
	}

	/**
	 * Returns the disconnect reason provided for the given kick flag, or null if not set.
	 * This is the message which will be shown in the server log and on the console.
	 */
	public function getDisconnectReason(int $flag) : Translatable|string|null{
		return $this->disconnectReasons[$flag] ?? null;
	}

	/**
	 * Returns the disconnect screen message provided for the given kick flag, or null if not set.
	 * This is the message shown to the player on the disconnect screen.
	 */
	public function getDisconnectScreenMessage(int $flag) : Translatable|string|null{
		return $this->disconnectScreenMessages[$flag] ?? null;
	}

	/**
	 * Resolves the message that will be shown in the server log if the player is kicked.
	 * Only one message (the highest priority one) will be shown. See priority order to decide how to set your
	 * messages.
	 *
	 * @see PlayerPreLoginEvent::KICK_FLAG_PRIORITY
	 */
	public function getFinalDisconnectReason() : Translatable|string{
		foreach(self::KICK_FLAG_PRIORITY as $p){
			if(isset($this->disconnectReasons[$p])){
				return $this->disconnectReasons[$p];
			}
		}

		return "";
	}

	/**
	 * Resolves the message that will be shown on the player's disconnect screen if they are kicked.
	 * Only one message (the highest priority one) will be shown. See priority order to decide how to set your
	 * messages.
	 *
	 * @see PlayerPreLoginEvent::KICK_FLAG_PRIORITY
	 */
	public function getFinalDisconnectScreenMessage() : Translatable|string{
		foreach(self::KICK_FLAG_PRIORITY as $p){
			if(isset($this->disconnectScreenMessages[$p])){
				return $this->disconnectScreenMessages[$p];
			}
		}

		return "";
	}
}
