<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\entity\projectile\WindCharge as WindChargeEntity;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\WindBurstParticle;
use pocketmine\world\sound\ThrowSound;
use pocketmine\world\sound\WindChargeShootSound;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class WindCharge extends ProjectileItem
{
	private const FORWARD_MULTIPLIER = 0.60;
	private const H_SPEED_CLAMP = 1.20;
	private const PRESERVE_FACTOR = 0.2;

	public function __construct(ItemIdentifier $identifier, string $name = "Wind Charge")
	{
		parent::__construct($identifier, $name);
	}

	public function getCooldownTicks(): int
	{
		return 10;
	}

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
		if($player->hasItemCooldown($this)){
			return ItemUseResult::FAIL;
		}

		$dir = $player->getDirectionVector();
		$force = $this->getThrowForce();
		$currentMotion = $player->getMotion();

		$forward = new Vector3($dir->x, 0.0, $dir->z);
		if($forward->lengthSquared() > 0.0){
			$forward = $forward->normalize();
		}

		$hNewX = $currentMotion->x * self::PRESERVE_FACTOR + $forward->x * ($force * self::FORWARD_MULTIPLIER);
		$hNewZ = $currentMotion->z * self::PRESERVE_FACTOR + $forward->z * ($force * self::FORWARD_MULTIPLIER);

		$lookDownFactor = max(0.0, -$dir->y);
		$horizontalSuppression = 1.0 - ($lookDownFactor * 0.95);
		$hNewX *= $horizontalSuppression;
		$hNewZ *= $horizontalSuppression;

		$horizontalLength = sqrt($hNewX * $hNewX + $hNewZ * $hNewZ);
		if($horizontalLength > self::H_SPEED_CLAMP && $horizontalLength > 0.0){
			$scale = self::H_SPEED_CLAMP / $horizontalLength;
			$hNewX *= $scale;
			$hNewZ *= $scale;
		}

		$verticalVelocity = 1.2;

		$player->setMotion(new Vector3($hNewX, $verticalVelocity, $hNewZ));
		$player->fallDistance = 0.0;

		$position = $player->getPosition();
		$world = $player->getWorld();

		$pitch = mt_rand(33, 60) / 100.0;
		$world->addSound($position, new WindChargeShootSound(LevelSoundEvent::WIND_CHARGE_BURST, $pitch));
		$world->addParticle($position, new WindBurstParticle());
		$world->addSound($position, new ThrowSound());

		$player->resetItemCooldown($this);

		$this->pop();
		return ItemUseResult::SUCCESS;
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
	{
		if($player->hasItemCooldown($this)){
			return ItemUseResult::FAIL;
		}

		$location = $player->getLocation();
		$projectile = $this->createEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);

		$projectile->setMotion($player->getDirectionVector()->multiply($this->getThrowForce()));

		$projectileEv = new ProjectileLaunchEvent($projectile);
		$projectileEv->call();
		if($projectileEv->isCancelled()){
			$projectile->flagForDespawn();
			return ItemUseResult::FAIL;
		}

		$projectile->spawnToAll();

		$position = $player->getPosition();
		$world = $player->getWorld();

		$world->addSound($position, new ThrowSound());

		$player->hasItemCooldown($this);

		$this->pop();

		return ItemUseResult::SUCCESS;
	}

	public function getThrowForce(): float
	{
		return 1.5;
	}

	protected function createEntity(Location $location, Player $thrower): Throwable
	{
		$entity = new WindChargeEntity($location, $thrower);
		$entity->setSource('player');
		$entity->setNoClientPredictions();
		return $entity;
	}
}