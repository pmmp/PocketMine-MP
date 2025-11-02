<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\sound\PopSound;

class Composter extends Transparent
{
    public const MIN_FILL_LEVEL = 0;
    public const MAX_FILL_LEVEL = 8;

    private int $fillLevel = self::MIN_FILL_LEVEL;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w): void
    {
        $w->boundedIntAuto(self::MIN_FILL_LEVEL, self::MAX_FILL_LEVEL, $this->fillLevel);
    }

    public function getFillLevel(): int
    {
        return $this->fillLevel;
    }

    /** @return $this */
    public function setFillLevel(int $fillLevel): self
    {
        if ($fillLevel < self::MIN_FILL_LEVEL || $fillLevel > self::MAX_FILL_LEVEL) {
            throw new \InvalidArgumentException("Fill level must be in range " . self::MIN_FILL_LEVEL . " ... " . self::MAX_FILL_LEVEL);
        }
        $this->fillLevel = $fillLevel;
        return $this;
    }

    protected function withFillLevel(int $fillLevel): Block
    {
        $new = clone $this;
        $new->setFillLevel(max(self::MIN_FILL_LEVEL, min(self::MAX_FILL_LEVEL, $fillLevel)));
        return $new;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        $world = $this->position->getWorld();

        if ($this->fillLevel >= self::MAX_FILL_LEVEL) {
            $bm = VanillaItems::BONE_MEAL();
            $bm->setCount(mt_rand(1, 3));
            $returnedItems[] = $bm;
            $world->setBlock($this->position, $this->withFillLevel(self::MIN_FILL_LEVEL));
            $world->addSound($this->position->add(0.5, 0.5, 0.5), new PopSound());
            // spawn multiple happy villager particles (closer to vanilla)
            $centerCollect = $this->position->add(0.5, 1.0, 0.5);
            for ($i = 0; $i < 8; $i++) {
                $offset = new Vector3((mt_rand(-25, 25)) / 100.0, mt_rand(0, 20) / 100.0, (mt_rand(-25, 25)) / 100.0);
                $world->addParticle($centerCollect->add($offset->x, $offset->y, $offset->z), new HappyVillagerParticle());
            }

            return true;
        }


        // Compost chances per-item (explicit, readable mapping).
        // We still defensively check for the presence of the constant before adding it
        // to avoid runtime errors on older/newer builds that may lack certain items.
        $compostChances = [];

        $namedChances = [
            // ~30% group

            'BEETROOT_SEEDS' => 0.30,
            'DRIED_KELP' => 0.30,
            'GLOW_BERRIES' => 0.30,
            'MELON_SEEDS' => 0.30,
            'PUMPKIN_SEEDS' => 0.30,
            'SWEET_BERRIES' => 0.30,
            'WHEAT_SEEDS' => 0.30,
            'TORCHFLOWER_SEEDS' => 0.30,
            'SEAGRASS' => 0.30,
            'KELP' => 0.30,
            'PITCHER_POD' => 0.30,
            // ~50% group

            'MELON' => 0.50,
            'SUGAR' => 0.50,
            'CACTUS' => 0.50,
            // ~65% group

            'APPLE' => 0.65,
            'CARROT' => 0.65,
            'POTATO' => 0.65,
            'BEETROOT' => 0.65,
            'NETHER_WART' => 0.65,
            'SEA_PICKLE' => 0.65,
            'SHROOMLIGHT' => 0.65,
            'SPORE_BLOSSOM' => 0.65,
            "WHEAT" => 0.65,
            "PUMPKIN" => 0.65,
            // ~85% group
            // baked potato,cookie,bread,hay bale,
            'BAKED_POTATO' => 0.85,
            'COOKIE' => 0.85,
            'BREAD' => 0.85,
            'HAY_BALE' => 0.85,

           
        ];

        foreach ($namedChances as $name => $chance) {
            $fq = 'pocketmine\\item\\ItemTypeIds::' . $name;
            if (defined($fq)) {
                $compostChances[constant($fq)] = $chance;
            }
        }

        $typeId = $item->getTypeId();
        if (isset($compostChances[$typeId])) {
            if ($this->fillLevel < self::MAX_FILL_LEVEL) {
                $item->pop();
                $chance = (float) $compostChances[$typeId];
                $roll = mt_rand() / mt_getrandmax();
                if ($roll <= $chance) {
                    $newBlock = $this->withFillLevel($this->fillLevel + 1);
                    $world->setBlock($this->position, $newBlock);

                    // success: spawn a few happy villager particles and a pop sound
                    $centerSuccess = $this->position->add(0.5, 0.8, 0.5);
                    for ($i = 0; $i < 4; $i++) {
                        $offset = new Vector3((mt_rand(-15, 15)) / 100.0, mt_rand(0, 15) / 100.0, (mt_rand(-15, 15)) / 100.0);
                        $world->addParticle($centerSuccess->add($offset->x, $offset->y, $offset->z), new HappyVillagerParticle());
                    }
                    $world->addSound($this->position->add(0.5, 0.5, 0.5), new PopSound());

                    if ($newBlock instanceof Composter && $newBlock->getFillLevel() >= self::MAX_FILL_LEVEL) {
                        // additional celebration particles when it becomes full
                        $centerFull = $this->position->add(0.5, 1.0, 0.5);
                        for ($i = 0; $i < 6; $i++) {
                            $offset = new Vector3((mt_rand(-25, 25)) / 100.0, mt_rand(0, 25) / 100.0, (mt_rand(-25, 25)) / 100.0);
                            $world->addParticle($centerFull->add($offset->x, $offset->y, $offset->z), new HappyVillagerParticle());
                        }
                    }
                } else {
                    $world->addSound($this->position->add(0.5, 0.5, 0.5), new PopSound());
                }
            }
            return true;
        }

        return true;
    }
}
