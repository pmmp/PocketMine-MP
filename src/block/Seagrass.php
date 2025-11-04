<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\utils\Waterloggable;
use pocketmine\block\utils\WaterloggedTrait;
use pocketmine\math\Vector3;
use pocketmine\block\Liquid;
use pocketmine\world\BlockTransaction;
use pocketmine\item\Item;
use pocketmine\player\Player;

class Seagrass extends Flowable implements Waterloggable
{
    use WaterloggedTrait;
  

    public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock): bool
    {
        // Seagrass occupies water spaces (allow placement into liquid replacement blocks)
        return $blockReplace instanceof Liquid ? $blockReplace->canBeReplaced() : false;
    }

    protected function describeBlockOnlyState(\pocketmine\data\runtime\RuntimeDataDescriber $w): void
    {
        // No other state bits, include waterlogged
        $this->describeWaterloggedState($w);
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if ($blockReplace instanceof Liquid) {
            $b = clone $this;
            $b->setWaterlogged(true);
            $tx->addBlock($blockReplace->position, $b);
            return true;
        }

        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }
}
