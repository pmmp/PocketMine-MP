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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\utils\Binary;

class PacketPool{
	/** @var \SplFixedArray<DataPacket> */
	protected static $pool = null;

	public static function init() : void{
		static::$pool = new \SplFixedArray(256);

		static::registerPacket(new LoginPacket());
		static::registerPacket(new PlayStatusPacket());
		static::registerPacket(new ServerToClientHandshakePacket());
		static::registerPacket(new ClientToServerHandshakePacket());
		static::registerPacket(new DisconnectPacket());
		static::registerPacket(new ResourcePacksInfoPacket());
		static::registerPacket(new ResourcePackStackPacket());
		static::registerPacket(new ResourcePackClientResponsePacket());
		static::registerPacket(new TextPacket());
		static::registerPacket(new SetTimePacket());
		static::registerPacket(new StartGamePacket());
		static::registerPacket(new AddPlayerPacket());
		static::registerPacket(new AddEntityPacket());
		static::registerPacket(new RemoveEntityPacket());
		static::registerPacket(new AddItemEntityPacket());
		static::registerPacket(new TakeItemEntityPacket());
		static::registerPacket(new MoveEntityAbsolutePacket());
		static::registerPacket(new MovePlayerPacket());
		static::registerPacket(new RiderJumpPacket());
		static::registerPacket(new UpdateBlockPacket());
		static::registerPacket(new AddPaintingPacket());
		static::registerPacket(new ExplodePacket());
		static::registerPacket(new LevelSoundEventPacketV1());
		static::registerPacket(new LevelEventPacket());
		static::registerPacket(new BlockEventPacket());
		static::registerPacket(new EntityEventPacket());
		static::registerPacket(new MobEffectPacket());
		static::registerPacket(new UpdateAttributesPacket());
		static::registerPacket(new InventoryTransactionPacket());
		static::registerPacket(new MobEquipmentPacket());
		static::registerPacket(new MobArmorEquipmentPacket());
		static::registerPacket(new InteractPacket());
		static::registerPacket(new BlockPickRequestPacket());
		static::registerPacket(new EntityPickRequestPacket());
		static::registerPacket(new PlayerActionPacket());
		static::registerPacket(new EntityFallPacket());
		static::registerPacket(new HurtArmorPacket());
		static::registerPacket(new SetEntityDataPacket());
		static::registerPacket(new SetEntityMotionPacket());
		static::registerPacket(new SetEntityLinkPacket());
		static::registerPacket(new SetHealthPacket());
		static::registerPacket(new SetSpawnPositionPacket());
		static::registerPacket(new AnimatePacket());
		static::registerPacket(new RespawnPacket());
		static::registerPacket(new ContainerOpenPacket());
		static::registerPacket(new ContainerClosePacket());
		static::registerPacket(new PlayerHotbarPacket());
		static::registerPacket(new InventoryContentPacket());
		static::registerPacket(new InventorySlotPacket());
		static::registerPacket(new ContainerSetDataPacket());
		static::registerPacket(new CraftingDataPacket());
		static::registerPacket(new CraftingEventPacket());
		static::registerPacket(new GuiDataPickItemPacket());
		static::registerPacket(new AdventureSettingsPacket());
		static::registerPacket(new BlockEntityDataPacket());
		static::registerPacket(new PlayerInputPacket());
		static::registerPacket(new FullChunkDataPacket());
		static::registerPacket(new SetCommandsEnabledPacket());
		static::registerPacket(new SetDifficultyPacket());
		static::registerPacket(new ChangeDimensionPacket());
		static::registerPacket(new SetPlayerGameTypePacket());
		static::registerPacket(new PlayerListPacket());
		static::registerPacket(new SimpleEventPacket());
		static::registerPacket(new EventPacket());
		static::registerPacket(new SpawnExperienceOrbPacket());
		static::registerPacket(new ClientboundMapItemDataPacket());
		static::registerPacket(new MapInfoRequestPacket());
		static::registerPacket(new RequestChunkRadiusPacket());
		static::registerPacket(new ChunkRadiusUpdatedPacket());
		static::registerPacket(new ItemFrameDropItemPacket());
		static::registerPacket(new GameRulesChangedPacket());
		static::registerPacket(new CameraPacket());
		static::registerPacket(new BossEventPacket());
		static::registerPacket(new ShowCreditsPacket());
		static::registerPacket(new AvailableCommandsPacket());
		static::registerPacket(new CommandRequestPacket());
		static::registerPacket(new CommandBlockUpdatePacket());
		static::registerPacket(new CommandOutputPacket());
		static::registerPacket(new UpdateTradePacket());
		static::registerPacket(new UpdateEquipPacket());
		static::registerPacket(new ResourcePackDataInfoPacket());
		static::registerPacket(new ResourcePackChunkDataPacket());
		static::registerPacket(new ResourcePackChunkRequestPacket());
		static::registerPacket(new TransferPacket());
		static::registerPacket(new PlaySoundPacket());
		static::registerPacket(new StopSoundPacket());
		static::registerPacket(new SetTitlePacket());
		static::registerPacket(new AddBehaviorTreePacket());
		static::registerPacket(new StructureBlockUpdatePacket());
		static::registerPacket(new ShowStoreOfferPacket());
		static::registerPacket(new PurchaseReceiptPacket());
		static::registerPacket(new PlayerSkinPacket());
		static::registerPacket(new SubClientLoginPacket());
		static::registerPacket(new WSConnectPacket());
		static::registerPacket(new SetLastHurtByPacket());
		static::registerPacket(new BookEditPacket());
		static::registerPacket(new NpcRequestPacket());
		static::registerPacket(new PhotoTransferPacket());
		static::registerPacket(new ModalFormRequestPacket());
		static::registerPacket(new ModalFormResponsePacket());
		static::registerPacket(new ServerSettingsRequestPacket());
		static::registerPacket(new ServerSettingsResponsePacket());
		static::registerPacket(new ShowProfilePacket());
		static::registerPacket(new SetDefaultGameTypePacket());
		static::registerPacket(new RemoveObjectivePacket());
		static::registerPacket(new SetDisplayObjectivePacket());
		static::registerPacket(new SetScorePacket());
		static::registerPacket(new LabTablePacket());
		static::registerPacket(new UpdateBlockSyncedPacket());
		static::registerPacket(new MoveEntityDeltaPacket());
		static::registerPacket(new SetScoreboardIdentityPacket());
		static::registerPacket(new SetLocalPlayerAsInitializedPacket());
		static::registerPacket(new UpdateSoftEnumPacket());
		static::registerPacket(new NetworkStackLatencyPacket());
		static::registerPacket(new ScriptCustomEventPacket());
		static::registerPacket(new SpawnParticleEffectPacket());
		static::registerPacket(new AvailableEntityIdentifiersPacket());
		static::registerPacket(new LevelSoundEventPacketV2());
		static::registerPacket(new NetworkChunkPublisherUpdatePacket());
		static::registerPacket(new BiomeDefinitionListPacket());
		static::registerPacket(new LevelSoundEventPacket());
	}

	/**
	 * @param DataPacket $packet
	 */
	public static function registerPacket(DataPacket $packet) : void{
		static::$pool[$packet->pid()] = clone $packet;
	}

	/**
	 * @param int $pid
	 *
	 * @return DataPacket
	 */
	public static function getPacketById(int $pid) : DataPacket{
		return isset(static::$pool[$pid]) ? clone static::$pool[$pid] : new UnknownPacket();
	}

	/**
	 * @param string $buffer
	 *
	 * @return DataPacket
	 */
	public static function getPacket(string $buffer) : DataPacket{
		$offset = 0;
		$pk = static::getPacketById(Binary::readUnsignedVarInt($buffer, $offset));
		$pk->setBuffer($buffer, $offset);

		return $pk;
	}
}
