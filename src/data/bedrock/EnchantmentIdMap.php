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

namespace pocketmine\data\bedrock;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;

/**
 * Handles translation of internal enchantment types to and from Minecraft: Bedrock IDs.
 */
final class EnchantmentIdMap{
	use SingletonTrait;

	/**
	 * @var Enchantment[]
	 * @phpstan-var array<int, Enchantment>
	 */
	private $idToEnch = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private $enchToId = [];

	private function __construct(){
		$this->register(EnchantmentIds::PROTECTION, VanillaEnchantments::PROTECTION());
		$this->register(EnchantmentIds::FIRE_PROTECTION, VanillaEnchantments::FIRE_PROTECTION());
		$this->register(EnchantmentIds::FEATHER_FALLING, VanillaEnchantments::FEATHER_FALLING());
		$this->register(EnchantmentIds::BLAST_PROTECTION, VanillaEnchantments::BLAST_PROTECTION());
		$this->register(EnchantmentIds::PROJECTILE_PROTECTION, VanillaEnchantments::PROJECTILE_PROTECTION());
		$this->register(EnchantmentIds::THORNS, VanillaEnchantments::THORNS());
		$this->register(EnchantmentIds::RESPIRATION, VanillaEnchantments::RESPIRATION());

		$this->register(EnchantmentIds::SHARPNESS, VanillaEnchantments::SHARPNESS());
		//TODO: smite, bane of arthropods (these don't make sense now because their applicable mobs don't exist yet)

		$this->register(EnchantmentIds::KNOCKBACK, VanillaEnchantments::KNOCKBACK());
		$this->register(EnchantmentIds::FIRE_ASPECT, VanillaEnchantments::FIRE_ASPECT());

		$this->register(EnchantmentIds::EFFICIENCY, VanillaEnchantments::EFFICIENCY());
		$this->register(EnchantmentIds::SILK_TOUCH, VanillaEnchantments::SILK_TOUCH());
		$this->register(EnchantmentIds::UNBREAKING, VanillaEnchantments::UNBREAKING());

		$this->register(EnchantmentIds::POWER, VanillaEnchantments::POWER());
		$this->register(EnchantmentIds::PUNCH, VanillaEnchantments::PUNCH());
		$this->register(EnchantmentIds::FLAME, VanillaEnchantments::FLAME());
		$this->register(EnchantmentIds::INFINITY, VanillaEnchantments::INFINITY());

		$this->register(EnchantmentIds::MENDING, VanillaEnchantments::MENDING());

		$this->register(EnchantmentIds::VANISHING, VanillaEnchantments::VANISHING());
	}

	public function register(int $mcpeId, Enchantment $enchantment) : void{
		$this->idToEnch[$mcpeId] = $enchantment;
		$this->enchToId[$enchantment->getRuntimeId()] = $mcpeId;
	}

	public function fromId(int $id) : ?Enchantment{
		//we might not have all the enchantment IDs registered
		return $this->idToEnch[$id] ?? null;
	}

	public function toId(Enchantment $enchantment) : int{
		if(!array_key_exists($enchantment->getRuntimeId(), $this->enchToId)){
			//this should never happen, so we treat it as an exceptional condition
			throw new \InvalidArgumentException("Enchantment does not have a mapped ID");
		}
		return $this->enchToId[$enchantment->getRuntimeId()];
	}
}
