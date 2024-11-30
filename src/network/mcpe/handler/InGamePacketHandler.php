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
use pocketmine\block\Lectern;
use pocketmine\block\tile\Sign;
use pocketmine\block\utils\SignText;
use pocketmine\entity\animation\ConsumingItemAnimation;
use pocketmine\entity\Attribute;
use pocketmine\entity\InvalidSkinException;
use pocketmine\event\player\PlayerEditBookEvent;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionBuilder;
use pocketmine\inventory\transaction\TransactionCancelledException;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\VanillaItems;
use pocketmine\item\WritableBook;
use pocketmine\item\WritableBookPage;
use pocketmine\item\WrittenBook;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ActorPickRequestPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\CommandRequestPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\EmotePacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemStackRequestPacket;
use pocketmine\network\mcpe\protocol\ItemStackResponsePacket;
use pocketmine\network\mcpe\protocol\LabTablePacket;
use pocketmine\network\mcpe\protocol\LecternUpdatePacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacketV1;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
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
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\MismatchTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\inventory\NormalTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\ReleaseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequest;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponse;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionStopBreak;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\world\format\Chunk;
use function array_push;
use function count;
use function fmod;
use function get_debug_type;
use function implode;
use function in_array;
use function is_infinite;
use function is_nan;
use function json_decode;
use function max;
use function mb_strlen;
use function microtime;
use function sprintf;
use function str_starts_with;
use function strlen;
use const JSON_THROW_ON_ERROR;

/**
 * This handler handles packets related to general gameplay.
 */
class InGamePacketHandler extends PacketHandler{
	private const MAX_FORM_RESPONSE_DEPTH = 2; //modal/simple will be 1, custom forms 2 - they will never contain anything other than string|int|float|bool|null

	protected float $lastRightClickTime = 0.0;
	protected ?UseItemTransactionData $lastRightClickData = null;

	protected ?Vector3 $lastPlayerAuthInputPosition = null;
	protected ?float $lastPlayerAuthInputYaw = null;
	protected ?float $lastPlayerAuthInputPitch = null;
	protected ?int $lastPlayerAuthInputFlags = null;

	public bool $forceMoveSync = false;

	protected ?string $lastRequestedFullSkinId = null;

	public function __construct(
		private Player $player,
		private NetworkSession $session,
		private InventoryManager $inventoryManager
	){}

	public function handleText(TextPacket $packet) : bool{
		if($packet->type === TextPacket::TYPE_CHAT){
			return $this->player->chat($packet->message);
		}

		return false;
	}

	public function handleMovePlayer(MovePlayerPacket $packet) : bool{
		//The client sends this every time it lands on the ground, even when using PlayerAuthInputPacket.
		//Silence the debug spam that this causes.
		return true;
	}

	private function resolveOnOffInputFlags(int $inputFlags, int $startFlag, int $stopFlag) : ?bool{
		$enabled = ($inputFlags & (1 << $startFlag)) !== 0;
		$disabled = ($inputFlags & (1 << $stopFlag)) !== 0;
		if($enabled !== $disabled){
			return $enabled;
		}
		//neither flag was set, or both were set
		return null;
	}

	public function handlePlayerAuthInput(PlayerAuthInputPacket $packet) : bool{
		$rawPos = $packet->getPosition();
		$rawYaw = $packet->getYaw();
		$rawPitch = $packet->getPitch();
		foreach([$rawPos->x, $rawPos->y, $rawPos->z, $rawYaw, $packet->getHeadYaw(), $rawPitch] as $float){
			if(is_infinite($float) || is_nan($float)){
				$this->session->getLogger()->debug("Invalid movement received, contains NAN/INF components");
				return false;
			}
		}

		if($rawYaw !== $this->lastPlayerAuthInputYaw || $rawPitch !== $this->lastPlayerAuthInputPitch){
			$this->lastPlayerAuthInputYaw = $rawYaw;
			$this->lastPlayerAuthInputPitch = $rawPitch;

			$yaw = fmod($rawYaw, 360);
			$pitch = fmod($rawPitch, 360);
			if($yaw < 0){
				$yaw += 360;
			}

			$this->player->setRotation($yaw, $pitch);
		}

		$hasMoved = $this->lastPlayerAuthInputPosition === null || !$this->lastPlayerAuthInputPosition->equals($rawPos);
		$newPos = $rawPos->subtract(0, 1.62, 0)->round(4);

		if($this->forceMoveSync && $hasMoved){
			$curPos = $this->player->getLocation();

			if($newPos->distanceSquared($curPos) > 1){  //Tolerate up to 1 block to avoid problems with client-sided physics when spawning in blocks
				$this->session->getLogger()->debug("Got outdated pre-teleport movement, received " . $newPos . ", expected " . $curPos);
				//Still getting movements from before teleport, ignore them
				return true;
			}

			// Once we get a movement within a reasonable distance, treat it as a teleport ACK and remove position lock
			$this->forceMoveSync = false;
		}

		$inputFlags = $packet->getInputFlags();
		if($inputFlags !== $this->lastPlayerAuthInputFlags){
			$this->lastPlayerAuthInputFlags = $inputFlags;

			$sneaking = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_SNEAKING, PlayerAuthInputFlags::STOP_SNEAKING);
			$sprinting = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_SPRINTING, PlayerAuthInputFlags::STOP_SPRINTING);
			$swimming = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_SWIMMING, PlayerAuthInputFlags::STOP_SWIMMING);
			$gliding = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_GLIDING, PlayerAuthInputFlags::STOP_GLIDING);
			$flying = $this->resolveOnOffInputFlags($inputFlags, PlayerAuthInputFlags::START_FLYING, PlayerAuthInputFlags::STOP_FLYING);
			$mismatch =
				($sneaking !== null && !$this->player->toggleSneak($sneaking)) |
				($sprinting !== null && !$this->player->toggleSprint($sprinting)) |
				($swimming !== null && !$this->player->toggleSwim($swimming)) |
				($gliding !== null && !$this->player->toggleGlide($gliding)) |
				($flying !== null && !$this->player->toggleFlight($flying));
			if((bool) $mismatch){
				$this->player->sendData([$this->player]);
			}

			if($packet->hasFlag(PlayerAuthInputFlags::START_JUMPING)){
				$this->player->jump();
			}
			if($packet->hasFlag(PlayerAuthInputFlags::MISSED_SWING)){
				$this->player->missSwing();
			}
		}

		if(!$this->forceMoveSync && $hasMoved){
			$this->lastPlayerAuthInputPosition = $rawPos;
			//TODO: this packet has WAYYYYY more useful information that we're not using
			$this->player->handleMovement($newPos);
		}

		$packetHandled = true;

		$blockActions = $packet->getBlockActions();
		if($blockActions !== null){
			if(count($blockActions) > 100){
				throw new PacketHandlingException("Too many block actions in PlayerAuthInputPacket");
			}
			foreach(Utils::promoteKeys($blockActions) as $k => $blockAction){
				$actionHandled = false;
				if($blockAction instanceof PlayerBlockActionStopBreak){
					$actionHandled = $this->handlePlayerActionFromData($blockAction->getActionType(), new BlockPosition(0, 0, 0), Facing::DOWN);
				}elseif($blockAction instanceof PlayerBlockActionWithBlockInfo){
					$actionHandled = $this->handlePlayerActionFromData($blockAction->getActionType(), $blockAction->getBlockPosition(), $blockAction->getFace());
				}

				if(!$actionHandled){
					$packetHandled = false;
					$this->session->getLogger()->debug("Unhandled player block action at offset $k in PlayerAuthInputPacket");
				}
			}
		}

		$useItemTransaction = $packet->getItemInteractionData();
		if($useItemTransaction !== null){
			if(count($useItemTransaction->getTransactionData()->getActions()) > 100){
				throw new PacketHandlingException("Too many actions in item use transaction");
			}

			$this->inventoryManager->setCurrentItemStackRequestId($useItemTransaction->getRequestId());
			$this->inventoryManager->addRawPredictedSlotChanges($useItemTransaction->getTransactionData()->getActions());
			if(!$this->handleUseItemTransaction($useItemTransaction->getTransactionData())){
				$packetHandled = false;
				$this->session->getLogger()->debug("Unhandled transaction in PlayerAuthInputPacket (type " . $useItemTransaction->getTransactionData()->getActionType() . ")");
			}else{
				$this->inventoryManager->syncMismatchedPredictedSlotChanges();
			}
			$this->inventoryManager->setCurrentItemStackRequestId(null);
		}

		$itemStackRequest = $packet->getItemStackRequest();
		if($itemStackRequest !== null){
			$result = $this->handleSingleItemStackRequest($itemStackRequest);
			$this->session->sendDataPacket(ItemStackResponsePacket::create([$result]));
		}

		return $packetHandled;
	}

	public function handleLevelSoundEventPacketV1(LevelSoundEventPacketV1 $packet) : bool{
		return true; //useless leftover from 1.8
	}

	public function handleActorEvent(ActorEventPacket $packet) : bool{
		if($packet->actorRuntimeId !== $this->player->getId()){
			//TODO HACK: EATING_ITEM is sent back to the server when the server sends it for other players (1.14 bug, maybe earlier)
			return $packet->actorRuntimeId === ActorEvent::EATING_ITEM;
		}

		switch($packet->eventId){
			case ActorEvent::EATING_ITEM: //TODO: ignore this and handle it server-side
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

		if(count($packet->trData->getActions()) > 50){
			throw new PacketHandlingException("Too many actions in inventory transaction");
		}
		if(count($packet->requestChangedSlots) > 10){
			throw new PacketHandlingException("Too many slot sync requests in inventory transaction");
		}

		$this->inventoryManager->setCurrentItemStackRequestId($packet->requestId);
		$this->inventoryManager->addRawPredictedSlotChanges($packet->trData->getActions());

		if($packet->trData instanceof NormalTransactionData){
			$result = $this->handleNormalTransaction($packet->trData, $packet->requestId);
		}elseif($packet->trData instanceof MismatchTransactionData){
			$this->session->getLogger()->debug("Mismatch transaction received");
			$this->inventoryManager->requestSyncAll();
			$result = true;
		}elseif($packet->trData instanceof UseItemTransactionData){
			$result = $this->handleUseItemTransaction($packet->trData);
		}elseif($packet->trData instanceof UseItemOnEntityTransactionData){
			$result = $this->handleUseItemOnEntityTransaction($packet->trData);
		}elseif($packet->trData instanceof ReleaseItemTransactionData){
			$result = $this->handleReleaseItemTransaction($packet->trData);
		}

		$this->inventoryManager->syncMismatchedPredictedSlotChanges();

		//requestChangedSlots asks the server to always send out the contents of the specified slots, even if they
		//haven't changed. Handling these is necessary to ensure the client inventory stays in sync if the server
		//rejects the transaction. The most common example of this is equipping armor by right-click, which doesn't send
		//a legacy prediction action for the destination armor slot.
		foreach($packet->requestChangedSlots as $containerInfo){
			foreach($containerInfo->getChangedSlotIndexes() as $netSlot){
				[$windowId, $slot] = ItemStackContainerIdTranslator::translate($containerInfo->getContainerId(), $this->inventoryManager->getCurrentWindowId(), $netSlot);
				$inventoryAndSlot = $this->inventoryManager->locateWindowAndSlot($windowId, $slot);
				if($inventoryAndSlot !== null){ //trigger the normal slot sync logic
					$this->inventoryManager->onSlotChange($inventoryAndSlot[0], $inventoryAndSlot[1]);
				}
			}
		}

		$this->inventoryManager->setCurrentItemStackRequestId(null);
		return $result;
	}

	private function executeInventoryTransaction(InventoryTransaction $transaction, int $requestId) : bool{
		$this->player->setUsingItem(false);

		$this->inventoryManager->setCurrentItemStackRequestId($requestId);
		$this->inventoryManager->addTransactionPredictedSlotChanges($transaction);
		try{
			$transaction->execute();
		}catch(TransactionValidationException $e){
			$this->inventoryManager->requestSyncAll();
			$logger = $this->session->getLogger();
			$logger->debug("Invalid inventory transaction $requestId: " . $e->getMessage());

			return false;
		}catch(TransactionCancelledException){
			$this->session->getLogger()->debug("Inventory transaction $requestId cancelled by a plugin");

			return false;
		}finally{
			$this->inventoryManager->syncMismatchedPredictedSlotChanges();
			$this->inventoryManager->setCurrentItemStackRequestId(null);
		}

		return true;
	}

	private function handleNormalTransaction(NormalTransactionData $data, int $itemStackRequestId) : bool{
		//When the ItemStackRequest system is used, this transaction type is used for dropping items by pressing Q.
		//I don't know why they don't just use ItemStackRequest for that too, which already supports dropping items by
		//clicking them outside an open inventory menu, but for now it is what it is.
		//Fortunately, this means we can be much stricter about the validation criteria.

		$actionCount = count($data->getActions());
		if($actionCount > 2){
			if($actionCount > 5){
				throw new PacketHandlingException("Too many actions ($actionCount) in normal inventory transaction");
			}

			//Due to a bug in the game, this transaction type is still sent when a player edits a book. We don't need
			//these transactions for editing books, since we have BookEditPacket, so we can just ignore them.
			$this->session->getLogger()->debug("Ignoring normal inventory transaction with $actionCount actions (drop-item should have exactly 2 actions)");
			return false;
		}

		$sourceSlot = null;
		$clientItemStack = null;
		$droppedCount = null;

		foreach($data->getActions() as $networkInventoryAction){
			if($networkInventoryAction->sourceType === NetworkInventoryAction::SOURCE_WORLD && $networkInventoryAction->inventorySlot == NetworkInventoryAction::ACTION_MAGIC_SLOT_DROP_ITEM){
				$droppedCount = $networkInventoryAction->newItem->getItemStack()->getCount();
				if($droppedCount <= 0){
					throw new PacketHandlingException("Expected positive count for dropped item");
				}
			}elseif($networkInventoryAction->sourceType === NetworkInventoryAction::SOURCE_CONTAINER && $networkInventoryAction->windowId === ContainerIds::INVENTORY){
				//mobile players can drop an item from a non-selected hotbar slot
				$sourceSlot = $networkInventoryAction->inventorySlot;
				$clientItemStack = $networkInventoryAction->oldItem->getItemStack();
			}else{
				$this->session->getLogger()->debug("Unexpected inventory action type $networkInventoryAction->sourceType in drop item transaction");
				return false;
			}
		}
		if($sourceSlot === null || $clientItemStack === null || $droppedCount === null){
			$this->session->getLogger()->debug("Missing information in drop item transaction, need source slot, client item stack and dropped count");
			return false;
		}

		$inventory = $this->player->getInventory();

		if(!$inventory->slotExists($sourceSlot)){
			return false; //TODO: size desync??
		}

		$sourceSlotItem = $inventory->getItem($sourceSlot);
		if($sourceSlotItem->getCount() < $droppedCount){
			return false;
		}
		$serverItemStack = $this->session->getTypeConverter()->coreItemStackToNet($sourceSlotItem);
		//Sadly we don't have itemstack IDs here, so we have to compare the basic item properties to ensure that we're
		//dropping the item the client expects (inventory might be out of sync with the client).
		if(
			$serverItemStack->getId() !== $clientItemStack->getId() ||
			$serverItemStack->getMeta() !== $clientItemStack->getMeta() ||
			$serverItemStack->getCount() !== $clientItemStack->getCount() ||
			$serverItemStack->getBlockRuntimeId() !== $clientItemStack->getBlockRuntimeId()
			//Raw extraData may not match because of TAG_Compound key ordering differences, and decoding it to compare
			//is costly. Assume that we're in sync if id+meta+count+runtimeId match.
			//NB: Make sure $clientItemStack isn't used to create the dropped item, as that would allow the client
			//to change the item NBT since we're not validating it.
		){
			return false;
		}

		//this modifies $sourceSlotItem
		$droppedItem = $sourceSlotItem->pop($droppedCount);

		$builder = new TransactionBuilder();
		$builder->getInventory($inventory)->setItem($sourceSlot, $sourceSlotItem);
		$builder->addAction(new DropItemAction($droppedItem));

		$transaction = new InventoryTransaction($this->player, $builder->generateActions());
		return $this->executeInventoryTransaction($transaction, $itemStackRequestId);
	}

	private function handleUseItemTransaction(UseItemTransactionData $data) : bool{
		$this->player->selectHotbarSlot($data->getHotbarSlot());

		switch($data->getActionType()){
			case UseItemTransactionData::ACTION_CLICK_BLOCK:
				//TODO: start hack for client spam bug
				$clickPos = $data->getClickPosition();
				$spamBug = ($this->lastRightClickData !== null &&
					microtime(true) - $this->lastRightClickTime < 0.1 && //100ms
					$this->lastRightClickData->getPlayerPosition()->distanceSquared($data->getPlayerPosition()) < 0.00001 &&
					$this->lastRightClickData->getBlockPosition()->equals($data->getBlockPosition()) &&
					$this->lastRightClickData->getClickPosition()->distanceSquared($clickPos) < 0.00001 //signature spam bug has 0 distance, but allow some error
				);
				//get rid of continued spam if the player clicks and holds right-click
				$this->lastRightClickData = $data;
				$this->lastRightClickTime = microtime(true);
				if($spamBug){
					return true;
				}
				//TODO: end hack for client spam bug

				self::validateFacing($data->getFace());

				$blockPos = $data->getBlockPosition();
				$vBlockPos = new Vector3($blockPos->getX(), $blockPos->getY(), $blockPos->getZ());
				$this->player->interactBlock($vBlockPos, $data->getFace(), $clickPos);
				//always sync this in case plugins caused a different result than the client expected
				//we *could* try to enhance detection of plugin-altered behaviour, but this would require propagating
				//more information up the stack. For now I think this is good enough.
				//if only the client would tell us what blocks it thinks changed...
				$this->syncBlocksNearby($vBlockPos, $data->getFace());
				return true;
			case UseItemTransactionData::ACTION_BREAK_BLOCK:
				$blockPos = $data->getBlockPosition();
				$vBlockPos = new Vector3($blockPos->getX(), $blockPos->getY(), $blockPos->getZ());
				if(!$this->player->breakBlock($vBlockPos)){
					$this->syncBlocksNearby($vBlockPos, null);
				}
				return true;
			case UseItemTransactionData::ACTION_CLICK_AIR:
				if($this->player->isUsingItem()){
					if(!$this->player->consumeHeldItem()){
						$hungerAttr = $this->player->getAttributeMap()->get(Attribute::HUNGER) ?? throw new AssumptionFailedError();
						$hungerAttr->markSynchronized(false);
					}
					return true;
				}
				$this->player->useHeldItem();
				return true;
		}

		return false;
	}

	/**
	 * @throws PacketHandlingException
	 */
	private static function validateFacing(int $facing) : void{
		if(!in_array($facing, Facing::ALL, true)){
			throw new PacketHandlingException("Invalid facing value $facing");
		}
	}

	/**
	 * Syncs blocks nearby to ensure that the client and server agree on the world's blocks after a block interaction.
	 */
	private function syncBlocksNearby(Vector3 $blockPos, ?int $face) : void{
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
		$target = $this->player->getWorld()->getEntity($data->getActorRuntimeId());
		if($target === null){
			return false;
		}

		$this->player->selectHotbarSlot($data->getHotbarSlot());

		switch($data->getActionType()){
			case UseItemOnEntityTransactionData::ACTION_INTERACT:
				$this->player->interactEntity($target, $data->getClickPosition());
				return true;
			case UseItemOnEntityTransactionData::ACTION_ATTACK:
				$this->player->attackEntity($target);
				return true;
		}

		return false;
	}

	private function handleReleaseItemTransaction(ReleaseItemTransactionData $data) : bool{
		$this->player->selectHotbarSlot($data->getHotbarSlot());

		if($data->getActionType() == ReleaseItemTransactionData::ACTION_RELEASE){
			$this->player->releaseHeldItem();
			return true;
		}

		return false;
	}

	private function handleSingleItemStackRequest(ItemStackRequest $request) : ItemStackResponse{
		if(count($request->getActions()) > 60){
			//recipe book auto crafting can affect all slots of the inventory when consuming inputs or producing outputs
			//this means there could be as many as 50 CraftingConsumeInput actions or Place (taking the result) actions
			//in a single request (there are certain ways items can be arranged which will result in the same stack
			//being taken from multiple times, but this is behaviour with a calculable limit)
			//this means there SHOULD be AT MOST 53 actions in a single request, but 60 is a nice round number.
			//n64Stacks = ?
			//n1Stacks = 45 - n64Stacks
			//nItemsRequiredFor1Craft = 9
			//nResults = floor((n1Stacks + (n64Stacks * 64)) / nItemsRequiredFor1Craft)
			//nTakeActionsTotal = floor(64 / nResults) + max(1, 64 % nResults) + ((nResults * nItemsRequiredFor1Craft) - (n64Stacks * 64))
			throw new PacketHandlingException("Too many actions in ItemStackRequest");
		}
		$executor = new ItemStackRequestExecutor($this->player, $this->inventoryManager, $request);
		try{
			$transaction = $executor->generateInventoryTransaction();
			$result = $this->executeInventoryTransaction($transaction, $request->getRequestId());
		}catch(ItemStackRequestProcessException $e){
			$result = false;
			$this->session->getLogger()->debug("ItemStackRequest #" . $request->getRequestId() . " failed: " . $e->getMessage());
			$this->session->getLogger()->debug(implode("\n", Utils::printableExceptionInfo($e)));
			$this->inventoryManager->requestSyncAll();
		}

		if(!$result){
			return new ItemStackResponse(ItemStackResponse::RESULT_ERROR, $request->getRequestId());
		}
		return $executor->buildItemStackResponse();
	}

	public function handleItemStackRequest(ItemStackRequestPacket $packet) : bool{
		$responses = [];
		if(count($packet->getRequests()) > 80){
			//TODO: we can probably lower this limit, but this will do for now
			throw new PacketHandlingException("Too many requests in ItemStackRequestPacket");
		}
		foreach($packet->getRequests() as $request){
			$responses[] = $this->handleSingleItemStackRequest($request);
		}

		$this->session->sendDataPacket(ItemStackResponsePacket::create($responses));

		return true;
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
		$target = $this->player->getWorld()->getEntity($packet->targetActorRuntimeId);
		if($target === null){
			return false;
		}
		if($packet->action === InteractPacket::ACTION_OPEN_INVENTORY && $target === $this->player){
			$this->inventoryManager->onClientOpenMainInventory();
			return true;
		}
		return false; //TODO
	}

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool{
		return $this->player->pickBlock(new Vector3($packet->blockPosition->getX(), $packet->blockPosition->getY(), $packet->blockPosition->getZ()), $packet->addUserData);
	}

	public function handleActorPickRequest(ActorPickRequestPacket $packet) : bool{
		return $this->player->pickEntity($packet->actorUniqueId);
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool{
		return $this->handlePlayerActionFromData($packet->action, $packet->blockPosition, $packet->face);
	}

	private function handlePlayerActionFromData(int $action, BlockPosition $blockPosition, int $face) : bool{
		$pos = new Vector3($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ());

		switch($action){
			case PlayerAction::START_BREAK:
				self::validateFacing($face);
				if(!$this->player->attackBlock($pos, $face)){
					$this->syncBlocksNearby($pos, $face);
				}

				break;

			case PlayerAction::ABORT_BREAK:
			case PlayerAction::STOP_BREAK:
				$this->player->stopBreakBlock($pos);
				break;
			case PlayerAction::START_SLEEPING:
				//unused
				break;
			case PlayerAction::STOP_SLEEPING:
				$this->player->stopSleep();
				break;
			case PlayerAction::CRACK_BREAK:
				self::validateFacing($face);
				$this->player->continueBreakBlock($pos, $face);
				break;
			case PlayerAction::INTERACT_BLOCK: //TODO: ignored (for now)
				break;
			case PlayerAction::CREATIVE_PLAYER_DESTROY_BLOCK:
				//TODO: do we need to handle this?
				break;
			case PlayerAction::START_ITEM_USE_ON:
			case PlayerAction::STOP_ITEM_USE_ON:
				//TODO: this has no obvious use and seems only used for analytics in vanilla - ignore it
				break;
			default:
				$this->session->getLogger()->debug("Unhandled/unknown player action type " . $action);
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
		$this->inventoryManager->onClientRemoveWindow($packet->windowId);
		return true;
	}

	public function handlePlayerHotbar(PlayerHotbarPacket $packet) : bool{
		return true; //this packet is useless
	}

	public function handleBlockActorData(BlockActorDataPacket $packet) : bool{
		$pos = new Vector3($packet->blockPosition->getX(), $packet->blockPosition->getY(), $packet->blockPosition->getZ());
		if($pos->distanceSquared($this->player->getLocation()) > 10000){
			return false;
		}

		$block = $this->player->getLocation()->getWorld()->getBlock($pos);
		$nbt = $packet->nbt->getRoot();
		if(!($nbt instanceof CompoundTag)) throw new AssumptionFailedError("PHPStan should ensure this is a CompoundTag"); //for phpstorm's benefit

		if($block instanceof BaseSign){
			$frontTextTag = $nbt->getTag(Sign::TAG_FRONT_TEXT);
			if(!$frontTextTag instanceof CompoundTag){
				throw new PacketHandlingException("Invalid tag type " . get_debug_type($frontTextTag) . " for tag \"" . Sign::TAG_FRONT_TEXT . "\" in sign update data");
			}
			$textBlobTag = $frontTextTag->getTag(Sign::TAG_TEXT_BLOB);
			if(!$textBlobTag instanceof StringTag){
				throw new PacketHandlingException("Invalid tag type " . get_debug_type($textBlobTag) . " for tag \"" . Sign::TAG_TEXT_BLOB . "\" in sign update data");
			}

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

		return false;
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool{
		return false; //TODO
	}

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool{
		$gameMode = $this->session->getTypeConverter()->protocolGameModeToCore($packet->gamemode);
		if($gameMode !== $this->player->getGamemode()){
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

	public function handleBossEvent(BossEventPacket $packet) : bool{
		return false; //TODO
	}

	public function handleShowCredits(ShowCreditsPacket $packet) : bool{
		return false; //TODO: handle resume
	}

	public function handleCommandRequest(CommandRequestPacket $packet) : bool{
		if(str_starts_with($packet->command, '/')){
			$this->player->chat($packet->command);
			return true;
		}
		return false;
	}

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool{
		return false; //TODO
	}

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool{
		if($packet->skin->getFullSkinId() === $this->lastRequestedFullSkinId){
			//TODO: HACK! In 1.19.60, the client sends its skin back to us if we sent it a skin different from the one
			//it's using. We need to prevent this from causing a feedback loop.
			$this->session->getLogger()->debug("Refused duplicate skin change request");
			return true;
		}
		$this->lastRequestedFullSkinId = $packet->skin->getFullSkinId();

		$this->session->getLogger()->debug("Processing skin change request");
		try{
			$skin = $this->session->getTypeConverter()->getSkinAdapter()->fromSkinData($packet->skin);
		}catch(InvalidSkinException $e){
			throw PacketHandlingException::wrap($e, "Invalid skin in PlayerSkinPacket");
		}
		return $this->player->changeSkin($skin, $packet->newSkinName, $packet->oldSkinName);
	}

	public function handleSubClientLogin(SubClientLoginPacket $packet) : bool{
		return false; //TODO
	}

	/**
	 * @throws PacketHandlingException
	 */
	private function checkBookText(string $string, string $fieldName, int $softLimit, int $hardLimit, bool &$cancel) : string{
		if(strlen($string) > $hardLimit){
			throw new PacketHandlingException(sprintf("Book %s must be at most %d bytes, but have %d bytes", $fieldName, $hardLimit, strlen($string)));
		}

		$result = TextFormat::clean($string, false);
		//strlen() is O(1), mb_strlen() is O(n)
		if(strlen($result) > $softLimit * 4 || mb_strlen($result, 'UTF-8') > $softLimit){
			$cancel = true;
			$this->session->getLogger()->debug("Cancelled book edit due to $fieldName exceeded soft limit of $softLimit chars");
		}

		return $result;
	}

	public function handleBookEdit(BookEditPacket $packet) : bool{
		$inventory = $this->player->getInventory();
		if(!$inventory->slotExists($packet->inventorySlot)){
			return false;
		}
		//TODO: break this up into book API things
		$oldBook = $inventory->getItem($packet->inventorySlot);
		if(!($oldBook instanceof WritableBook)){
			return false;
		}

		$newBook = clone $oldBook;
		$modifiedPages = [];
		$cancel = false;
		switch($packet->type){
			case BookEditPacket::TYPE_REPLACE_PAGE:
				$text = self::checkBookText($packet->text, "page text", 256, WritableBookPage::PAGE_LENGTH_HARD_LIMIT_BYTES, $cancel);
				$newBook->setPageText($packet->pageNumber, $text);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_ADD_PAGE:
				if(!$newBook->pageExists($packet->pageNumber)){
					//this may only come before a page which already exists
					//TODO: the client can send insert-before actions on trailing client-side pages which cause odd behaviour on the server
					return false;
				}
				$text = self::checkBookText($packet->text, "page text", 256, WritableBookPage::PAGE_LENGTH_HARD_LIMIT_BYTES, $cancel);
				$newBook->insertPage($packet->pageNumber, $text);
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
				if(!$newBook->pageExists($packet->pageNumber) || !$newBook->pageExists($packet->secondaryPageNumber)){
					//the client will create pages on its own without telling us until it tries to switch them
					$newBook->addPage(max($packet->pageNumber, $packet->secondaryPageNumber));
				}
				$newBook->swapPages($packet->pageNumber, $packet->secondaryPageNumber);
				$modifiedPages = [$packet->pageNumber, $packet->secondaryPageNumber];
				break;
			case BookEditPacket::TYPE_SIGN_BOOK:
				$title = self::checkBookText($packet->title, "title", 16, Limits::INT16_MAX, $cancel);
				//this one doesn't have a limit in vanilla, so we have to improvise
				$author = self::checkBookText($packet->author, "author", 256, Limits::INT16_MAX, $cancel);

				$newBook = VanillaItems::WRITTEN_BOOK()
					->setPages($oldBook->getPages())
					->setAuthor($author)
					->setTitle($title)
					->setGeneration(WrittenBook::GENERATION_ORIGINAL);
				break;
			default:
				return false;
		}

		//for redundancy, in case of protocol changes, we don't want to pass these directly
		$action = match($packet->type){
			BookEditPacket::TYPE_REPLACE_PAGE => PlayerEditBookEvent::ACTION_REPLACE_PAGE,
			BookEditPacket::TYPE_ADD_PAGE => PlayerEditBookEvent::ACTION_ADD_PAGE,
			BookEditPacket::TYPE_DELETE_PAGE => PlayerEditBookEvent::ACTION_DELETE_PAGE,
			BookEditPacket::TYPE_SWAP_PAGES => PlayerEditBookEvent::ACTION_SWAP_PAGES,
			BookEditPacket::TYPE_SIGN_BOOK => PlayerEditBookEvent::ACTION_SIGN_BOOK,
			default => throw new AssumptionFailedError("We already filtered unknown types in the switch above")
		};

		/*
		 * Plugins may have created books with more than 50 pages; we allow plugins to do this, but not players.
		 * Don't allow the page count to grow past 50, but allow deleting, swapping or altering text of existing pages.
		 */
		$oldPageCount = count($oldBook->getPages());
		$newPageCount = count($newBook->getPages());
		if(($newPageCount > $oldPageCount && $newPageCount > 50)){
			$this->session->getLogger()->debug("Cancelled book edit due to adding too many pages (new page count would be $newPageCount)");
			$cancel = true;
		}

		$event = new PlayerEditBookEvent($this->player, $oldBook, $newBook, $action, $modifiedPages);
		if($cancel){
			$event->cancel();
		}

		$event->call();
		if($event->isCancelled()){
			return true;
		}

		$this->player->getInventory()->setItem($packet->inventorySlot, $event->getNewBook());

		return true;
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet) : bool{
		if($packet->cancelReason !== null){
			//TODO: make APIs for this to allow plugins to use this information
			return $this->player->onFormSubmit($packet->formId, null);
		}elseif($packet->formData !== null){
			try{
				$responseData = json_decode($packet->formData, true, self::MAX_FORM_RESPONSE_DEPTH, JSON_THROW_ON_ERROR);
			}catch(\JsonException $e){
				throw PacketHandlingException::wrap($e, "Failed to decode form response data");
			}
			return $this->player->onFormSubmit($packet->formId, $responseData);
		}else{
			throw new PacketHandlingException("Expected either formData or cancelReason to be set in ModalFormResponsePacket");
		}
	}

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $packet) : bool{
		return false; //TODO: GUI stuff
	}

	public function handleLabTable(LabTablePacket $packet) : bool{
		return false; //TODO
	}

	public function handleLecternUpdate(LecternUpdatePacket $packet) : bool{
		$pos = $packet->blockPosition;
		$chunkX = $pos->getX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $pos->getZ() >> Chunk::COORD_BIT_SIZE;
		$world = $this->player->getWorld();
		if(!$world->isChunkLoaded($chunkX, $chunkZ) || $world->isChunkLocked($chunkX, $chunkZ)){
			return false;
		}

		$lectern = $world->getBlockAt($pos->getX(), $pos->getY(), $pos->getZ());
		if($lectern instanceof Lectern && $this->player->canInteract($lectern->getPosition(), 15)){
			if(!$lectern->onPageTurn($packet->page)){
				$this->syncBlocksNearby($lectern->getPosition(), null);
			}
			return true;
		}

		return false;
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

	public function handleEmote(EmotePacket $packet) : bool{
		$this->player->emote($packet->getEmoteId());
		return true;
	}
}
