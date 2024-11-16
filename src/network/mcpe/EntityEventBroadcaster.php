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

namespace pocketmine\network\mcpe;

use pocketmine\entity\Attribute;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\network\mcpe\protocol\types\entity\MetadataProperty;

/**
 * This class allows broadcasting entity events to many viewers on the server network.
 */
interface EntityEventBroadcaster{

	/**
	 * @param NetworkSession[] $recipients
	 * @param Attribute[]      $attributes
	 */
	public function syncAttributes(array $recipients, Living $entity, array $attributes) : void;

	/**
	 * @param NetworkSession[]   $recipients
	 * @param MetadataProperty[] $properties
	 *
	 * @phpstan-param array<int, MetadataProperty> $properties
	 */
	public function syncActorData(array $recipients, Entity $entity, array $properties) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onEntityEffectAdded(array $recipients, Living $entity, EffectInstance $effect, bool $replacesOldEffect) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onEntityEffectRemoved(array $recipients, Living $entity, EffectInstance $effect) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onEntityRemoved(array $recipients, Entity $entity) : void;

	/**
	 * TODO: expand this to more than just humans
	 *
	 * @param NetworkSession[] $recipients
	 */
	public function onMobMainHandItemChange(array $recipients,Human $mob) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onMobOffHandItemChange(array $recipients, Human $mob) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onMobArmorChange(array $recipients, Living $mob) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onPickUpItem(array $recipients, Entity $collector, Entity $pickedUp) : void;

	/**
	 * @param NetworkSession[] $recipients
	 */
	public function onEmote(array $recipients, Human $from, string $emoteId) : void;
}
