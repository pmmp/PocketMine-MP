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

namespace pocketmine;

use pocketmine\utils\ObjectSet;
use function array_filter;
use function spl_object_id;

final class MessageChannel{
	/**
	 * All subscribers, regardless of whether they have permission to receive messages or not
	 *
	 * @var MessageChannelSubscriber[]|ObjectSet
	 * @phpstan-var ObjectSet<MessageChannelSubscriber>
	 */
	private ObjectSet $nonPermittedSubscribers;

	/**
	 * Only subscribers whose permissions have been checked. This is invalidated whenever a subscriber's permissions are
	 * updated.
	 *
	 * @var MessageChannelSubscriber[]|ObjectSet
	 * @phpstan-var ObjectSet<MessageChannelSubscriber>
	 */
	private ObjectSet $permittedSubscribers;

	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure() : void>
	 */
	private array $permissionCallbacks = [];

	public function __construct(
		private ?string $permission
	){
		$this->nonPermittedSubscribers = new ObjectSet();
		$this->permittedSubscribers = new ObjectSet();
	}

	public function getPermission() : ?string{ return $this->permission; }

	public function setPermission(?string $permission) : void{
		$this->permission = $permission;
		foreach($this->permittedSubscribers as $subscriber){
			$this->updateSubscriberAccess($subscriber);
		}
		foreach($this->nonPermittedSubscribers as $subscriber){
			$this->updateSubscriberAccess($subscriber);
		}
	}

	private function updateSubscriberAccess(MessageChannelSubscriber $subscriber) : void{
		$permissible = $subscriber->getPermissible();

		//If either the subscriber doesn't support permission restricting, or no permission is set, we send all messages
		//to it. Mainly useful for programmatic message collectors like in plugins.
		$permitted = $permissible === null || $this->permission === null || $permissible->hasPermission($this->permission);

		[$add, $remove] = $permitted ?
			[$this->permittedSubscribers, $this->nonPermittedSubscribers] :
			[$this->nonPermittedSubscribers, $this->permittedSubscribers];
		$add->add($subscriber);
		$remove->remove($subscriber);
	}

	/**
	 * Subscribes to a particular message broadcast channel.
	 * The channel ID can be any arbitrary string.
	 */
	public function subscribe(MessageChannelSubscriber $subscriber) : void{
		$this->updateSubscriberAccess($subscriber);

		$permCallback = $this->permissionCallbacks[spl_object_id($subscriber)] = fn() => $this->updateSubscriberAccess($subscriber);
		$subscriber->getPermissible()?->getPermissionRecalculationCallbacks()->add($permCallback);
	}

	/**
	 * Unsubscribes from a particular message broadcast channel.
	 */
	public function unsubscribe(MessageChannelSubscriber $subscriber) : void{
		$this->nonPermittedSubscribers->remove($subscriber);
		$this->permittedSubscribers->remove($subscriber);

		$splObjectId = spl_object_id($subscriber);
		$permCallback = $this->permissionCallbacks[$splObjectId] ?? null;
		if($permCallback !== null){
			$subscriber->getPermissible()?->getPermissionRecalculationCallbacks()->remove($permCallback);
			unset($this->permissionCallbacks[$splObjectId]);
		}
	}

	/**
	 * Returns a list of all subscribers with permission to receive messages from this channel.
	 *
	 * @return MessageChannelSubscriber[]
	 * @phpstan-return array<int, MessageChannelSubscriber>
	 */
	public function getPermittedSubscribers() : array{
		//convert to an array so callers can't mess up the internal state of the channel
		return $this->permittedSubscribers->toArray();
	}

	/**
	 * Returns a list of subscribers who don't have permission to receive messages from this channel.
	 * If their permissions are updated, they'll automatically start receiving new messages.
	 *
	 * @return MessageChannelSubscriber[]
	 * @phpstan-return array<int, MessageChannelSubscriber>
	 */
	public function getNonPermittedSubscribers() : array{
		return $this->nonPermittedSubscribers->toArray();
	}

	/**
	 * Returns a list of all broadcast subscribers with permission to receive messages from this channel, and who can
	 * receive non-standard message types such as tips, popups and titles.
	 *
	 * @return MinecraftMessageChannelSubscriber[]
	 * @phpstan-return array<int, MinecraftMessageChannelSubscriber>
	 */
	public function getMinecraftPermittedSubscribers() : array{
		return array_filter($this->permittedSubscribers->toArray(), fn(MessageChannelSubscriber $s) => $s instanceof MinecraftMessageChannelSubscriber);
	}
}
