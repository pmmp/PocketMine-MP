<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_shift;
use function count;

class OpCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"op",
			KnownTranslationFactory::pocketmine_command_op_description(),
			KnownTranslationFactory::commands_op_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_OP_GIVE);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		$name = array_shift($args);

		$player = $sender->getServer()->getPlayerByPrefix($name);
		if($player !== null){
			$name = $player->getName();
		}elseif(!Player::isValidUserName($name)){
			throw new InvalidCommandSyntaxException();
		}

		$sender->getServer()->addOp($name);
		if(($player = $sender->getServer()->getPlayerExact($name)) !== null){
			$player->sendMessage(KnownTranslationFactory::commands_op_message()->prefix(TextFormat::GRAY));
		}
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_op_success($name));
		return true;
	}
}