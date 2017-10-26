<?php

namespace pocketmine\ui;

use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Utils;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class API{
	/** @var array(CustomUI[]) */
	private static $UIs = [];

	/**
	 * @param Plugin $plugin
	 * @param CustomUI $ui
	 * @return int id
	 */
	public static function addUI(Plugin $plugin, CustomUI &$ui){
		$ui->setID(count(self::$UIs[$plugin->getName()]??[]));
		$id = $ui->getID();
		self::$UIs[$plugin->getName()][$id] = $ui;
		$ui = null;
		return $id;
	}

	public static function resetUIs(Plugin $plugin){
		self::$UIs[$plugin->getName()] = [];
	}

	/**
	 * @return array(CustomUI[])
	 */
	public static function getAllUIs(): array{
		return self::$UIs;
	}

	/**
	 * @param Plugin $plugin
	 * @return CustomUI[]
	 */
	public static function getPluginUIs(Plugin $plugin): array{
		return self::$UIs[$plugin->getName()];
	}

	/**
	 * @param Plugin $plugin
	 * @param int $id
	 * @return CustomUI
	 */
	public static function getPluginUI(Plugin $plugin, int $id): CustomUI{
		return self::$UIs[$plugin->getName()][$id];
	}

	public static function handle(Plugin $plugin, int $id, $response, Player $player){
		$ui = self::getPluginUIs($plugin)[$id];
		var_dump($ui);
		return $ui->handle($response, $player)??"";
	}

	public static function showUI(CustomUI $ui, Player $player){
		$pk = new ModalFormRequestPacket();
		$pk->formData = json_encode($ui);
		$pk->formId = Utils::javaStringHash($ui->getTitle());
		$player->dataPacket($pk);
	}

	public static function showUIbyID(Plugin $plugin, int $id, Player $player){
		$ui = self::getPluginUIs($plugin)[$id];
		$pk = new ModalFormRequestPacket();
		$pk->formData = json_encode($ui);
		$pk->formId = $id;
		$player->dataPacket($pk);
	}

	/**
	 * @param Player[]|OfflinePlayer[] $players
	 * @return array
	 */
	public static function playerArrayToNameArray(array $players): array{
		$return = array_map(function ($player){
			/** @var OfflinePlayer|Player $player */
			return $player->getName();
		}, $players);
		sort($return, SORT_NATURAL);
		return $return;
	}
}
