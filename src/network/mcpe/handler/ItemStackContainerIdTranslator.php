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

namespace pocketmine\network\mcpe\handler;

use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerUIIds;
use pocketmine\network\PacketHandlingException;

final class ItemStackContainerIdTranslator{

	private function __construct(){
		//NOOP
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int}
	 * @throws PacketHandlingException
	 */
	public static function translate(int $containerInterfaceId, int $currentWindowId, int $slotId) : array{
		return match($containerInterfaceId){
			ContainerUIIds::ARMOR => [ContainerIds::ARMOR, $slotId],

			ContainerUIIds::HOTBAR,
			ContainerUIIds::INVENTORY,
			ContainerUIIds::COMBINED_HOTBAR_AND_INVENTORY => [ContainerIds::INVENTORY, $slotId],

			//TODO: HACK! The client sends an incorrect slot ID for the offhand as of 1.19.70 (though this doesn't really matter since the offhand has only 1 slot anyway)
			ContainerUIIds::OFFHAND => [ContainerIds::OFFHAND, 0],

			ContainerUIIds::ANVIL_INPUT,
			ContainerUIIds::ANVIL_MATERIAL,
			ContainerUIIds::BEACON_PAYMENT,
			ContainerUIIds::CARTOGRAPHY_ADDITIONAL,
			ContainerUIIds::CARTOGRAPHY_INPUT,
			ContainerUIIds::COMPOUND_CREATOR_INPUT,
			ContainerUIIds::CRAFTING_INPUT,
			ContainerUIIds::CREATED_OUTPUT,
			ContainerUIIds::CURSOR,
			ContainerUIIds::ENCHANTING_INPUT,
			ContainerUIIds::ENCHANTING_MATERIAL,
			ContainerUIIds::GRINDSTONE_ADDITIONAL,
			ContainerUIIds::GRINDSTONE_INPUT,
			ContainerUIIds::LAB_TABLE_INPUT,
			ContainerUIIds::LOOM_DYE,
			ContainerUIIds::LOOM_INPUT,
			ContainerUIIds::LOOM_MATERIAL,
			ContainerUIIds::MATERIAL_REDUCER_INPUT,
			ContainerUIIds::MATERIAL_REDUCER_OUTPUT,
			ContainerUIIds::SMITHING_TABLE_INPUT,
			ContainerUIIds::SMITHING_TABLE_MATERIAL,
			ContainerUIIds::SMITHING_TABLE_TEMPLATE,
			ContainerUIIds::STONECUTTER_INPUT,
			ContainerUIIds::TRADE2_INGREDIENT1,
			ContainerUIIds::TRADE2_INGREDIENT2,
			ContainerUIIds::TRADE_INGREDIENT1,
			ContainerUIIds::TRADE_INGREDIENT2 => [ContainerIds::UI, $slotId],

			ContainerUIIds::BARREL,
			ContainerUIIds::BLAST_FURNACE_INGREDIENT,
			ContainerUIIds::BREWING_STAND_FUEL,
			ContainerUIIds::BREWING_STAND_INPUT,
			ContainerUIIds::BREWING_STAND_RESULT,
			ContainerUIIds::FURNACE_FUEL,
			ContainerUIIds::FURNACE_INGREDIENT,
			ContainerUIIds::FURNACE_RESULT,
			ContainerUIIds::HORSE_EQUIP,
			ContainerUIIds::LEVEL_ENTITY, //chest
			ContainerUIIds::SHULKER_BOX,
			ContainerUIIds::SMOKER_INGREDIENT => [$currentWindowId, $slotId],

			//all preview slots are ignored, since the client shouldn't be modifying those directly

			default => throw new PacketHandlingException("Unexpected container UI ID $containerInterfaceId")
		};
	}
}
