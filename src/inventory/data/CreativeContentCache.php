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

namespace pocketmine\inventory\data;

use pocketmine\inventory\CreativeCategory;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeGroupEntry;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeItemEntry;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use function array_reduce;

final class CreativeContentCache{

	/**
	 * @param list<CreativeGroup>     $groupEntries
	 * @param list<CreativeItemEntry> $itemEntries
	 */
	public function __construct(
		private readonly array $groupEntries,
		private readonly array $itemEntries,
	){
		//NOOP
	}

	public function getPacket(NetworkSession $session) : CreativeContentPacket{
		return CreativeContentPacket::create(array_reduce(
			$this->groupEntries,
			function(array $carry, CreativeGroup $group) use ($session) : array{
				$categoryId = match ($group->categoryId) {
					CreativeCategory::CONSTRUCTION => CreativeContentPacket::CATEGORY_CONSTRUCTION,
					CreativeCategory::NATURE => CreativeContentPacket::CATEGORY_NATURE,
					CreativeCategory::EQUIPMENT => CreativeContentPacket::CATEGORY_EQUIPMENT,
					CreativeCategory::ITEMS => CreativeContentPacket::CATEGORY_ITEMS
				};

				if($group->name instanceof Translatable){
					$player = $session->getPlayer();

					if($player === null){
						throw new \LogicException("Cannot send creative content for a player that is not yet created");
					}

					if(!$player->getServer()->isLanguageForced()){
						[$message, ] = $player->getNetworkSession()->prepareClientTranslatableMessage($group->name);
					}else{
						$message = $player->getLanguage()->translate($group->name);
					}
				}else{
					$message = $group->name;
				}

				$carry[] = new CreativeGroupEntry(
					$categoryId,
					$message,
					$group->icon === null ? ItemStack::null() : $session->getTypeConverter()->coreItemStackToNet($group->icon)
				);
				return $carry;
			},
			[]
		), $this->itemEntries);
	}
}
