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

use pocketmine\network\mcpe\protocol\AddBehaviorTreePacket;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\AddItemEntityPacket;
use pocketmine\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\AutomationClientConnectPacket;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\AvailableEntityIdentifiersPacket;
use pocketmine\network\mcpe\protocol\BiomeDefinitionListPacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\CameraPacket;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\ClientToServerHandshakePacket;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\CommandOutputPacket;
use pocketmine\network\mcpe\protocol\CommandRequestPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\EntityFallPacket;
use pocketmine\network\mcpe\protocol\EntityPickRequestPacket;
use pocketmine\network\mcpe\protocol\EventPacket;
use pocketmine\network\mcpe\protocol\ExplodePacket;
use pocketmine\network\mcpe\protocol\FullChunkDataPacket;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\GuiDataPickItemPacket;
use pocketmine\network\mcpe\protocol\HurtArmorPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\protocol\LabTablePacket;
use pocketmine\network\mcpe\protocol\LecternUpdatePacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacketV1;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacketV2;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\MoveEntityAbsolutePacket;
use pocketmine\network\mcpe\protocol\MoveEntityDeltaPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkChunkPublisherUpdatePacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\NpcRequestPacket;
use pocketmine\network\mcpe\protocol\PhotoTransferPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerHotbarPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\PurchaseReceiptPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkDataPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\ResourcePackDataInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\RespawnPacket;
use pocketmine\network\mcpe\protocol\RiderJumpPacket;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\network\mcpe\protocol\ServerToClientHandshakePacket;
use pocketmine\network\mcpe\protocol\SetCommandsEnabledPacket;
use pocketmine\network\mcpe\protocol\SetDefaultGameTypePacket;
use pocketmine\network\mcpe\protocol\SetDifficultyPacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\network\mcpe\protocol\SetHealthPacket;
use pocketmine\network\mcpe\protocol\SetLastHurtByPacket;
use pocketmine\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetScoreboardIdentityPacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\ShowCreditsPacket;
use pocketmine\network\mcpe\protocol\ShowProfilePacket;
use pocketmine\network\mcpe\protocol\ShowStoreOfferPacket;
use pocketmine\network\mcpe\protocol\SimpleEventPacket;
use pocketmine\network\mcpe\protocol\SpawnExperienceOrbPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\network\mcpe\protocol\StructureBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\SubClientLoginPacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockSyncedPacket;
use pocketmine\network\mcpe\protocol\UpdateEquipPacket;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\network\mcpe\protocol\VideoStreamConnectPacket;

/**
 * Handlers are attached to sessions to handle packets received from their associated clients. A handler
 * is mutable and may be removed/replaced at any time.
 *
 * This class is an automatically generated stub. Do not edit it manually.
 */
abstract class SessionHandler{

	public function setUp() : void{

	}

	public function handleLogin(LoginPacket $packet) : bool{
		return false;
	}

	public function handlePlayStatus(PlayStatusPacket $packet) : bool{
		return false;
	}

	public function handleServerToClientHandshake(ServerToClientHandshakePacket $packet) : bool{
		return false;
	}

	public function handleClientToServerHandshake(ClientToServerHandshakePacket $packet) : bool{
		return false;
	}

	public function handleDisconnect(DisconnectPacket $packet) : bool{
		return false;
	}

	public function handleResourcePacksInfo(ResourcePacksInfoPacket $packet) : bool{
		return false;
	}

	public function handleResourcePackStack(ResourcePackStackPacket $packet) : bool{
		return false;
	}

	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool{
		return false;
	}

	public function handleText(TextPacket $packet) : bool{
		return false;
	}

	public function handleSetTime(SetTimePacket $packet) : bool{
		return false;
	}

	public function handleStartGame(StartGamePacket $packet) : bool{
		return false;
	}

	public function handleAddPlayer(AddPlayerPacket $packet) : bool{
		return false;
	}

	public function handleAddEntity(AddEntityPacket $packet) : bool{
		return false;
	}

	public function handleRemoveEntity(RemoveEntityPacket $packet) : bool{
		return false;
	}

	public function handleAddItemEntity(AddItemEntityPacket $packet) : bool{
		return false;
	}

	public function handleTakeItemEntity(TakeItemEntityPacket $packet) : bool{
		return false;
	}

	public function handleMoveEntityAbsolute(MoveEntityAbsolutePacket $packet) : bool{
		return false;
	}

	public function handleMovePlayer(MovePlayerPacket $packet) : bool{
		return false;
	}

	public function handleRiderJump(RiderJumpPacket $packet) : bool{
		return false;
	}

	public function handleUpdateBlock(UpdateBlockPacket $packet) : bool{
		return false;
	}

	public function handleAddPainting(AddPaintingPacket $packet) : bool{
		return false;
	}

	public function handleExplode(ExplodePacket $packet) : bool{
		return false;
	}

	public function handleLevelSoundEventPacketV1(LevelSoundEventPacketV1 $packet) : bool{
		return false;
	}

	public function handleLevelEvent(LevelEventPacket $packet) : bool{
		return false;
	}

	public function handleBlockEvent(BlockEventPacket $packet) : bool{
		return false;
	}

	public function handleEntityEvent(EntityEventPacket $packet) : bool{
		return false;
	}

	public function handleMobEffect(MobEffectPacket $packet) : bool{
		return false;
	}

	public function handleUpdateAttributes(UpdateAttributesPacket $packet) : bool{
		return false;
	}

	public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool{
		return false;
	}

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool{
		return false;
	}

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool{
		return false;
	}

	public function handleInteract(InteractPacket $packet) : bool{
		return false;
	}

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool{
		return false;
	}

	public function handleEntityPickRequest(EntityPickRequestPacket $packet) : bool{
		return false;
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool{
		return false;
	}

	public function handleEntityFall(EntityFallPacket $packet) : bool{
		return false;
	}

	public function handleHurtArmor(HurtArmorPacket $packet) : bool{
		return false;
	}

	public function handleSetEntityData(SetEntityDataPacket $packet) : bool{
		return false;
	}

	public function handleSetEntityMotion(SetEntityMotionPacket $packet) : bool{
		return false;
	}

	public function handleSetEntityLink(SetEntityLinkPacket $packet) : bool{
		return false;
	}

	public function handleSetHealth(SetHealthPacket $packet) : bool{
		return false;
	}

	public function handleSetSpawnPosition(SetSpawnPositionPacket $packet) : bool{
		return false;
	}

	public function handleAnimate(AnimatePacket $packet) : bool{
		return false;
	}

	public function handleRespawn(RespawnPacket $packet) : bool{
		return false;
	}

	public function handleContainerOpen(ContainerOpenPacket $packet) : bool{
		return false;
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool{
		return false;
	}

	public function handlePlayerHotbar(PlayerHotbarPacket $packet) : bool{
		return false;
	}

	public function handleInventoryContent(InventoryContentPacket $packet) : bool{
		return false;
	}

	public function handleInventorySlot(InventorySlotPacket $packet) : bool{
		return false;
	}

	public function handleContainerSetData(ContainerSetDataPacket $packet) : bool{
		return false;
	}

	public function handleCraftingData(CraftingDataPacket $packet) : bool{
		return false;
	}

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool{
		return false;
	}

	public function handleGuiDataPickItem(GuiDataPickItemPacket $packet) : bool{
		return false;
	}

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool{
		return false;
	}

	public function handleBlockEntityData(BlockEntityDataPacket $packet) : bool{
		return false;
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool{
		return false;
	}

	public function handleFullChunkData(FullChunkDataPacket $packet) : bool{
		return false;
	}

	public function handleSetCommandsEnabled(SetCommandsEnabledPacket $packet) : bool{
		return false;
	}

	public function handleSetDifficulty(SetDifficultyPacket $packet) : bool{
		return false;
	}

	public function handleChangeDimension(ChangeDimensionPacket $packet) : bool{
		return false;
	}

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool{
		return false;
	}

	public function handlePlayerList(PlayerListPacket $packet) : bool{
		return false;
	}

	public function handleSimpleEvent(SimpleEventPacket $packet) : bool{
		return false;
	}

	public function handleEvent(EventPacket $packet) : bool{
		return false;
	}

	public function handleSpawnExperienceOrb(SpawnExperienceOrbPacket $packet) : bool{
		return false;
	}

	public function handleClientboundMapItemData(ClientboundMapItemDataPacket $packet) : bool{
		return false;
	}

	public function handleMapInfoRequest(MapInfoRequestPacket $packet) : bool{
		return false;
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool{
		return false;
	}

	public function handleChunkRadiusUpdated(ChunkRadiusUpdatedPacket $packet) : bool{
		return false;
	}

	public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool{
		return false;
	}

	public function handleGameRulesChanged(GameRulesChangedPacket $packet) : bool{
		return false;
	}

	public function handleCamera(CameraPacket $packet) : bool{
		return false;
	}

	public function handleBossEvent(BossEventPacket $packet) : bool{
		return false;
	}

	public function handleShowCredits(ShowCreditsPacket $packet) : bool{
		return false;
	}

	public function handleAvailableCommands(AvailableCommandsPacket $packet) : bool{
		return false;
	}

	public function handleCommandRequest(CommandRequestPacket $packet) : bool{
		return false;
	}

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool{
		return false;
	}

	public function handleCommandOutput(CommandOutputPacket $packet) : bool{
		return false;
	}

	public function handleUpdateTrade(UpdateTradePacket $packet) : bool{
		return false;
	}

	public function handleUpdateEquip(UpdateEquipPacket $packet) : bool{
		return false;
	}

	public function handleResourcePackDataInfo(ResourcePackDataInfoPacket $packet) : bool{
		return false;
	}

	public function handleResourcePackChunkData(ResourcePackChunkDataPacket $packet) : bool{
		return false;
	}

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool{
		return false;
	}

	public function handleTransfer(TransferPacket $packet) : bool{
		return false;
	}

	public function handlePlaySound(PlaySoundPacket $packet) : bool{
		return false;
	}

	public function handleStopSound(StopSoundPacket $packet) : bool{
		return false;
	}

	public function handleSetTitle(SetTitlePacket $packet) : bool{
		return false;
	}

	public function handleAddBehaviorTree(AddBehaviorTreePacket $packet) : bool{
		return false;
	}

	public function handleStructureBlockUpdate(StructureBlockUpdatePacket $packet) : bool{
		return false;
	}

	public function handleShowStoreOffer(ShowStoreOfferPacket $packet) : bool{
		return false;
	}

	public function handlePurchaseReceipt(PurchaseReceiptPacket $packet) : bool{
		return false;
	}

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool{
		return false;
	}

	public function handleSubClientLogin(SubClientLoginPacket $packet) : bool{
		return false;
	}

	public function handleAutomationClientConnect(AutomationClientConnectPacket $packet) : bool{
		return false;
	}

	public function handleSetLastHurtBy(SetLastHurtByPacket $packet) : bool{
		return false;
	}

	public function handleBookEdit(BookEditPacket $packet) : bool{
		return false;
	}

	public function handleNpcRequest(NpcRequestPacket $packet) : bool{
		return false;
	}

	public function handlePhotoTransfer(PhotoTransferPacket $packet) : bool{
		return false;
	}

	public function handleModalFormRequest(ModalFormRequestPacket $packet) : bool{
		return false;
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet) : bool{
		return false;
	}

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $packet) : bool{
		return false;
	}

	public function handleServerSettingsResponse(ServerSettingsResponsePacket $packet) : bool{
		return false;
	}

	public function handleShowProfile(ShowProfilePacket $packet) : bool{
		return false;
	}

	public function handleSetDefaultGameType(SetDefaultGameTypePacket $packet) : bool{
		return false;
	}

	public function handleRemoveObjective(RemoveObjectivePacket $packet) : bool{
		return false;
	}

	public function handleSetDisplayObjective(SetDisplayObjectivePacket $packet) : bool{
		return false;
	}

	public function handleSetScore(SetScorePacket $packet) : bool{
		return false;
	}

	public function handleLabTable(LabTablePacket $packet) : bool{
		return false;
	}

	public function handleUpdateBlockSynced(UpdateBlockSyncedPacket $packet) : bool{
		return false;
	}

	public function handleMoveEntityDelta(MoveEntityDeltaPacket $packet) : bool{
		return false;
	}

	public function handleSetScoreboardIdentity(SetScoreboardIdentityPacket $packet) : bool{
		return false;
	}

	public function handleSetLocalPlayerAsInitialized(SetLocalPlayerAsInitializedPacket $packet) : bool{
		return false;
	}

	public function handleUpdateSoftEnum(UpdateSoftEnumPacket $packet) : bool{
		return false;
	}

	public function handleNetworkStackLatency(NetworkStackLatencyPacket $packet) : bool{
		return false;
	}

	public function handleScriptCustomEvent(ScriptCustomEventPacket $packet) : bool{
		return false;
	}

	public function handleSpawnParticleEffect(SpawnParticleEffectPacket $packet) : bool{
		return false;
	}

	public function handleAvailableEntityIdentifiers(AvailableEntityIdentifiersPacket $packet) : bool{
		return false;
	}

	public function handleLevelSoundEventPacketV2(LevelSoundEventPacketV2 $packet) : bool{
		return false;
	}

	public function handleNetworkChunkPublisherUpdate(NetworkChunkPublisherUpdatePacket $packet) : bool{
		return false;
	}

	public function handleBiomeDefinitionList(BiomeDefinitionListPacket $packet) : bool{
		return false;
	}

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool{
		return false;
	}

	public function handleLecternUpdate(LecternUpdatePacket $packet) : bool{
		return false;
	}

	public function handleVideoStreamConnect(VideoStreamConnectPacket $packet) : bool{
		return false;
	}
}
