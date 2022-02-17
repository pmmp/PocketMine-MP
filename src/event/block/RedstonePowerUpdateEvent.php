<?php

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

/**
 *
 */
class RedstonePowerUpdateEvent extends BlockEvent implements Cancellable
{
    use CancellableTrait;

    /**
     * @param Block $block
     * @param bool $setPowered
     */
    public function __construct(Block $block, private bool $setPowered)
    {
        parent::__construct($block);
    }

    /**
     * Power or unpower the redstone-block (That cannot be done if the event is cancelled)
     *
     * @param bool $setPowered
     */
    public function setPowered(bool $setPowered): void
    {
        $this->setPowered = $setPowered;
    }

    /**
     * @return bool
     */
    public function getPowered(): bool
    {
        return $this->setPowered;
    }
}