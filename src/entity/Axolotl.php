<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\world\Location;

class Axolotl extends WaterAnimal
{
    private const TAG_VARIANT = "Variant"; // TAG_Int
    private const TAG_BABY = "Baby"; // TAG_Byte

    /**
     * Variant index: 0..3 (pink, brown, gold, cyan)
     * @var int
     */
    private int $variant = 0;

    public static function getNetworkTypeId(): string
    {
        return 'minecraft:axolotl';
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        //Size: Height: 0.42 blocks Width: 0.75 blocks
        return new EntitySizeInfo(0.5, 0.5);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.86;
    }
    protected function getInitialGravity(): float
    {
        return 0.04;
    }

    public function initEntity(CompoundTag $nbt): void
    {
        $this->setMaxHealth(14);
        parent::initEntity($nbt);

        // reduce how long an axolotl can stay out of water (default mobs have 300 ticks)
        // Axolotls should suffocate relatively quickly when placed on land
        $this->setMaxAirSupplyTicks(100); // ~5 seconds of air (server ticks)

        $this->variant = $nbt->getInt(self::TAG_VARIANT, $this->variant);

        // read baby flag from NBT if present
        $isBaby = $nbt->getByte(self::TAG_BABY, $this->isBaby() ? 1 : 0) === 1;

        // Compatibility with vanilla summon/spawn NBT: some tools set entity_born or EntityBorn
        // to indicate the mob was recently born. If present, treat it as a baby.
        if ($nbt->getByte("entity_born", 0) === 1 || $nbt->getByte("EntityBorn", 0) === 1) {
            $isBaby = true;
        }

        $this->setBaby($isBaby);

        // Axolotls should only be able to "breathe" while underwater (unless they have
        // Water Breathing / Conduit). Override canBreathe() so Living's air tick logic
        // treats being on land as inability to breathe and causes suffocation damage.

        // If the axolotl is spawned on land, give it very little air so it will start taking
        // suffocation damage quickly and try to encourage players to place it into water.
        if (!$this->isUnderwater()) {
            $this->setAirSupplyTicks(0);
        }
    }

    public function canBreathe(): bool
    {
        // Axolotls can breathe when underwater, or if they have water-breathing effects.
        return $this->effectManager->has(\pocketmine\entity\effect\VanillaEffects::WATER_BREATHING()) || $this->effectManager->has(\pocketmine\entity\effect\VanillaEffects::CONDUIT_POWER()) || $this->isUnderwater();
    }

    public function getName(): string
    {
        return "Axolotl";
    }

    public function getPickedItem(): ?Item
    {
        return VanillaItems::AXOLOTL_SPAWN_EGG();
    }

    public function setVariant(int $variant): void
    {
        // clamp to 0..3
        if ($variant < 0) {
            $variant = 0;
        }
        if ($variant > 3) {
            $variant = 3;
        }
        $this->variant = $variant;
        $this->networkPropertiesDirty = true;
    }

    public function getVariant(): int
    {
        return $this->variant;
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setInt(self::TAG_VARIANT, $this->variant);
        $nbt->setByte(self::TAG_BABY, $this->isBaby() ? 1 : 0);
        return $nbt;
    }

    protected function syncNetworkData(
        \pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection $properties
    ): void {
        parent::syncNetworkData($properties);
        $properties->setInt(\pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties::VARIANT, $this->variant);
    }

    // more color variations
    public function getColorVariants(): array
    {
        return [
            "pink" => VanillaItems::AXOLOTL_SPAWN_EGG(),
            "brown" => VanillaItems::AXOLOTL_SPAWN_EGG(),
            "gold" => VanillaItems::AXOLOTL_SPAWN_EGG(),
            "cyan" => VanillaItems::AXOLOTL_SPAWN_EGG(),
            "blue" => VanillaItems::AXOLOTL_SPAWN_EGG(),
        ];
    }
}
