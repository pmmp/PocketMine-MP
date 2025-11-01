<?php

declare(strict_types=1);

namespace pocketmine\block\inventory;

use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\item\Item;
use pocketmine\world\Position;
use pocketmine\block\utils\AnvilHelper;
use pocketmine\item\VanillaItems;

class AnvilInventory extends SimpleInventory implements BlockInventory, TemporaryInventory
{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;
	public const SLOT_MATERIAL = 1;
	public const SLOT_RESULT = 2;

	private ?string $newItemName = null;

	public function __construct(Position $holder)
	{
		$this->holder = $holder;
		// Anvil has three slots: input, material and result
		parent::__construct(3);

		// Result slot should be read-only, calculated by AnvilTransaction
		// No auto-update listener needed - client will request CraftRecipeOptional action
	}

	public function setNewItemName(?string $name): void
	{
		$this->newItemName = $name;
		// Don't auto-update result slot - let CraftRecipeOptional action handle it
	}

	public function getNewItemName(): ?string
	{
		return $this->newItemName;
	}

	public function getInput(): Item
	{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getMaterial(): Item
	{
		return $this->getItem(self::SLOT_MATERIAL);
	}

	public function getResult(): Item
	{
		return $this->getItem(self::SLOT_RESULT);
	}
}
