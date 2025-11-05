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
use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerBucketEmptyEvent;

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
		// Determine target block and result block. Allow placing water+mob on waterloggable
		// or replaceable positions (so bucket can be used even when there isn't an existing water source).
		$targetBlock = null;
		$resultBlock = null;
		$waterBlock = VanillaBlocks::WATER();

		if ($blockClicked instanceof Waterloggable) {
			$targetBlock = $blockClicked;
			$resultBlock = (clone $targetBlock)->setContainedWater(clone $waterBlock);
		} elseif ($blockReplace instanceof Waterloggable) {
			$targetBlock = $blockReplace;
			$resultBlock = (clone $targetBlock)->setContainedWater(clone $waterBlock);
		} elseif ($blockReplace->canBeReplaced()) {
			// place water in the replace position
			$targetBlock = $blockReplace;
			$resultBlock = clone $waterBlock;
		} elseif ($blockClicked instanceof Liquid && $blockClicked->isSource()) {
			// allow emptying into an existing liquid source (remove it)
			$targetBlock = $blockClicked;
			$resultBlock = VanillaBlocks::AIR();
		} elseif ($blockReplace instanceof Liquid && $blockReplace->isSource()) {
			$targetBlock = $blockReplace;
			$resultBlock = VanillaBlocks::AIR();
		} else {
			return ItemUseResult::NONE;
		}

		// Create the entity based on the item type
		$world = $player->getWorld();
		$pos = $targetBlock->getPosition()->add(0.5, 0, 0.5);
		$entity = null;
		switch ($this->getTypeId()) {
			case ItemTypeIds::AXOLOTL_BUCKET:
				$entity = new Axolotl(Location::fromObject($pos, $world, Utils::getRandomFloat() * 360, 0));
				// set variant from bucket NBT if present, otherwise random
				$variant = $this->getNamedTag()->getInt("Variant", mt_rand(0, 4));
				$entity->setVariant($variant);
				// set baby/adult state from bucket NBT if present
				$isBaby = $this->getNamedTag()->getByte("Baby", 0) === 1;
				$entity->setBaby($isBaby);
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

		// If spawned from a bucket, give the axolotl a short grace period of air
		// so it does not immediately suffocate when placed onto land by the player.
		// This overrides the axolotl's initEntity land-air behavior.
		$entity->setAirSupplyTicks($entity->getMaxAirSupplyTicks());

		// Fire bucket-empty event so plugins can cancel
		$ev = new PlayerBucketEmptyEvent($player, $targetBlock, $face, $this, VanillaItems::BUCKET());
		$ev->call();
		if ($ev->isCancelled()) {
			return ItemUseResult::FAIL;
		}

		// Apply world change (remove the water source)
		$player->getWorld()->setBlock($targetBlock->getPosition(), $resultBlock);

		// Spawn entity, play sound, and replace item with empty bucket
		$entity->spawnToAll();
		$player->getWorld()->addSound($pos->add(0, 0.5, 0), new BucketEmptyWaterSound());

		$this->pop();
		$returnedItems[] = $ev->getItem();

		return ItemUseResult::SUCCESS;
	}
}
