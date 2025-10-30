<?php

declare(strict_types=1);

namespace pocketmine\utils;

class EquipmentSlot{

	/* For MobEquipmentPacket */
	public const MAINHAND = 0;
	public const OFFHAND = 1;

	/** For MobArmorEquipmentPacket */
	public const HEAD = 0;
	public const CHEST = 1;
	public const LEGS = 2;
	public const FEET = 3;
}