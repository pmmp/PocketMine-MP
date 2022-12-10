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
use pocketmine\block\Lectern;
use pocketmine\block\utils\SignText;
use pocketmine\entity\animation\ConsumingItemAnimation;
use pocketmine\entity\Attribute;
use pocketmine\entity\InvalidSkinException;
use pocketmine\event\player\PlayerEditBookEvent;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionException;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\VanillaItems;
use pocketmine\item\WritableBook;
use pocketmine\item\WritableBookPage;
use pocketmine\item\WrittenBook;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\convert\TypeConversionException;
use pocketmine\network\mcpe\convert\TypeConverter;
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
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\EmotePacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
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
use pocketmine\network\mcpe\protocol\RequestAbilityPacket;
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
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
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
use pocketmine\world\format\Chunk;
use function array_push;
use function base64_encode;
use function count;
use function fmod;
use function implode;
use function in_array;
use function is_bool;
use function is_infinite;
use function is_nan;
use function json_decode;
use function json_encode;
use function max;
use function mb_strlen;
use function microtime;
use function preg_match;
use function sprintf;
use function strlen;
use function strpos;
use function substr;
use function trim;
use const JSON_THROW_ON_ERROR;

/**
 * This handler handles packets related to general gameplay.
 */
class InGamePacketHandler extends PacketHandler{
	private const MAX_FORM_RESPONSE_DEPTH = 2; //modal/simple will be 1, custom forms 2 - they will never contain anything other than string|int|float|bool|null

	/** @var CraftingTransaction|null */
	protected $craftingTransaction = null;

	/** @var float */
	protected $lastRightClickTime = 0.0;
	/** @var UseItemTransactionData|null */
	protected $lastRightClickData = null;

	protected ?Vector3 $lastPlayerAuthInputPosition = null;
	protected ?float $lastPlayerAuthInputYaw = null;
	protected ?float $lastPlayerAuthInputPitch = null;
	protected ?int $lastPlayerAuthInputFlags = null;

	/** @var bool */
	public $forceMoveSync = false;

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
		$newPos = $rawPos->round(4)->subtract(0, 1.62, 0);

		if($this->forceMoveSync && $hasMoved){
			$curPos = $this->player->getLocation();

			if($newPos->distanceSquared($curPos) > 1){  //Tolerate up to 1 block to avoid problems with client-sided physics when spawning in blocks
				$this->session->getLogger()->debug("Got outdated pre-teleport movement, received " . $newPos . ", expected " . $curPos);
				//Still getting movements from before teleport, ignore them
				return false;
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
			$mismatch =
				($sneaking !== null && !$this->player->toggleSneak($sneaking)) |
				($sprinting !== null && !$this->player->toggleSprint($sprinting)) |
				($swimming !== null && !$this->player->toggleSwim($swimming)) |
				($gliding !== null && !$this->player->toggleGlide($gliding));
			if((bool) $mismatch){
				$this->player->sendData([$this->player]);
			}

			if($packet->hasFlag(PlayerAuthInputFlags::START_JUMPING)){
				$this->player->jump();
			}
		}

		if(!$this->forceMoveSync && $hasMoved){
			$this->lastPlayerAuthInputPosition = $rawPos;
			//TODO: this packet has WAYYYYY more useful information that we're not using
			$this->player->handleMovement($newPos);
		}

		$packetHandled = true;

		$useItemTransaction = $packet->getItemInteractionData();
		if($useItemTransaction !== null){
			if(!$this->handleUseItemTransaction($useItemTransaction->getTransactionData())){
				$packetHandled = false;
				$this->session->getLogger()->debug("Unhandled transaction in PlayerAuthInputPacket (type " . $useItemTransaction->getTransactionData()->getActionType() . ")");
			}else{
				$this->inventoryManager->syncMismatchedPredictedSlotChanges();
			}
		}

		$blockActions = $packet->getBlockActions();
		if($blockActions !== null){
			foreach($blockActions as $k => $blockAction){
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

		$this->inventoryManager->addPredictedSlotChanges($packet->trData->getActions());

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

		if($this->craftingTransaction === null){ //don't sync if we're waiting to complete a crafting transaction
			$this->inventoryManager->syncMismatchedPredictedSlotChanges();
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
				$networkInventoryAction->sourceType === NetworkInventoryAction::SOURCE_TODO || (
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
			}catch(TypeConversionException $e){
				$this->session->getLogger()->debug("Error unpacking inventory action: " . $e->getMessage());
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
			$this->player->setUsingItem(false);
			try{
				$this->craftingTransaction->execute();
			}catch(TransactionException $e){
				$this->session->getLogger()->debug("Failed to execute crafting transaction: " . $e->getMessage());
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

			$this->player->setUsingItem(false);
			$transaction = new InventoryTransaction($this->player, $actions);
			try{
				$transaction->execute();
			}catch(TransactionException $e){
				$logger = $this->session->getLogger();
				$logger->debug("Failed to execute inventory transaction: " . $e->getMessage());
				$logger->debug("Actions: " . json_encode($data->getActions()));
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
				if(!$this->player->interactBlock($vBlockPos, $data->getFace(), $clickPos)){
					$this->onFailedBlockAction($vBlockPos, $data->getFace());
				}
				return true;
			case UseItemTransactionData::ACTION_BREAK_BLOCK:
				$blockPos = $data->getBlockPosition();
				$vBlockPos = new Vector3($blockPos->getX(), $blockPos->getY(), $blockPos->getZ());
				if(!$this->player->breakBlock($vBlockPos)){
					$this->onFailedBlockAction($vBlockPos, null);
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
	 * Internal function used to execute rollbacks when an action fails on a block.
	 */
	private function onFailedBlockAction(Vector3 $blockPos, ?int $face) : void{
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
		return false; //TODO
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
					$this->onFailedBlockAction($pos, $face);
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

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool{
		return true; //this is a broken useless packet, so we don't use it
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

			$this->session->getLogger()->debug("Invalid sign update data: " . base64_encode($packet->nbt->getEncodedNbt()));
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
		$blockPosition = $packet->blockPosition;
		$block = $this->player->getWorld()->getBlockAt($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ());
		if($block instanceof ItemFrame && $block->getFramedItem() !== null){
			return $this->player->attackBlock(new Vector3($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ()), $block->getFacing());
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
		//TODO: break this up into book API things
		$oldBook = $this->player->getInventory()->getItem($packet->inventorySlot);
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
			return $this->player->onFormSubmit($packet->formId, self::stupid_json_decode($packet->formData, true));
		}else{
			throw new PacketHandlingException("Expected either formData or cancelReason to be set in ModalFormResponsePacket");
		}
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
				if($i === $len || ($raw[$i] === "," && !$inQuotes)){
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
			try{
				return json_decode($fixed, $assoc, self::MAX_FORM_RESPONSE_DEPTH, JSON_THROW_ON_ERROR);
			}catch(\JsonException $e){
				throw PacketHandlingException::wrap($e, "Failed to fix JSON (original: $json, modified: $fixed)");
			}
		}

		try{
			return json_decode($json, $assoc, self::MAX_FORM_RESPONSE_DEPTH, JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw PacketHandlingException::wrap($e);
		}
	}

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $packet) : bool{
		return false; //TODO: GUI stuff
	}

	public function handleLabTable(LabTablePacket $packet) : bool{
		return false; //TODO
	}

	public function handleLecternUpdate(LecternUpdatePacket $packet) : bool{
		if($packet->dropBook){
			//Drop book is handled with an interact event on use item transaction
			return true;
		}

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
				$this->onFailedBlockAction($lectern->getPosition(), null);
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

	public function handleRequestAbility(RequestAbilityPacket $packet) : bool{
		if($packet->getAbilityId() === RequestAbilityPacket::ABILITY_FLYING){
			$isFlying = $packet->getAbilityValue();
			if(!is_bool($isFlying)){
				throw new PacketHandlingException("Flying ability should always have a bool value");
			}
			if($isFlying !== $this->player->isFlying()){
				if(!$this->player->toggleFlight($isFlying)){
					$this->session->syncAbilities($this->player);
				}
			}

			return true;
		}

		return false;
	}
}
