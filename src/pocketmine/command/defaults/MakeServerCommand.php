<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Server;

class MakeServerCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "Creates a Altay Phar",
            "/makeserver",
            ["ms"]
        );
        $this->setPermission("altay.command.makeserver");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if (!$this->testPermission($sender)) {
            return false;
        }

        $server = $sender->getServer();
        $pharPath = Server::getInstance()->getPluginPath() . "Altay" . DIRECTORY_SEPARATOR . $server->getName() . "_v" . $server->getApiVersion() . ".phar";
        if (file_exists($pharPath)) {
            $sender->sendMessage("Phar file already exists, overwriting...");
            @unlink($pharPath);
        }
        $sender->sendMessage($server->getName() . " Phar has begun to be created. This will take 2 minutes (may vary depending on your system).");
        $phar = new \Phar($pharPath);
        $phar->setMetadata([
            "name" => $server->getName(),
            "version" => $server->getPocketMineVersion(),
            "api" => $server->getApiVersion(),
            "minecraft" => $server->getVersion(),
            "protocol" => ProtocolInfo::CURRENT_PROTOCOL,
            "creationDate" => time()
        ]);
        $phar->setStub('<?php require_once("phar://". __FILE__ ."/src/pocketmine/PocketMine.php");  __HALT_COMPILER();');
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();

        $filePath = substr(\pocketmine\PATH, 0, 7) === "phar://" ? \pocketmine\PATH : realpath(\pocketmine\PATH) . "/";
        $filePath = rtrim(str_replace("\\", "/", $filePath), "/") . "/";

        if(file_exists($filePath."vendor")){
            foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath . "vendor")) as $file){
                $path = ltrim(str_replace(["\\", $filePath], ["/", ""], $file), "/");
                if ($path{0} === "." or strpos($path, "/.") !== false or substr($path, 0, 7) !== "vendor/") {
                    continue;
                }
                $phar->addFile($file->getPathname(), $path);
            }
        }

        /** @var \SplFileInfo $file */
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath . "src")) as $file){
            $path = ltrim(str_replace(["\\", $filePath], ["/", ""], $file), "/");
            if ($path{0} === "." or strpos($path, "/.") !== false or substr($path, 0, 4) !== "src/") {
                continue;
            }
            $phar->addFile($file->getPathname(), $path);
        }

        foreach($phar as $file => $finfo){
            /** @var \PharFileInfo $finfo */
            if ($finfo->getSize() > (1024 * 512)) {
                $finfo->compress(\Phar::GZ);
            }
        }
        $phar->stopBuffering();
        $sender->sendMessage($server->getName() . " " . $server->getPocketMineVersion() . " Phar file has been created on " . $pharPath);

        return true;
    }
}
