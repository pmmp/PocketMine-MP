<?php

declare(strict_types=1);

namespace pocketmine\event\player;


use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

/**
 * Called where player interacts with the entity (right click by the entity).
 */
class PlayerInteractEntityEvent extends PlayerEvent implements Cancellable{
	/** @var Entity */
	protected $entity;
	/** @var Item */
	protected $item;
	/** @var Vector3 */
	protected $clickPos;

	/**
	 * PlayerInteractEntityEvent constructor.
	 * @param Player  $player
	 * @param Entity  $entity
	 * @param Item    $item
	 * @param Vector3 $clickPos
	 */
	public function __construct(Player $player, Entity $entity, Item $item, Vector3 $clickPos){
		$this->player = $player;
		$this->entity = $entity;
		$this->item = $item;
		$this->clickPos = $clickPos;
	}

	/**
	 * @return Entity
	 */
	public function getEntity() : Entity{
		return $this->entity;
	}

	/**
	 * @return Item
	 */
	public function getItem() : Item{
		return $this->item;
	}

	/**
	 * @return Vector3
	 */
	public function getClickPos() : Vector3{
		return $this->clickPos;
	}
}
