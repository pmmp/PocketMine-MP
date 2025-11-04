<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\item\ItemIdentifier as IID;
use pocketmine\player\Player;
use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\block\utils\Waterloggable;
use pocketmine\math\Vector3;
use pocketmine\entity\Axolotl;
use pocketmine\world\sound\BucketEmptyWaterSound;
use pocketmine\entity\Location;
use pocketmine\item\ItemUseResult;
use pocketmine\world\World;
use pocketmine\utils\Utils;

/**
 * Generic mob bucket placeholder supporting the various fish/axolotl buckets.
 */
final class MobBucketItem extends Item{
	public function __construct(IID $identifier, string $name){
		parent::__construct($identifier, $name);
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
	{
		// Only allow placing mobs in water source blocks
		$liquid = null;
		if ($blockClicked instanceof Waterloggable && ($water = $blockClicked->getContainedWater()) !== null && $water->isSource()) {
			$liquid = $water;
		} elseif ($blockClicked instanceof Liquid && $blockClicked->isSource()) {
			$liquid = $blockClicked;
		}

		if ($liquid === null) {
			return ItemUseResult::NONE;
		}

		$world = $player->getWorld();
		$pos = $blockReplace->getPosition()->add(0.5, 0, 0.5);
		$entity = null;
		switch ($this->getTypeId()) {
			case ItemTypeIds::AXOLOTL_BUCKET:
				$entity = new Axolotl(Location::fromObject($pos, $world, Utils::getRandomFloat() * 360, 0));
				break;
			default:
				return ItemUseResult::NONE;
		}

		if ($entity === null) {
			return ItemUseResult::FAIL;
		}

		if ($this->hasCustomName()) {
			$entity->setNameTag($this->getCustomName());
		}

		$this->pop();
		$returnedItems[] = VanillaItems::BUCKET();
		$entity->spawnToAll();
		$world->addSound($pos->add(0, 0.5, 0), new BucketEmptyWaterSound());

		return ItemUseResult::SUCCESS;
	}
}
