<?php

declare(strict_types=1);

namespace pocketmine\entity\object;

use pocketmine\world\Position;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\ArmorInventory;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\player\Player;
use pocketmine\entity\Location;
use pocketmine\equipment\ArmorStandEntityEquipment;
use pocketmine\utils\EquipmentSlot;
use pocketmine\world\particle\ArmorStandDestroyParticle;
use pocketmine\world\sound\ArmorStandBreakSound;
use pocketmine\world\sound\ArmorStandFallSound;
use pocketmine\world\sound\ArmorStandHitSound;
use pocketmine\world\sound\ArmorStandPlaceSound;
use RuntimeException;
use function array_merge;
use function min;


class ArmorStand extends Living
{
    public const NETWORK_ID = EntityIds::ARMOR_STAND;

    public const TAG_MAINHAND = "Mainhand";
    public const TAG_OFFHAND = "Offhand";
    public const TAG_POSE_INDEX = "PoseIndex";
    public const TAG_ARMOR = "Armor";

    /** @var ArmorStandEntityEquipment */
    protected $equipment;

    public const WIDTH = 0.5;
    public const HEIGHT = 1.975;

    protected const GRAVITY = 0.04;
    private $properties = [];

    private ?Item $item = null; // Nullable dan di-set null secara default

    // metadata handled via entity network properties
    protected $vibrateTimer = 0;
    private bool $isVibrating = false;
    private Inventory $inventory;
    private int $pose = 0;

    protected Location $location;
    /**
     * @return ArmorStandEntityEquipment
     */
    public function getEquipment(): ArmorStandEntityEquipment
    {
        return $this->equipment;
    }

    public static function getNetworkTypeId(): string
    {
        return self::NETWORK_ID;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(self::HEIGHT, self::WIDTH);
    }

    protected function getInitialGravity(): float
    {
        return self::GRAVITY;
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setMaxHealth(6);
        $this->setNoClientPredictions(true);

        parent::initEntity($nbt);

        $this->equipment = new ArmorStandEntityEquipment($this);

        if ($nbt->getTag(self::TAG_ARMOR) instanceof ListTag) {
            $armors = $nbt->getListTag(self::TAG_ARMOR);

            /** @var CompoundTag $armor */
            foreach ($armors as $armor) {
                $slot = $armor->getByte("Slot", 0);

                $this->armorInventory->setItem($slot, Item::nbtDeserialize($armor));
            }
        }

        if ($nbt->getTag(self::TAG_MAINHAND) instanceof CompoundTag) {
            $this->equipment->setItemInHand(Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_MAINHAND)));
        }
        if ($nbt->getTag(self::TAG_OFFHAND) instanceof CompoundTag) {
            $this->equipment->setOffhandItem(Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_OFFHAND)));
        }

    // Allow pose indices up to 13 (wiki maps 13+ to hero pose)
    $this->setPose(min($nbt->getInt(self::TAG_POSE_INDEX, 0), 13));
    $this->getNetworkProperties()->setString(EntityMetadataProperties::INTERACTIVE_TAG, "armorstand.change.pose");
    $this->networkPropertiesDirty = true;
    }

    public function setPose(int $pose): void
    {
        $this->pose = $pose;
        // also update metadata so clients receive the change
        $this->getNetworkProperties()->setByte(EntityMetadataProperties::ARMOR_STAND_POSE_INDEX, $pose);
        $this->networkPropertiesDirty = true;
        // immediate sync so clients see the pose change right away
        $this->sendData(null, $this->getDirtyNetworkData());
    }

    public function getPose(): int
    {
        return $this->pose;
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        // Ensure we read the item the player currently holds so we don't call methods on a null value
        $this->item = $player->getInventory()->getItemInHand();

        if ($player->isSneaking()) {
            // cycle through 0..13 inclusive (14 states) so hero pose (13+) can be reached
            $this->setPose(($this->getPose() + 1) % 14);
            return true;
        }
        if ($this->getPosition()->isValid() && !$player->isSpectator()) {
            $targetSlot = EquipmentSlot::MAINHAND;
            $isArmorSlot = false;
            if ($this->item instanceof Armor) {
                $targetSlot = $this->item->getArmorSlot();
                $isArmorSlot = true;
            } elseif ($this->item->getTypeId() === ItemTypeIds::fromBlockTypeId(BlockTypeIds::MOB_HEAD) || $this->item->getTypeId() === ItemTypeIds::fromBlockTypeId(BlockTypeIds::PUMPKIN)) {
                $targetSlot = ArmorInventory::SLOT_HEAD;
                $isArmorSlot = true;
            } elseif ($this->item->isNull()) {
                $clickOffset = $clickPos->y - $this->location->y;
                if ($clickOffset >= 0.1 && $clickOffset < 0.55 && !$this->armorInventory->getItem(ArmorInventory::SLOT_FEET)->isNull()) {
                    $targetSlot = ArmorInventory::SLOT_FEET;
                    $isArmorSlot = true;
                } elseif ($clickOffset >= 0.9 && $clickOffset < 1.6 && !$this->armorInventory->getItem(ArmorInventory::SLOT_CHEST)->isNull()) {
                    $targetSlot = ArmorInventory::SLOT_CHEST;
                    $isArmorSlot = true;
                } elseif ($clickOffset >= 0.4 && $clickOffset < 1.2 && !$this->armorInventory->getItem(ArmorInventory::SLOT_LEGS)->isNull()) {
                    $targetSlot = ArmorInventory::SLOT_LEGS;
                    $isArmorSlot = true;
                } elseif ($clickOffset >= 1.6 && !$this->armorInventory->getItem(ArmorInventory::SLOT_HEAD)->isNull()) {
                    $targetSlot = ArmorInventory::SLOT_HEAD;
                    $isArmorSlot = true;
                }
            }
            $this->getWorld()->addSound($this->getPosition(), new ArmorStandPlaceSound());
            $this->tryChangeEquipment($player, $this->item, $targetSlot, $isArmorSlot);
            return true;
        }
        return false;
    }

    protected function tryChangeEquipment(Player $player, Item $targetItem, int $slot, bool $isArmorSlot = false): void
    {
        $sourceItem = $isArmorSlot ? $this->armorInventory->getItem($slot) : $this->equipment->getItem($slot);

        // Place the item onto the armor stand (use a clone so we don't keep player's item instance)
        $placed = (clone $targetItem)->setCount(1);
        if ($isArmorSlot) {
            $this->armorInventory->setItem($slot, $placed);
        } else {
            $this->equipment->setItem($slot, $placed);
        }

        // Adjust player's held item properly (PlayerInventory returns a copy), to avoid duping we must modify the inventory
        if ($player->isSurvival()) {
            $held = $player->getInventory()->getItemInHand();
            if (!$held->isNull()) {
                if ($held->getCount() > 1) {
                    $held->setCount($held->getCount() - 1);
                    $player->getInventory()->setItemInHand($held);
                } else {
                    // remove the held item
                    $player->getInventory()->setItemInHand(VanillaItems::AIR());
                }
            }
        }

        // Return the previous item from the armor stand back to the player (if any)
        if (!$sourceItem->isNull()) {
            $player->getInventory()->addItem($sourceItem);
        }

        $this->equipment->sendContents($player);
        $this->sendContents($player);
    }

    public function sendContents($target): void
    {
        if ($target instanceof Player) {
            $target = [$target];
        }

        $pk = new MobArmorEquipmentPacket();
        $pk->actorRuntimeId = $this->getId();
        $pk->head = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->armorInventory->getHelmet()));
        $pk->chest = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->armorInventory->getChestplate()));
        $pk->legs = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->armorInventory->getLeggings()));
        $pk->feet = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->armorInventory->getBoots()));
        $pk->body = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet(VanillaBlocks::AIR()->asItem()));

        foreach ($target as $player) {
            if ($player === $this->armorInventory->getHolder()) {
                $pk2 = new InventoryContentPacket();
                $pk2->windowId = $player->getCurrentWindow($this);
                $pk2->items = array_map(function($i) {
                    return ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($i));
                }, $this->inventory->getContents(true));
                $player->getNetworkSession()->sendDataPacket($pk2);
            } else {
                $player->getNetworkSession()->sendDataPacket($pk);
            }
        }
    }

    protected function onHitGround(): ?float
    {
        $this->getWorld()->addSound($this->getPosition(), new ArmorStandFallSound());
        return null;
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();

        // Main hand / Offhand from equipment (serialize AIR when empty)
        if ($this->equipment instanceof ArmorStandEntityEquipment) {
            $main = $this->equipment->getItemInHand();
            $nbt->setTag(self::TAG_MAINHAND, $main->isNull() ? VanillaItems::AIR()->nbtSerialize(-1) : $main->nbtSerialize(-1));

            $off = $this->equipment->getOffhandItem();
            $nbt->setTag(self::TAG_OFFHAND, $off->isNull() ? VanillaItems::AIR()->nbtSerialize(-1) : $off->nbtSerialize(-1));
        }

        // Armor items: ensure we never call nbtSerialize on a null Item
        $armorTag = new ListTag([], NBT::TAG_Compound);
        for ($i = 0; $i < 4; $i++) {
            $item = $this->armorInventory !== null ? $this->armorInventory->getItem($i) : null;
            if ($item === null || $item->isNull()) {
                $armorTag->push(VanillaItems::AIR()->nbtSerialize($i));
            } else {
                $armorTag->push($item->nbtSerialize($i));
            }
        }
        $nbt->setTag(self::TAG_ARMOR, $armorTag);

        $nbt->setInt(self::TAG_POSE_INDEX, $this->getPose());

        return $nbt;
    }


    public function attack(EntityDamageEvent $source): void
    {
        parent::attack($source);
        if ($source instanceof EntityDamageByChildEntityEvent && $source->getChild() instanceof Arrow) {
            $this->kill();
        }

        if ($source->getCause() === EntityDamageEvent::CAUSE_CONTACT) { // cactus
            $source->cancel();
        }

        if (!$source->isCancelled()) {
            // Set vibrating flag and reset timer (don't accumulate indefinitely)
            $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::VIBRATING, true);
            $this->networkPropertiesDirty = true;
            $this->vibrateTimer = 30; // ticks
            $this->isVibrating = true;
            // send metadata immediately so clients start vibrating right away
            $this->sendData(null, $this->getDirtyNetworkData());
            // Prevent armor stands from being knocked back / moved when hit
            $this->setMotion(new Vector3(0, 0, 0));
        }
    }

    protected function doHitAnimation(): void
    {
        $this->getWorld()->addSound($this->getPosition(), new ArmorStandHitSound());
    }

    public function startDeathAnimation(): void
    {
        $this->getWorld()->addSound($this->getPosition(), new ArmorStandBreakSound());
        $this->getWorld()->addParticle($this->getPosition(), new ArmorStandDestroyParticle());
    }

    protected function onDeathUpdate(int $tickDiff): bool
    {
        return true;
    }

    protected function sendSpawnPacket(Player $player): void
    {
        parent::sendSpawnPacket($player);

        $this->equipment->sendContents($player);
    }

    public function getName(): string
    {
        return "ArmorStand";
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        // Handle vibrating timer using the network properties (avoid mixing local $properties)
        if ($this->isVibrating) {
            $this->vibrateTimer -= $tickDiff;
            if ($this->vibrateTimer <= 0) {
                $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::VIBRATING, false);
                $this->networkPropertiesDirty = true;
                $this->vibrateTimer = 0;
                $this->isVibrating = false;
                    // send metadata immediately so clients stop vibrating
                    $this->sendData(null, $this->getDirtyNetworkData());
            }
        }

        return $hasUpdate;
    }

    public function getDataFlag(int $propertyId, int $flagId): bool
    {
        return (((int) $this->getPropertyValue($propertyId, -1)) & (1 << $flagId)) > 0;
    }

    public function getGenericFlag(int $flagId): bool
    {
        return $this->getDataFlag($flagId >= 64 ? EntityMetadataProperties::FLAGS2 : EntityMetadataProperties::FLAGS, $flagId % 64);
    }

    public function getPropertyValue(int $key, int $type)
    {
        if ($type !== -1) {
            $this->checkType($key, $type);
        }
        return isset($this->properties[$key]) ? $this->properties[$key][1] : null;
    }

    private function checkType(int $key, int $type): void
    {
        if (isset($this->properties[$key]) and $this->properties[$key][0] !== $type) {
            throw new RuntimeException("Expected type $type, but have " . $this->properties[$key][0]);
        }
    }

    protected function onDeath(): void
    {
        parent::onDeath();

        $world = $this->getWorld();
        if ($world === null) {
            return;
        }

        if ($this->armorInventory !== null) {
            foreach ($this->armorInventory->getContents() as $item) {
                if (!$item->isNull()) {
                    $world->dropItem($this->getPosition(), $item);
                }
            }
        }

        if ($this->equipment !== null) {
            $mainhandItem = $this->equipment->getItemInHand();
            if (!$mainhandItem->isNull()) {
                $world->dropItem($this->getPosition(), $mainhandItem);
            }

            $offhandItem = $this->equipment->getOffhandItem();
            if (!$offhandItem->isNull()) {
                $world->dropItem($this->getPosition(), $offhandItem);
            }
        }
    }

    public function getPosition(): Position
    {
        return $this->location->asPosition();
    }
}
