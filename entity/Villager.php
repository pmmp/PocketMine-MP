<?php

/*
    _____ _                 _        __  __ _____
  / ____| |               | |      |  \/  |  __ \
 | |    | | ___  _   _  __| |______| \  / | |__) |
 | |    | |/ _ \| | | |/ _` |______| |\/| |  ___/
 | |____| | (_) | |_| | (_| |      | |  | | |
  \_____|_|\___/ \__,_|\__,_|      |_|  |_|_|

     Make of Things.
 */

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\inventory\TradeInventory;
use pocketmine\inventory\TradeRecipe;
use pocketmine\entity\Ageable;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use function array_rand;
use function count;
use function mt_rand;

class Villager extends Creature implements NPC, Ageable{

public const NETWORK_ID = self::VILLAGER;

public static $names = [
	Villager::PROFESSION_FARMER => [
		Villager::CAREER_FARMER => "entity.villager.farmer",
		Villager::CAREER_FISHERMAN => "entity.villager.fisherman",
		Villager::CAREER_STEPHERD => "entity.villager.shepherd",
		Villager::CAREER_FLETCHER => "entity.villager.fletcher"
	], Villager::PROFESSION_LIBRARIAN => [
		Villager::CAREER_LIBRARIAN => "entity.villager.librarian",
		Villager::CAREER_CARTOGRAPHER => "entity.villager.cartographer"
	], Villager::PROFESSION_PRIEST => [
		Villager::CAREER_CLERIC => "entity.villager.cleric"
	], Villager::PROFESSION_BLACKSMITH => [
		Villager::CAREER_ARMOR => "entity.villager.armor", Villager::CAREER_WEAPON => "entity.villager.weapon",
		Villager::CAREER_TOOL => "entity.villager.tool"
	], Villager::PROFESSION_BUTCHER => [
		Villager::CAREER_BUTCHER => "entity.villager.butcher", Villager::CAREER_LEATHER => "entity.villager.leather"
	]
];

public const CAREER_FARMER = 1, CAREER_LIBRARIAN = 1, CAREER_CLERIC = 1, CAREER_ARMOR = 1, CAREER_BUTCHER = 1;
public const CAREER_FISHERMAN = 2, CAREER_CARTOGRAPHER = 2, CAREER_WEAPON = 2, CAREER_LEATHER = 2;
public const CAREER_STEPHERD = 3, CAREER_TOOL = 3;
public const CAREER_FLETCHER = 4;

public const PROFESSION_FARMER = 0;
public const PROFESSION_LIBRARIAN = 1;
public const PROFESSION_PRIEST = 2;
public const PROFESSION_BLACKSMITH = 3;
public const PROFESSION_BUTCHER = 4;

public $width = 0.6;
public $height = 1.8;

/** @var int */
protected $career;
/** @var int */
protected $tradeTier;
/** @var bool */
protected $isWilling = true;

protected $offers;

public function getName() : string{
	return "Villager";
}

protected function initEntity() : void{
	parent::initEntity();

	/** @var int $profession */
	$profession = $this->namedtag->getInt("Profession", self::PROFESSION_FARMER);

	if($profession > 4 or $profession < 0){
		$profession = self::PROFESSION_FARMER;
	}

	$this->setProfession($profession);

	$this->career = $this->namedtag->getInt("Career", array_rand(self::$names[$this->getProfession()])); // custom
	$this->tradeTier = $this->namedtag->getInt("TradeTier", 0);
	$this->updateTradeItems();
}

public function updateTradeItems() : void{
	$this->offers = new CompoundTag("Offers", [
		new ListTag("Recipes", []) // TODO
	]);
}

public function updateTradeTier() : void{
	$tradeTier = $this->getTradeTier() + 1;
	try{
		$this->setTradeTier($tradeTier);
		$this->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), mt_rand(2, 5) * 20));
	}catch(\InvalidArgumentException $exception){
	}
}

public function setTradeTier(int $tradeTier) : void{
	$items = []; // TODO
	if(count($items) < ($tradeTier + 1)){
		throw new \InvalidArgumentException("Trade tier $tradeTier is not available");
	}

	$this->tradeTier = $tradeTier;
	$this->updateTradeItems();
}

public function getTradeTier() : int{
	return $this->tradeTier;
}

public function getCareer() : int{
	return $this->career;
}

public function setCareer(int $career) : void{
	$pro = $this->getProfession();
	if(!isset(self::$names[$pro][$career])){
		throw new \InvalidArgumentException("$career is not found on $pro profession.");
	}

	$this->career = $career;
}

public function setOffers(CompoundTag $offers) : void{
	$this->offers = $offers;
}

public function getOffers() : ?CompoundTag{
	return $this->offers;
}

public function saveNBT() : void {
	parent::saveNBT();

	$this->namedtag->setInt("Profession", $this->getProfession());
	$this->namedtag->setInt("Career", $this->career);
	$this->namedtag->setInt("TradeTier", $this->tradeTier);
	$this->updateTradeItems();
	$this->namedtag->setTag($this->offers, true);
}

public function setProfession(int $profession) : void{
	$this->propertyManager->setInt(self::DATA_VARIANT, $profession);
}

public function getProfession() : int{
	return $this->propertyManager->getInt(self::DATA_VARIANT);
}

public function isBaby() : bool{
	return $this->getGenericFlag(self::DATA_FLAG_BABY);
}

public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
	if(!$this->isBaby() and $this->offers instanceof CompoundTag and !$this->isImmobile() and !$this->isWilling()){
		$player->addWindow($this->getInventory());
		return true;
	}
	return parent::onInteract($player, $item, $clickPos);
}

public function getInventory() : TradeInventory{
	return new TradeInventory($this);
}

public function getDisplayName() : string{
	return self::$names[$this->getProfession()][$this->getCareer()] ?? "entity.villager.name";
}

public function isWilling() : bool{
	return $this->isWilling;
}

public function setWilling(bool $isWilling) : void{
	$this->isWilling = $isWilling;
}
}
