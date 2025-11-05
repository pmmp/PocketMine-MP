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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeTags;
use pocketmine\block\Lava;
use pocketmine\block\Liquid;
use pocketmine\block\utils\Waterloggable;
use pocketmine\block\Water;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\entity\Axolotl;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class LiquidBucket extends Item{
	private Liquid $liquid;

	public function __construct(ItemIdentifier $identifier, string $name, Liquid $liquid){
		parent::__construct($identifier, $name);
		$this->liquid = $liquid;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getFuelTime() : int{
		if($this->liquid instanceof Lava){
			return 20000;
		}

		return 0;
	}

	public function getFuelResidue() : Item{
		return VanillaItems::BUCKET();
	}

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
		//TODO: move this to generic placement logic

		$targetBlock = null;
		$resultBlock = null;

		$waterloggable = match(true){
			$blockClicked instanceof Waterloggable && $blockClicked->canBeWaterlogged() => $blockClicked,
			$blockReplace instanceof Waterloggable && $blockReplace->canBeWaterlogged() => $blockReplace,
			default => null
		};
		if($waterloggable !== null && ($this->liquid instanceof Water || $waterloggable->hasTypeTag(BlockTypeTags::NON_SOURCE_WATERLOGGABLE))){
			if($this->liquid instanceof Water){
				$targetBlock = $waterloggable;
				$resultBlock = (clone $targetBlock)->setContainedWater((clone $this->liquid)->setStill(false));
			}else{
				$targetBlock = $blockReplace->canBeReplaced() ? $blockReplace : (($target = $blockReplace->getSide($face))->canBeReplaced() ? $target : null);
				$resultBlock = clone $this->liquid;
			}
		}elseif($blockReplace->canBeReplaced()){
			$targetBlock = $blockReplace;
			$resultBlock = clone $this->liquid;
		}

		if($targetBlock === null || $resultBlock === null){
			return ItemUseResult::NONE;
		}

		$ev = new PlayerBucketEmptyEvent($player, $targetBlock, $face, $this, VanillaItems::BUCKET());
		$ev->call();
		if(!$ev->isCancelled()){
			$player->getWorld()->setBlock($targetBlock->getPosition(), $resultBlock);
			$player->getWorld()->addSound($targetBlock->getPosition()->add(0.5, 0.5, 0.5), $this->liquid->getBucketEmptySound());

			$this->pop();
			$returnedItems[] = $ev->getItem();
			return ItemUseResult::SUCCESS;
		}

		return ItemUseResult::FAIL;
	}

	public function getLiquid() : Liquid{
		return $this->liquid;
	}

	public function onInteractEntity(Player $player, Entity $entity, Vector3 $clickVector) : bool{
		// Allow using a water bucket on an axolotl to pick it up into a mob bucket
		if(!($this->liquid instanceof Water)){
			return false;
		}

		if(!($entity instanceof Axolotl)){
			return false;
		}

		// Prepare resulting mob-bucket item with variant NBT
		$result = clone VanillaItems::AXOLOTL_BUCKET();
		// write variant into bucket NBT
		$variant = $entity->getVariant();
		$result->getNamedTag()->setInt("Variant", $variant);

		// write baby/adult flag
		$result->getNamedTag()->setByte("Baby", $entity->isBaby() ? 1 : 0);

			// preserve custom name if present, otherwise set a translatable JSON name so clients can localize it
			if($entity->getNameTag() !== ""){
				$result->setCustomName($entity->getNameTag());
			}else{
				// Use a JSON text component with translation keys so the client can localize the generated name.
				// The translation key "pocketmine.item.axolotl_bucket" should be added to language files and
				// expects two parameters: age and variant.
				$variantKeys = [
					0 => "pocketmine.axolotl.variant.pink",
					1 => "pocketmine.axolotl.variant.brown",
					2 => "pocketmine.axolotl.variant.gold",
					3 => "pocketmine.axolotl.variant.cyan",
				];

				$ageKey = $entity->isBaby() ? "pocketmine.axolotl.age.baby" : "pocketmine.axolotl.age.adult";
				$variantKey = $variantKeys[$variant] ?? "pocketmine.axolotl.variant.pink";

				// Build a localized plain string server-side to avoid storing raw JSON in the item NBT
				// Translate age and variant keys using the player's language, then format the bucket name
				$lang = $player->getLanguage();
				$ageText = $lang->translateString($ageKey);
				$variantText = $lang->translateString($variantKey);
				$translated = $lang->translateString("pocketmine.item.axolotl_bucket", [$ageText, $variantText]);
				$result->setCustomName($translated);
			}

		// Fire fill event so plugins can cancel
		$blockClicked = $player->getWorld()->getBlock($entity->getPosition());
		$ev = new PlayerBucketFillEvent($player, $blockClicked, 0, $this, $result);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		// Play fill sound
		$player->getWorld()->addSound($entity->getPosition(), $this->liquid->getBucketFillSound());

		// Remove the entity from the world
		$entity->close();

		// Replace/insert the resulting item into the player's inventory
		$inv = $player->getInventory();
		if($player->hasFiniteResources()){
			// consume one water bucket from hand
			$held = $inv->getItemInHand();
			$held->pop();
			if($held->isNull()){
				$inv->setItemInHand($ev->getItem());
			}else{
				// add to inventory (or drop if full)
				foreach($inv->addItem($ev->getItem()) as $drop){
					$player->dropItem($drop);
				}
			}
		}else{
			// Creative: don't consume bucket, just give the result
			foreach($inv->addItem($ev->getItem()) as $drop){
				$player->dropItem($drop);
			}
		}

		return true;
	}
}