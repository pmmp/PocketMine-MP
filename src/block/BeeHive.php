<?php

namespace pocketmine\block;

use pocketmine\block\Opaque;
use pocketmine\block\tile\Tile;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\GlassBottle;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use pocketmine\block\tile\TileFactory;

class BeeHive extends Opaque implements HorizontalFacing
{
    use FacesOppositePlacingPlayerTrait {
        describeBlockOnlyState as describeFacingState;
    }

    protected int $honeyLevel = 0;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w): void
    {
        $this->describeFacingState($w);
        $w->boundedIntAuto(0, 5, $this->honeyLevel);
    }

    public function getHoneyLevel(): int
    {
        return $this->honeyLevel;
    }

    public function setHoneyLevel(int $honeyLevel): self
    {
        if ($honeyLevel < 0 || $honeyLevel > 5) {
            throw new \InvalidArgumentException("honeyLevel must be between 0 and 5");
        }
        $this->honeyLevel = $honeyLevel;
        return $this;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        // If player used a glass bottle on a full hive (honey level 5), give honey bottle and reset level
        if ($item instanceof GlassBottle) {
            if ($this->getHoneyLevel() >= 5) {
                // consume bottle for non-creative players
                if ($player !== null && $player->hasFiniteResources()) {
                    $item->pop();
                }

                // give honey bottle (will be placed into the player's inventory by the caller)
                $returnedItems[] = VanillaItems::HONEY_BOTTLE();

                // reset honey level
                $this->position->getWorld()->setBlock($this->position, $this->setHoneyLevel(0));

                // indicate we handled the interaction
                return true;
            }

            // not enough honey yet -> let item-handling (if any) proceed
            return false;
        }

        if ($player->isCreative() && $player !== null && $player->isSneaking()) {
            $this->position->getWorld()->setBlock($this->position, $this->setHoneyLevel(($this->getHoneyLevel() + 1) % 6));
            return true;
        }

        // otherwise show the current honey level to player (do not modify state)
        $player?->sendTip("Honey Level: " . $this->getHoneyLevel());
        return true;
    }

    protected function writeDefaultTileData(CompoundTag $tag): void
    {
        // Use TileFactory to get the proper save id for this tile class
        $tag->setString(Tile::TAG_ID, TileFactory::getInstance()->getSaveId(self::class));
        if ($tag->getTag("Occupants") === null) {
            $tag->setTag(
                "Occupants",
                CompoundTag::create()
                    ->setTag("Occupants", new ListTag([
                        CompoundTag::create()
                            ->setTag("ActorIdentifier", new StringTag("minecraft:bee<>")) // erm Mojang???
                            ->setTag("SaveData", CompoundTag::create())
                            ->setInt("TicksLeftToStay", 0)
                    ]))
            );
        }

        if ($tag->getTag("ShouldSpawnBees") === null) {
            $tag->setTag("ShouldSpawnBees", new ByteTag(0));
        }
    }
}
