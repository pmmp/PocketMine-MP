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

namespace pocketmine\command\defaults;

use pocketmine\block\BlockFactory;
use pocketmine\color\Color;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\lang\TranslationContainer;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\world\particle\BlockForceFieldParticle;
use pocketmine\world\particle\BubbleParticle;
use pocketmine\world\particle\CriticalParticle;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\EnchantmentTableParticle;
use pocketmine\world\particle\EnchantParticle;
use pocketmine\world\particle\EntityFlameParticle;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\particle\InkParticle;
use pocketmine\world\particle\InstantEnchantParticle;
use pocketmine\world\particle\ItemBreakParticle;
use pocketmine\world\particle\LavaDripParticle;
use pocketmine\world\particle\LavaParticle;
use pocketmine\world\particle\Particle;
use pocketmine\world\particle\PortalParticle;
use pocketmine\world\particle\RainSplashParticle;
use pocketmine\world\particle\RedstoneParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\particle\SplashParticle;
use pocketmine\world\particle\SporeParticle;
use pocketmine\world\particle\TerrainParticle;
use pocketmine\world\particle\WaterDripParticle;
use pocketmine\world\particle\WaterParticle;
use pocketmine\world\World;
use function count;
use function explode;
use function max;
use function microtime;
use function mt_rand;
use function strpos;
use function strtolower;

class ParticleCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.particle.description",
			"%pocketmine.command.particle.usage"
		);
		$this->setPermission("pocketmine.command.particle");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 7){
			throw new InvalidCommandSyntaxException();
		}

		if($sender instanceof Player){
			$senderPos = $sender->getPosition();
			$world = $senderPos->getWorld();
			$pos = new Vector3(
				$this->getRelativeDouble($senderPos->getX(), $sender, $args[1]),
				$this->getRelativeDouble($senderPos->getY(), $sender, $args[2], 0, World::Y_MAX),
				$this->getRelativeDouble($senderPos->getZ(), $sender, $args[3])
			);
		}else{
			$world = $sender->getServer()->getWorldManager()->getDefaultWorld();
			$pos = new Vector3((float) $args[1], (float) $args[2], (float) $args[3]);
		}

		$name = strtolower($args[0]);

		$xd = (float) $args[4];
		$yd = (float) $args[5];
		$zd = (float) $args[6];

		$count = isset($args[7]) ? max(1, (int) $args[7]) : 1;

		$data = isset($args[8]) ? (int) $args[8] : null;

		$particle = $this->getParticle($name, $data);

		if($particle === null){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.particle.notFound", [$name]));
			return true;
		}

		$sender->sendMessage(new TranslationContainer("commands.particle.success", [$name, $count]));

		$random = new Random((int) (microtime(true) * 1000) + mt_rand());

		for($i = 0; $i < $count; ++$i){
			$world->addParticle($pos->add(
				$random->nextSignedFloat() * $xd,
				$random->nextSignedFloat() * $yd,
				$random->nextSignedFloat() * $zd
			), $particle);
		}

		return true;
	}

	private function getParticle(string $name, ?int $data = null) : ?Particle{
		switch($name){
			case "explode":
				return new ExplodeParticle();
			case "hugeexplosion":
				return new HugeExplodeParticle();
			case "hugeexplosionseed":
				return new HugeExplodeSeedParticle();
			case "bubble":
				return new BubbleParticle();
			case "splash":
				return new SplashParticle();
			case "wake":
			case "water":
				return new WaterParticle();
			case "crit":
				return new CriticalParticle();
			case "smoke":
				return new SmokeParticle($data ?? 0);
			case "spell":
				return new EnchantParticle(new Color(0, 0, 0, 255)); //TODO: colour support
			case "instantspell":
				return new InstantEnchantParticle(new Color(0, 0, 0, 255)); //TODO: colour support
			case "dripwater":
				return new WaterDripParticle();
			case "driplava":
				return new LavaDripParticle();
			case "townaura":
			case "spore":
				return new SporeParticle();
			case "portal":
				return new PortalParticle();
			case "flame":
				return new FlameParticle();
			case "lava":
				return new LavaParticle();
			case "reddust":
				return new RedstoneParticle($data ?? 1);
			case "snowballpoof":
				return new ItemBreakParticle(VanillaItems::SNOWBALL());
			case "slime":
				return new ItemBreakParticle(VanillaItems::SLIMEBALL());
			case "itembreak":
				if($data !== null and $data !== 0){
					return new ItemBreakParticle(ItemFactory::getInstance()->get($data));
				}
				break;
			case "terrain":
				if($data !== null and $data !== 0){
					return new TerrainParticle(BlockFactory::getInstance()->get($data, 0));
				}
				break;
			case "heart":
				return new HeartParticle($data ?? 0);
			case "ink":
				return new InkParticle($data ?? 0);
			case "droplet":
				return new RainSplashParticle();
			case "enchantmenttable":
				return new EnchantmentTableParticle();
			case "happyvillager":
				return new HappyVillagerParticle();
			case "angryvillager":
				return new AngryVillagerParticle();
			case "forcefield":
				return new BlockForceFieldParticle($data ?? 0);
			case "mobflame":
				return new EntityFlameParticle();
		}

		if(strpos($name, "iconcrack_") === 0){
			$d = explode("_", $name);
			if(count($d) === 3){
				return new ItemBreakParticle(ItemFactory::getInstance()->get((int) $d[1], (int) $d[2]));
			}
		}elseif(strpos($name, "blockcrack_") === 0){
			$d = explode("_", $name);
			if(count($d) === 2){
				return new TerrainParticle(BlockFactory::getInstance()->get(((int) $d[1]) & 0xff, ((int) $d[1]) >> 12));
			}
		}elseif(strpos($name, "blockdust_") === 0){
			$d = explode("_", $name);
			if(count($d) >= 4){
				return new DustParticle(new Color(((int) $d[1]) & 0xff, ((int) $d[2]) & 0xff, ((int) $d[3]) & 0xff, isset($d[4]) ? ((int) $d[4]) & 0xff : 255));
			}
		}

		return null;
	}
}
