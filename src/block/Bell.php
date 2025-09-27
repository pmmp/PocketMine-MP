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

namespace pocketmine\block;

use pocketmine\block\tile\Bell as TileBell;
use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\HorizontalFacingOption;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\BellRingSound;

final class Bell extends Transparent implements HorizontalFacing{
	use HorizontalFacingTrait;

	private BellAttachmentType $attachmentType = BellAttachmentType::FLOOR;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->enum($this->attachmentType);
		$w->enum($this->facing);
	}

	protected function recalculateCollisionBoxes() : array{
		$realFacing = $this->facing->toFacing();
		if($this->attachmentType === BellAttachmentType::FLOOR){
			return [
				AxisAlignedBB::one()->squashedCopy(Facing::axis($realFacing), 1 / 4)->trimmedCopy(Facing::UP, 3 / 16)
			];
		}
		if($this->attachmentType === BellAttachmentType::CEILING){
			return [
				AxisAlignedBB::one()->contractedCopy(1 / 4, 0, 1 / 4)->trimmedCopy(Facing::DOWN, 1 / 4)
			];
		}

		$box = AxisAlignedBB::one()
			->squashedCopy(Facing::axis(Facing::rotateY($realFacing, true)), 1 / 4)
			->trimmedCopy(Facing::UP, 1 / 16)
			->trimmedCopy(Facing::DOWN, 1 / 4);

		return [
			$this->attachmentType === BellAttachmentType::ONE_WALL ? $box->trimmedCopy($realFacing, 3 / 16) : $box
		];
	}

	public function getSupportType(Facing $facing) : SupportType{
		return SupportType::NONE;
	}

	public function getAttachmentType() : BellAttachmentType{ return $this->attachmentType; }

	/** @return $this */
	public function setAttachmentType(BellAttachmentType $attachmentType) : self{
		$this->attachmentType = $attachmentType;
		return $this;
	}

	private function canBeSupportedAt(Block $block, Facing $face) : bool{
		return $block->getAdjacentSupportType($face) !== SupportType::NONE;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, Facing $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedAt($blockReplace, Facing::opposite($face))){
			return false;
		}
		if($face === Facing::UP){
			if($player !== null){
				$this->setFacing(HorizontalFacingOption::fromFacing(Facing::opposite($player->getHorizontalFacing())));
			}
			$this->setAttachmentType(BellAttachmentType::FLOOR);
		}elseif($face === Facing::DOWN){
			$this->setAttachmentType(BellAttachmentType::CEILING);
		}else{
			$this->setFacing(HorizontalFacingOption::fromFacing($face));
			$this->setAttachmentType(
				$this->canBeSupportedAt($blockReplace, $face) ?
					BellAttachmentType::TWO_WALLS :
					BellAttachmentType::ONE_WALL
			);
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		foreach(match($this->attachmentType){
			BellAttachmentType::CEILING => [Facing::UP],
			BellAttachmentType::FLOOR => [Facing::DOWN],
			BellAttachmentType::ONE_WALL => [Facing::opposite($this->facing->toFacing())],
			BellAttachmentType::TWO_WALLS => [$this->facing->toFacing(), Facing::opposite($this->facing->toFacing())]
		} as $supportBlockDirection){
			if(!$this->canBeSupportedAt($this, $supportBlockDirection)){
				$this->position->getWorld()->useBreakOn($this->position);
				break;
			}
		}
	}

	public function onInteract(Item $item, Facing $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player !== null){
			$faceHit = Facing::opposite($player->getHorizontalFacing());
			if($this->isValidFaceToRing($faceHit)){
				$this->ring($faceHit);
				return true;
			}
		}

		return false;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		$faceHit = Facing::opposite($projectile->getHorizontalFacing());
		if($this->isValidFaceToRing($faceHit)){
			$this->ring($faceHit);
		}
	}

	public function ring(Facing $faceHit) : void{
		$world = $this->position->getWorld();
		$world->addSound($this->position, new BellRingSound());
		$tile = $world->getTile($this->position);
		if($tile instanceof TileBell){
			$world->broadcastPacketToViewers($this->position, $tile->createFakeUpdatePacket($faceHit));
		}
	}

	public function getDropsForIncompatibleTool(Item $item) : array{
		return [$this->asItem()];
	}

	private function isValidFaceToRing(Facing $faceHit) : bool{
		return match($this->attachmentType){
			BellAttachmentType::CEILING => true,
			BellAttachmentType::FLOOR => Facing::axis($faceHit) === Facing::axis($this->facing->toFacing()),
			BellAttachmentType::ONE_WALL, BellAttachmentType::TWO_WALLS => $faceHit === Facing::rotateY($this->facing->toFacing(), false) || $faceHit === Facing::rotateY($this->facing->toFacing(), true),
		};
	}
}
