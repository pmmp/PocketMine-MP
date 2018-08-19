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

use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\CommandRequestPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\EntityFallPacket;
use pocketmine\network\mcpe\protocol\EntityPickRequestPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\protocol\LabTablePacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerHotbarPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\ShowCreditsPacket;
use pocketmine\network\mcpe\protocol\SpawnExperienceOrbPacket;
use pocketmine\network\mcpe\protocol\SubClientLoginPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\Player;

/**
 * Temporary session handler implementation
 * TODO: split this up properly into different handlers
 */
class SimpleSessionHandler extends SessionHandler{

	/** @var Player */
	private $player;

	/** @var CraftingTransaction|null */
	protected $craftingTransaction = null;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function handleText(TextPacket $packet) : bool{
		if($packet->type === TextPacket::TYPE_CHAT){
			return $this->player->chat($packet->message);
		}

		return false;
	}

	public function handleMovePlayer(MovePlayerPacket $packet) : bool{
		return $this->player->handleMovePlayer($packet);
	}

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool{
		return $this->player->handleLevelSoundEvent($packet);
	}

	public function handleEntityEvent(EntityEventPacket $packet) : bool{
		return $this->player->handleEntityEvent($packet);
	}

	public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool{
		if($this->player->isSpectator()){
			$this->player->sendAllInventories();
			return true;
		}

		/** @var InventoryAction[] $actions */
		$actions = [];
		foreach($packet->actions as $networkInventoryAction){
			try{
				$action = $networkInventoryAction->createInventoryAction($this->player);
				if($action !== null){
					$actions[] = $action;
				}
			}catch(\Exception $e){
				$this->player->getServer()->getLogger()->debug("Unhandled inventory action from " . $this->player->getName() . ": " . $e->getMessage());
				$this->player->sendAllInventories();
				return false;
			}
		}

		if($packet->isCraftingPart){
			if($this->craftingTransaction === null){
				$this->craftingTransaction = new CraftingTransaction($this->player, $actions);
			}else{
				foreach($actions as $action){
					$this->craftingTransaction->addAction($action);
				}
			}

			if($packet->isFinalCraftingPart){
				//we get the actions for this in several packets, so we need to wait until we have all the pieces before
				//trying to execute it

				$ret = true;
				try{
					$this->craftingTransaction->execute();
				}catch(TransactionValidationException $e){
					$this->player->getServer()->getLogger()->debug("Failed to execute crafting transaction for " . $this->player->getName() . ": " . $e->getMessage());
					$ret = false;
				}

				$this->craftingTransaction = null;
				return $ret;
			}

			return true;
		}
		if($this->craftingTransaction !== null){
			$this->player->getServer()->getLogger()->debug("Got unexpected normal inventory action with incomplete crafting transaction from " . $this->player->getName() . ", refusing to execute crafting");
			$this->craftingTransaction = null;
		}

		switch($packet->transactionType){
			case InventoryTransactionPacket::TYPE_NORMAL:
				$transaction = new InventoryTransaction($this->player, $actions);

				try{
					$transaction->execute();
				}catch(TransactionValidationException $e){
					$this->player->getServer()->getLogger()->debug("Failed to execute inventory transaction from " . $this->player->getName() . ": " . $e->getMessage());
					$this->player->getServer()->getLogger()->debug("Actions: " . json_encode($packet->actions));

					return false;
				}

				//TODO: fix achievement for getting iron from furnace

				return true;
			case InventoryTransactionPacket::TYPE_MISMATCH:
				if(count($packet->actions) > 0){
					$this->player->getServer()->getLogger()->debug("Expected 0 actions for mismatch, got " . count($packet->actions) . ", " . json_encode($packet->actions));
				}
				$this->player->sendAllInventories();

				return true;
			case InventoryTransactionPacket::TYPE_USE_ITEM:
				$blockVector = new Vector3($packet->trData->x, $packet->trData->y, $packet->trData->z);
				$face = $packet->trData->face;

				$type = $packet->trData->actionType;
				switch($type){
					case InventoryTransactionPacket::USE_ITEM_ACTION_CLICK_BLOCK:
						$this->player->interactBlock($blockVector, $face, $packet->trData->clickPos);
						return true;
					case InventoryTransactionPacket::USE_ITEM_ACTION_BREAK_BLOCK:
						$this->player->breakBlock($blockVector);
						return true;
					case InventoryTransactionPacket::USE_ITEM_ACTION_CLICK_AIR:
						$this->player->useHeldItem();
						return true;
				}
				break;
			case InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY:
				$target = $this->player->getLevel()->getEntity($packet->trData->entityRuntimeId);
				if($target === null){
					return false;
				}

				switch($packet->trData->actionType){
					case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT:
						$this->player->interactEntity($target, $packet->trData->clickPos);
						return true;
					case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_ATTACK:
						$this->player->attackEntity($target);
						return true;
				}

				break;
			case InventoryTransactionPacket::TYPE_RELEASE_ITEM:
				switch($packet->trData->actionType){
					case InventoryTransactionPacket::RELEASE_ITEM_ACTION_RELEASE:
						$this->player->releaseHeldItem();
						return true;
					case InventoryTransactionPacket::RELEASE_ITEM_ACTION_CONSUME:
						$this->player->consumeHeldItem();
						return true;
				}
				break;
			default:
				break;

		}

		$this->player->sendAllInventories();
		return false;
	}

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool{
		return $this->player->equipItem($packet->hotbarSlot);
	}

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool{
		return true; //Not used
	}

	public function handleInteract(InteractPacket $packet) : bool{
		return false; //TODO
	}

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool{
		return $this->player->pickBlock(new Vector3($packet->blockX, $packet->blockY, $packet->blockZ), $packet->addUserData);
	}

	public function handleEntityPickRequest(EntityPickRequestPacket $packet) : bool{
		return false; //TODO
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool{
		$pos = new Vector3($packet->x, $packet->y, $packet->z);

		switch($packet->action){
			case PlayerActionPacket::ACTION_START_BREAK:
				$this->player->startBreakBlock($pos, $packet->face);

				break;

			case PlayerActionPacket::ACTION_ABORT_BREAK:
			case PlayerActionPacket::ACTION_STOP_BREAK:
				$this->player->stopBreakBlock($pos);
				break;
			case PlayerActionPacket::ACTION_START_SLEEPING:
				//unused
				break;
			case PlayerActionPacket::ACTION_STOP_SLEEPING:
				$this->player->stopSleep();
				break;
			case PlayerActionPacket::ACTION_JUMP:
				$this->player->jump();
				return true;
			case PlayerActionPacket::ACTION_START_SPRINT:
				$this->player->toggleSprint(true);
				return true;
			case PlayerActionPacket::ACTION_STOP_SPRINT:
				$this->player->toggleSprint(false);
				return true;
			case PlayerActionPacket::ACTION_START_SNEAK:
				$this->player->toggleSneak(true);
				return true;
			case PlayerActionPacket::ACTION_STOP_SNEAK:
				$this->player->toggleSneak(false);
				return true;
			case PlayerActionPacket::ACTION_START_GLIDE:
			case PlayerActionPacket::ACTION_STOP_GLIDE:
				break; //TODO
			case PlayerActionPacket::ACTION_CONTINUE_BREAK:
				$this->player->continueBreakBlock($pos, $packet->face);
				break;
			case PlayerActionPacket::ACTION_START_SWIMMING:
				break; //TODO
			case PlayerActionPacket::ACTION_STOP_SWIMMING:
				//TODO: handle this when it doesn't spam every damn tick (yet another spam bug!!)
				break;
			default:
				$this->player->getServer()->getLogger()->debug("Unhandled/unknown player action type " . $packet->action . " from " . $this->player->getName());
				return false;
		}

		$this->player->setUsingItem(false);

		return true;
	}

	public function handleEntityFall(EntityFallPacket $packet) : bool{
		return true; //Not used
	}

	public function handleAnimate(AnimatePacket $packet) : bool{
		return $this->player->animate($packet->action);
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool{
		return $this->player->doCloseWindow($packet->windowId);
	}

	public function handlePlayerHotbar(PlayerHotbarPacket $packet) : bool{
		return true; //this packet is useless
	}

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool{
		return true; //this is a broken useless packet, so we don't use it
	}

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool{
		return $this->player->handleAdventureSettings($packet);
	}

	public function handleBlockEntityData(BlockEntityDataPacket $packet) : bool{
		return $this->player->handleBlockEntityData($packet);
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool{
		return false; //TODO
	}

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool{
		if($packet->gamemode !== $this->player->getGamemode()){
			//Set this back to default. TODO: handle this properly
			$this->player->sendGamemode();
			$this->player->sendSettings();
		}
		return true;
	}

	public function handleSpawnExperienceOrb(SpawnExperienceOrbPacket $packet) : bool{
		return false; //TODO
	}

	public function handleMapInfoRequest(MapInfoRequestPacket $packet) : bool{
		return false; //TODO
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool{
		$this->player->setViewDistance($packet->radius);

		return true;
	}

	public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool{
		return $this->player->handleItemFrameDropItem($packet);
	}

	public function handleBossEvent(BossEventPacket $packet) : bool{
		return false; //TODO
	}

	public function handleShowCredits(ShowCreditsPacket $packet) : bool{
		return false; //TODO: handle resume
	}

	public function handleCommandRequest(CommandRequestPacket $packet) : bool{
		return $this->player->chat($packet->command);
	}

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool{
		return false; //TODO
	}

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool{
		return $this->player->changeSkin($packet->skin, $packet->newSkinName, $packet->oldSkinName);
	}

	public function handleSubClientLogin(SubClientLoginPacket $packet) : bool{
		return false; //TODO
	}

	public function handleBookEdit(BookEditPacket $packet) : bool{
		return $this->player->handleBookEdit($packet);
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet) : bool{
		return $this->player->onFormSubmit($packet->formId, self::stupid_json_decode($packet->formData, true));
	}

	/**
	 * Hack to work around a stupid bug in Minecraft W10 which causes empty strings to be sent unquoted in form responses.
	 *
	 * @param string $json
	 * @param bool   $assoc
	 *
	 * @return mixed
	 */
	private static function stupid_json_decode(string $json, bool $assoc = false){
		if(preg_match('/^\[(.+)\]$/s', $json, $matches) > 0){
			$parts = preg_split('/(?:"(?:\\"|[^"])*"|)\K(,)/', $matches[1]); //Splits on commas not inside quotes, ignoring escaped quotes
			foreach($parts as $k => $part){
				$part = trim($part);
				if($part === ""){
					$part = "\"\"";
				}
				$parts[$k] = $part;
			}

			$fixed = "[" . implode(",", $parts) . "]";
			if(($ret = json_decode($fixed, $assoc)) === null){
				throw new \InvalidArgumentException("Failed to fix JSON: " . json_last_error_msg() . "(original: $json, modified: $fixed)");
			}

			return $ret;
		}

		return json_decode($json, $assoc);
	}

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $packet) : bool{
		return false; //TODO: GUI stuff
	}

	public function handleLabTable(LabTablePacket $packet) : bool{
		return false; //TODO
	}
}
