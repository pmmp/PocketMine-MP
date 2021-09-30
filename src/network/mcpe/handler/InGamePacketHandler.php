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

use pocketmine\block\BaseSign;
use pocketmine\block\ItemFrame;
use pocketmine\block\utils\SignText;
use pocketmine\crafting\CraftingGrid;
use pocketmine\entity\animation\ConsumingItemAnimation;
use pocketmine\entity\InvalidSkinException;
use pocketmine\event\player\PlayerEditBookEvent;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionException;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\VanillaItems;
use pocketmine\item\WritableBook;
use pocketmine\item\WrittenBook;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ActorPickRequestPacket;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\CommandRequestPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\protocol\LabTablePacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacketV1;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerHotbarPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\ShowCreditsPacket;
use pocketmine\network\mcpe\protocol\SpawnExperienceOrbPacket;
use pocketmine\network\mcpe\protocol\SubClientLoginPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\MismatchTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\inventory\NormalTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\ReleaseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use function array_key_exists;
use function array_push;
use function base64_encode;
use function count;
use function fmod;
use function implode;
use function is_infinite;
use function is_nan;
use function json_decode;
use function json_encode;
use function json_last_error_msg;
use function max;
use function microtime;
use function preg_match;
use function strlen;
use function strpos;
use function substr;
use function trim;

/**
 * This handler handles packets related to general gameplay.
 */
class InGamePacketHandler extends PacketHandler{

	/** @var Player */
	private $player;
	/** @var NetworkSession */
	private $session;

	/** @var CraftingTransaction|null */
	protected $craftingTransaction = null;

	/** @var float */
	protected $lastRightClickTime = 0.0;
	/** @var UseItemTransactionData|null */
	protected $lastRightClickData = null;

	/** @var bool */
	public $forceMoveSync = false;

	/**
	 * TODO: HACK! This tracks GUIs for inventories that the server considers "always open" so that the client can't
	 * open them twice. (1.16 hack)
	 * @var true[]
	 * @phpstan-var array<int, true>
	 * @internal
	 */
	protected $openHardcodedWindows = [];

	private InventoryManager $inventoryManager;

	public function __construct(Player $player, NetworkSession $session, InventoryManager $inventoryManager){
		$this->player = $player;
		$this->session = $session;
		$this->inventoryManager = $inventoryManager;
	}

	public function handleText(TextPacket $packet) : bool{
		if($packet->type === TextPacket::TYPE_CHAT){
			return $this->player->chat($packet->message);
		}

		return false;
	}

	public function handleMovePlayer(MovePlayerPacket $packet) : bool{
		$rawPos = $packet->position;
		foreach([$rawPos->x, $rawPos->y, $rawPos->z, $packet->yaw, $packet->headYaw, $packet->pitch] as $float){
			if(is_infinite($float) || is_nan($float)){
				$this->session->getLogger()->debug("Invalid movement received, contains NAN/INF components");
				return false;
			}
		}

		$yaw = fmod($packet->yaw, 360);
		$pitch = fmod($packet->pitch, 360);
		if($yaw < 0){
			$yaw += 360;
		}

		$this->player->setRotation($yaw, $pitch);

		$curPos = $this->player->getLocation();
		$newPos = $packet->position->round(4)->subtract(0, 1.62, 0);

		if($this->forceMoveSync and $newPos->distanceSquared($curPos) > 1){  //Tolerate up to 1 block to avoid problems with client-sided physics when spawning in blocks
			$this->session->getLogger()->debug("Got outdated pre-teleport movement, received " . $newPos . ", expected " . $curPos);
			//Still getting movements from before teleport, ignore them
			return false;
		}

		// Once we get a movement within a reasonable distance, treat it as a teleport ACK and remove position lock
		$this->forceMoveSync = false;

		$this->player->handleMovement($newPos);

		return true;
	}

	public function handleLevelSoundEventPacketV1(LevelSoundEventPacketV1 $packet) : bool{
		return true; //useless leftover from 1.8
	}

	public function handleActorEvent(ActorEventPacket $packet) : bool{
		if($packet->entityRuntimeId !== $this->player->getId()){
			//TODO HACK: EATING_ITEM is sent back to the server when the server sends it for other players (1.14 bug, maybe earlier)
			return $packet->event === ActorEventPacket::EATING_ITEM;
		}
		$this->player->doCloseInventory();

		switch($packet->event){
			case ActorEventPacket::EATING_ITEM: //TODO: ignore this and handle it server-side
				$item = $this->player->getInventory()->getItemInHand();
				if($item->isNull()){
					return false;
				}
				$this->player->broadcastAnimation(new ConsumingItemAnimation($this->player, $this->player->getInventory()->getItemInHand()));
				break;
			default:
				return false;
		}

		return true;
	}

	public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool{
		$result = true;

		if($packet->trData instanceof NormalTransactionData){
			$result = $this->handleNormalTransaction($packet->trData);
		}elseif($packet->trData instanceof MismatchTransactionData){
			$this->session->getLogger()->debug("Mismatch transaction received");
			$this->inventoryManager->syncAll();
			$result = true;
		}elseif($packet->trData instanceof UseItemTransactionData){
			$result = $this->handleUseItemTransaction($packet->trData);
		}elseif($packet->trData instanceof UseItemOnEntityTransactionData){
			$result = $this->handleUseItemOnEntityTransaction($packet->trData);
		}elseif($packet->trData instanceof ReleaseItemTransactionData){
			$result = $this->handleReleaseItemTransaction($packet->trData);
		}

		if(!$result){
			$this->inventoryManager->syncAll();
		}
		return $result;
	}

	private function handleNormalTransaction(NormalTransactionData $data) : bool{
		/** @var InventoryAction[] $actions */
		$actions = [];

		$isCraftingPart = false;
		$converter = TypeConverter::getInstance();
		foreach($data->getActions() as $networkInventoryAction){
			if(
				(
					$networkInventoryAction->sourceType === NetworkInventoryAction::SOURCE_TODO and (
						$networkInventoryAction->windowId === NetworkInventoryAction::SOURCE_TYPE_CRAFTING_RESULT or
						$networkInventoryAction->windowId === NetworkInventoryAction::SOURCE_TYPE_CRAFTING_USE_INGREDIENT
					)
				) or (
					$this->craftingTransaction !== null &&
					!$networkInventoryAction->oldItem->getItemStack()->equals($networkInventoryAction->newItem->getItemStack()) &&
					$networkInventoryAction->sourceType === NetworkInventoryAction::SOURCE_CONTAINER &&
					$networkInventoryAction->windowId === ContainerIds::UI &&
					$networkInventoryAction->inventorySlot === UIInventorySlotOffset::CREATED_ITEM_OUTPUT
				)
			){
				$isCraftingPart = true;
			}

			try{
				$action = $converter->createInventoryAction($networkInventoryAction, $this->player, $this->inventoryManager);
				if($action !== null){
					$actions[] = $action;
				}
			}catch(\UnexpectedValueException $e){
				$this->session->getLogger()->debug("Unhandled inventory action: " . $e->getMessage());
				return false;
			}
		}

		if($isCraftingPart){
			if($this->craftingTransaction === null){
				//TODO: this might not be crafting if there is a special inventory open (anvil, enchanting, loom etc)
				$this->craftingTransaction = new CraftingTransaction($this->player, $this->player->getServer()->getCraftingManager(), $actions);
			}else{
				foreach($actions as $action){
					$this->craftingTransaction->addAction($action);
				}
			}

			try{
				$this->craftingTransaction->validate();
			}catch(TransactionValidationException $e){
				//transaction is incomplete - crafting transaction comes in lots of little bits, so we have to collect
				//all of the parts before we can execute it
				return true;
			}
			try{
				$this->inventoryManager->onTransactionStart($this->craftingTransaction);
				$this->craftingTransaction->execute();
			}catch(TransactionException $e){
				$this->session->getLogger()->debug("Failed to execute crafting transaction: " . $e->getMessage());

				//TODO: only sync slots that the client tried to change
				foreach($this->craftingTransaction->getInventories() as $inventory){
					$this->inventoryManager->syncContents($inventory);
				}
				/*
				 * TODO: HACK!
				 * we can't resend the contents of the crafting window, so we force the client to close it instead.
				 * So people don't whine about messy desync issues when someone cancels CraftItemEvent, or when a crafting
				 * transaction goes wrong.
				 */
				$this->session->sendDataPacket(ContainerClosePacket::create(InventoryManager::HARDCODED_CRAFTING_GRID_WINDOW_ID, true));

				return false;
			}finally{
				$this->craftingTransaction = null;
			}
		}else{
			//normal transaction fallthru
			if($this->craftingTransaction !== null){
				$this->session->getLogger()->debug("Got unexpected normal inventory action with incomplete crafting transaction, refusing to execute crafting");
				$this->craftingTransaction = null;
				return false;
			}

			if(count($actions) === 0){
				//TODO: 1.13+ often sends transactions with nothing but useless crap in them, no need for the debug noise
				return true;
			}

			$transaction = new InventoryTransaction($this->player, $actions);
			$this->inventoryManager->onTransactionStart($transaction);
			try{
				$transaction->execute();
			}catch(TransactionException $e){
				$logger = $this->session->getLogger();
				$logger->debug("Failed to execute inventory transaction: " . $e->getMessage());
				$logger->debug("Actions: " . json_encode($data->getActions()));

				foreach($transaction->getInventories() as $inventory){
					$this->inventoryManager->syncContents($inventory);
				}

				return false;
			}
		}

		return true;
	}

	private function handleUseItemTransaction(UseItemTransactionData $data) : bool{
		$this->player->selectHotbarSlot($data->getHotbarSlot());

		switch($data->getActionType()){
			case UseItemTransactionData::ACTION_CLICK_BLOCK:
				//TODO: start hack for client spam bug
				$clickPos = $data->getClickPos();
				$spamBug = ($this->lastRightClickData !== null and
					microtime(true) - $this->lastRightClickTime < 0.1 and //100ms
					$this->lastRightClickData->getPlayerPos()->distanceSquared($data->getPlayerPos()) < 0.00001 and
					$this->lastRightClickData->getBlockPos()->equals($data->getBlockPos()) and
					$this->lastRightClickData->getClickPos()->distanceSquared($clickPos) < 0.00001 //signature spam bug has 0 distance, but allow some error
				);
				//get rid of continued spam if the player clicks and holds right-click
				$this->lastRightClickData = $data;
				$this->lastRightClickTime = microtime(true);
				if($spamBug){
					return true;
				}
				//TODO: end hack for client spam bug

				$blockPos = $data->getBlockPos();
				if(!$this->player->interactBlock($blockPos, $data->getFace(), $clickPos)){
					$this->onFailedBlockAction($blockPos, $data->getFace());
				}elseif(
					!array_key_exists($windowId = InventoryManager::HARDCODED_CRAFTING_GRID_WINDOW_ID, $this->openHardcodedWindows) &&
					$this->player->getCraftingGrid()->getGridWidth() === CraftingGrid::SIZE_BIG
				){
					//TODO: HACK! crafting grid doesn't fit very well into the current PM container system, so this hack
					//allows it to carry on working approximately the same way as it did in 1.14
					$this->openHardcodedWindows[$windowId] = true;
					$this->session->sendDataPacket(ContainerOpenPacket::blockInvVec3(
						InventoryManager::HARDCODED_CRAFTING_GRID_WINDOW_ID,
						WindowTypes::WORKBENCH,
						$blockPos
					));
				}
				return true;
			case UseItemTransactionData::ACTION_BREAK_BLOCK:
				$blockPos = $data->getBlockPos();
				if(!$this->player->breakBlock($blockPos)){
					$this->onFailedBlockAction($blockPos, null);
				}
				return true;
			case UseItemTransactionData::ACTION_CLICK_AIR:
				if($this->player->isUsingItem()){
					if(!$this->player->consumeHeldItem()){
						$this->inventoryManager->syncSlot($this->player->getInventory(), $this->player->getInventory()->getHeldItemIndex());
					}
					return true;
				}
				if(!$this->player->useHeldItem()){
					$this->inventoryManager->syncSlot($this->player->getInventory(), $this->player->getInventory()->getHeldItemIndex());
				}
				return true;
		}

		return false;
	}

	/**
	 * Internal function used to execute rollbacks when an action fails on a block.
	 */
	private function onFailedBlockAction(Vector3 $blockPos, ?int $face) : void{
		$this->inventoryManager->syncSlot($this->player->getInventory(), $this->player->getInventory()->getHeldItemIndex());
		if($blockPos->distanceSquared($this->player->getLocation()) < 10000){
			$blocks = $blockPos->sidesArray();
			if($face !== null){
				$sidePos = $blockPos->getSide($face);

				/** @var Vector3[] $blocks */
				array_push($blocks, ...$sidePos->sidesArray()); //getAllSides() on each of these will include $blockPos and $sidePos because they are next to each other
			}else{
				$blocks[] = $blockPos;
			}
			foreach($this->player->getWorld()->createBlockUpdatePackets($blocks) as $packet){
				$this->session->sendDataPacket($packet);
			}
		}
	}

	private function handleUseItemOnEntityTransaction(UseItemOnEntityTransactionData $data) : bool{
		$target = $this->player->getWorld()->getEntity($data->getEntityRuntimeId());
		if($target === null){
			return false;
		}

		$this->player->selectHotbarSlot($data->getHotbarSlot());

		//TODO: use transactiondata for rollbacks here
		switch($data->getActionType()){
			case UseItemOnEntityTransactionData::ACTION_INTERACT:
				if(!$this->player->interactEntity($target, $data->getClickPos())){
					$this->inventoryManager->syncSlot($this->player->getInventory(), $this->player->getInventory()->getHeldItemIndex());
				}
				return true;
			case UseItemOnEntityTransactionData::ACTION_ATTACK:
				if(!$this->player->attackEntity($target)){
					$this->inventoryManager->syncSlot($this->player->getInventory(), $this->player->getInventory()->getHeldItemIndex());
				}
				return true;
		}

		return false;
	}

	private function handleReleaseItemTransaction(ReleaseItemTransactionData $data) : bool{
		$this->player->selectHotbarSlot($data->getHotbarSlot());

		//TODO: use transactiondata for rollbacks here (resending entire inventory is very wasteful)
		switch($data->getActionType()){
			case ReleaseItemTransactionData::ACTION_RELEASE:
				if(!$this->player->releaseHeldItem()){
					$this->inventoryManager->syncContents($this->player->getInventory());
				}
				return true;
		}

		return false;
	}

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool{
		if($packet->windowId === ContainerIds::OFFHAND){
			return true; //this happens when we put an item into the offhand
		}
		if($packet->windowId === ContainerIds::INVENTORY){
			$this->inventoryManager->onClientSelectHotbarSlot($packet->hotbarSlot);
			if(!$this->player->selectHotbarSlot($packet->hotbarSlot)){
				$this->inventoryManager->syncSelectedHotbarSlot();
			}
			return true;
		}
		return false;
	}

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool{
		return true; //Not used
	}

	public function handleInteract(InteractPacket $packet) : bool{
		if($packet->action === InteractPacket::ACTION_MOUSEOVER){
			//TODO HACK: silence useless spam (MCPE 1.8)
			//due to some messy Mojang hacks, it sends this when changing the held item now, which causes us to think
			//the inventory was closed when it wasn't.
			//this is also sent whenever entity metadata updates, which can get really spammy.
			//TODO: implement handling for this where it matters
			return true;
		}
		$target = $this->player->getWorld()->getEntity($packet->target);
		if($target === null){
			return false;
		}
		if(
			$packet->action === InteractPacket::ACTION_OPEN_INVENTORY && $target === $this->player &&
			!array_key_exists($windowId = InventoryManager::HARDCODED_INVENTORY_WINDOW_ID, $this->openHardcodedWindows)
		){
			//TODO: HACK! this restores 1.14ish behaviour, but this should be able to be listened to and
			//controlled by plugins. However, the player is always a subscriber to their own inventory so it
			//doesn't integrate well with the regular container system right now.
			$this->openHardcodedWindows[$windowId] = true;
			$this->session->sendDataPacket(ContainerOpenPacket::entityInv(
				InventoryManager::HARDCODED_INVENTORY_WINDOW_ID,
				WindowTypes::INVENTORY,
				$this->player->getId()
			));
			return true;
		}
		return false; //TODO
	}

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool{
		return $this->player->pickBlock(new Vector3($packet->blockX, $packet->blockY, $packet->blockZ), $packet->addUserData);
	}

	public function handleActorPickRequest(ActorPickRequestPacket $packet) : bool{
		return false; //TODO
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool{
		$pos = new Vector3($packet->x, $packet->y, $packet->z);

		switch($packet->action){
			case PlayerActionPacket::ACTION_START_BREAK:
				if(!$this->player->attackBlock($pos, $packet->face)){
					$this->onFailedBlockAction($pos, $packet->face);
				}

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
				if(!$this->player->toggleSprint(true)){
					$this->player->sendData([$this->player]);
				}
				return true;
			case PlayerActionPacket::ACTION_STOP_SPRINT:
				if(!$this->player->toggleSprint(false)){
					$this->player->sendData([$this->player]);
				}
				return true;
			case PlayerActionPacket::ACTION_START_SNEAK:
				if(!$this->player->toggleSneak(true)){
					$this->player->sendData([$this->player]);
				}
				return true;
			case PlayerActionPacket::ACTION_STOP_SNEAK:
				if(!$this->player->toggleSneak(false)){
					$this->player->sendData([$this->player]);
				}
				return true;
			case PlayerActionPacket::ACTION_START_GLIDE:
			case PlayerActionPacket::ACTION_STOP_GLIDE:
				break; //TODO
			case PlayerActionPacket::ACTION_CRACK_BREAK:
				$this->player->continueBreakBlock($pos, $packet->face);
				break;
			case PlayerActionPacket::ACTION_START_SWIMMING:
				break; //TODO
			case PlayerActionPacket::ACTION_STOP_SWIMMING:
				//TODO: handle this when it doesn't spam every damn tick (yet another spam bug!!)
				break;
			case PlayerActionPacket::ACTION_INTERACT_BLOCK: //TODO: ignored (for now)
				break;
			case PlayerActionPacket::ACTION_CREATIVE_PLAYER_DESTROY_BLOCK:
				//TODO: do we need to handle this?
				break;
			default:
				$this->session->getLogger()->debug("Unhandled/unknown player action type " . $packet->action);
				return false;
		}

		$this->player->setUsingItem(false);

		return true;
	}

	public function handleSetActorMotion(SetActorMotionPacket $packet) : bool{
		return true; //Not used: This packet is (erroneously) sent to the server when the client is riding a vehicle.
	}

	public function handleAnimate(AnimatePacket $packet) : bool{
		return true; //Not used
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool{
		$this->player->doCloseInventory();

		if(array_key_exists($packet->windowId, $this->openHardcodedWindows)){
			unset($this->openHardcodedWindows[$packet->windowId]);
		}else{
			$this->inventoryManager->onClientRemoveWindow($packet->windowId);
		}

		$this->session->sendDataPacket(ContainerClosePacket::create($packet->windowId, false));
		return true;
	}

	public function handlePlayerHotbar(PlayerHotbarPacket $packet) : bool{
		return true; //this packet is useless
	}

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool{
		return true; //this is a broken useless packet, so we don't use it
	}

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool{
		if($packet->entityUniqueId !== $this->player->getId()){
			return false; //TODO: operators can change other people's permissions using this
		}

		$handled = false;

		$isFlying = $packet->getFlag(AdventureSettingsPacket::FLYING);
		if($isFlying !== $this->player->isFlying()){
			if(!$this->player->toggleFlight($isFlying)){
				$this->session->syncAdventureSettings($this->player);
			}
			$handled = true;
		}

		//TODO: check for other changes

		return $handled;
	}

	public function handleBlockActorData(BlockActorDataPacket $packet) : bool{
		$pos = new Vector3($packet->x, $packet->y, $packet->z);
		if($pos->distanceSquared($this->player->getLocation()) > 10000){
			return false;
		}

		$block = $this->player->getLocation()->getWorld()->getBlock($pos);
		$nbt = $packet->namedtag->getRoot();
		if(!($nbt instanceof CompoundTag)) throw new AssumptionFailedError("PHPStan should ensure this is a CompoundTag"); //for phpstorm's benefit

		if($block instanceof BaseSign){
			if(($textBlobTag = $nbt->getTag("Text")) instanceof StringTag){
				try{
					$text = SignText::fromBlob($textBlobTag->getValue());
				}catch(\InvalidArgumentException $e){
					throw PacketHandlingException::wrap($e, "Invalid sign text update");
				}

				try{
					if(!$block->updateText($this->player, $text)){
						foreach($this->player->getWorld()->createBlockUpdatePackets([$pos]) as $updatePacket){
							$this->session->sendDataPacket($updatePacket);
						}
					}
				}catch(\UnexpectedValueException $e){
					throw PacketHandlingException::wrap($e);
				}

				return true;
			}

			$this->session->getLogger()->debug("Invalid sign update data: " . base64_encode($packet->namedtag->getEncodedNbt()));
		}

		return false;
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool{
		return false; //TODO
	}

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool{
		$gameMode = TypeConverter::getInstance()->protocolGameModeToCore($packet->gamemode);
		if($gameMode === null || !$gameMode->equals($this->player->getGamemode())){
			//Set this back to default. TODO: handle this properly
			$this->session->syncGameMode($this->player->getGamemode(), true);
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
		$block = $this->player->getWorld()->getBlockAt($packet->x, $packet->y, $packet->z);
		if($block instanceof ItemFrame and $block->getFramedItem() !== null){
			return $this->player->attackBlock(new Vector3($packet->x, $packet->y, $packet->z), $block->getFacing());
		}
		return false;
	}

	public function handleBossEvent(BossEventPacket $packet) : bool{
		return false; //TODO
	}

	public function handleShowCredits(ShowCreditsPacket $packet) : bool{
		return false; //TODO: handle resume
	}

	public function handleCommandRequest(CommandRequestPacket $packet) : bool{
		if(strpos($packet->command, '/') === 0){
			$this->player->chat($packet->command);
			return true;
		}
		return false;
	}

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool{
		return false; //TODO
	}

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool{
		try{
			$skin = SkinAdapterSingleton::get()->fromSkinData($packet->skin);
		}catch(InvalidSkinException $e){
			throw PacketHandlingException::wrap($e, "Invalid skin in PlayerSkinPacket");
		}
		return $this->player->changeSkin($skin, $packet->newSkinName, $packet->oldSkinName);
	}

	public function handleSubClientLogin(SubClientLoginPacket $packet) : bool{
		return false; //TODO
	}

	public function handleBookEdit(BookEditPacket $packet) : bool{
		//TODO: break this up into book API things
		$oldBook = $this->player->getInventory()->getItem($packet->inventorySlot);
		if(!($oldBook instanceof WritableBook)){
			return false;
		}

		$newBook = clone $oldBook;
		$modifiedPages = [];

		switch($packet->type){
			case BookEditPacket::TYPE_REPLACE_PAGE:
				$newBook->setPageText($packet->pageNumber, $packet->text);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_ADD_PAGE:
				if(!$newBook->pageExists($packet->pageNumber)){
					//this may only come before a page which already exists
					//TODO: the client can send insert-before actions on trailing client-side pages which cause odd behaviour on the server
					return false;
				}
				$newBook->insertPage($packet->pageNumber, $packet->text);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_DELETE_PAGE:
				if(!$newBook->pageExists($packet->pageNumber)){
					return false;
				}
				$newBook->deletePage($packet->pageNumber);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_SWAP_PAGES:
				if(!$newBook->pageExists($packet->pageNumber) or !$newBook->pageExists($packet->secondaryPageNumber)){
					//the client will create pages on its own without telling us until it tries to switch them
					$newBook->addPage(max($packet->pageNumber, $packet->secondaryPageNumber));
				}
				$newBook->swapPages($packet->pageNumber, $packet->secondaryPageNumber);
				$modifiedPages = [$packet->pageNumber, $packet->secondaryPageNumber];
				break;
			case BookEditPacket::TYPE_SIGN_BOOK:
				/** @var WrittenBook $newBook */
				$newBook = VanillaItems::WRITTEN_BOOK()
					->setPages($oldBook->getPages())
					->setAuthor($packet->author)
					->setTitle($packet->title)
					->setGeneration(WrittenBook::GENERATION_ORIGINAL);
				break;
			default:
				return false;
		}

		$event = new PlayerEditBookEvent($this->player, $oldBook, $newBook, $packet->type, $modifiedPages);
		$event->call();
		if($event->isCancelled()){
			return true;
		}

		$this->player->getInventory()->setItem($packet->inventorySlot, $event->getNewBook());

		return true;
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet) : bool{
		return $this->player->onFormSubmit($packet->formId, self::stupid_json_decode($packet->formData, true));
	}

	/**
	 * Hack to work around a stupid bug in Minecraft W10 which causes empty strings to be sent unquoted in form responses.
	 *
	 * @return mixed
	 * @throws PacketHandlingException
	 */
	private static function stupid_json_decode(string $json, bool $assoc = false){
		if(preg_match('/^\[(.+)\]$/s', $json, $matches) > 0){
			$raw = $matches[1];
			$lastComma = -1;
			$newParts = [];
			$inQuotes = false;
			for($i = 0, $len = strlen($raw); $i <= $len; ++$i){
				if($i === $len or ($raw[$i] === "," and !$inQuotes)){
					$part = substr($raw, $lastComma + 1, $i - ($lastComma + 1));
					if(trim($part) === ""){ //regular parts will have quotes or something else that makes them non-empty
						$part = '""';
					}
					$newParts[] = $part;
					$lastComma = $i;
				}elseif($raw[$i] === '"'){
					if(!$inQuotes){
						$inQuotes = true;
					}else{
						$backslashes = 0;
						for(; $backslashes < $i && $raw[$i - $backslashes - 1] === "\\"; ++$backslashes){}
						if(($backslashes % 2) === 0){ //unescaped quote
							$inQuotes = false;
						}
					}
				}
			}

			$fixed = "[" . implode(",", $newParts) . "]";
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

	public function handleNetworkStackLatency(NetworkStackLatencyPacket $packet) : bool{
		return true; //TODO: implement this properly - this is here to silence debug spam from MCPE dev builds
	}

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool{
		/*
		 * We don't handle this - all sounds are handled by the server now.
		 * However, some plugins find this useful to detect events like left-click-air, which doesn't have any other
		 * action bound to it.
		 * In addition, we use this handler to silence debug noise, since this packet is frequently sent by the client.
		 */
		return true;
	}
}
