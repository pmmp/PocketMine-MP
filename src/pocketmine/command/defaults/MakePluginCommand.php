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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\plugin\FolderPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class MakePluginCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "Creates a Phar plugin from a unarchived",
            '/makeplugin <pluginName>',
            ["mp"], [[
                new CommandParameter("plugin", CommandParameter::ARG_TYPE_RAWTEXT, false)
            ]]
        );

        $this->setPermission("altay.command.makeplugin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        if(count($args) < 1){
            throw new InvalidCommandSyntaxException();
        }

        $pluginName = trim(implode(" ", $args));
        if($pluginName === "" or !(($plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName)) instanceof Plugin)){
            $sender->sendMessage(TextFormat::RED . "Invalid plugin name, check the name case.");
            return true;
        }
        $description = $plugin->getDescription();

        if(!($plugin->getPluginLoader() instanceof FolderPluginLoader)){
            $sender->sendMessage(TextFormat::RED . "Plugin " . $description->getName() . " is not in folder structure.");
            return true;
        }

        $pharPath  = Server::getInstance()->getPluginPath() . "Altay" . DIRECTORY_SEPARATOR . $description->getFullName() . ".phar";
        if(file_exists($pharPath)){
            $sender->sendMessage("Phar plugin already exists, overwriting...");
            unlink($pharPath);
        }

        $phar = new \Phar($pharPath);
        $phar->setMetadata([
            "name" => $description->getName(),
            "version" => $description->getVersion(),
            "main" => $description->getMain(),
            "api" => $description->getCompatibleApis(),
            "depend" => $description->getDepend(),
            "description" => $description->getDescription(),
            "authors" => $description->getAuthors(),
            "website" => $description->getWebsite(),
            "creationDate" => time()
        ]);

        $phar->setStub('<?php echo "Altay plugin ' . $description->getFullName() . '\nThis file has been generated using Turanic at ' . date("r") . '.\n----------------\n";if(extension_loaded("phar")){$phar = new \Phar(__FILE__);foreach($phar->getMetadata() as $key => $value){echo ucfirst($key).": ".(is_array($value) ? implode(", ", $value):$value)."\n";}} __HALT_COMPILER();');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $reflection = new \ReflectionClass("pocketmine\\plugin\\PluginBase");
        $file = $reflection->getProperty("file");
        $file->setAccessible(true);
        $basePath = rtrim(str_replace("\\", "/", $file->getValue($plugin)), "/") . "/";

        $phar->startBuffering();

        //If paths contain any of these, they will be excluded
        $excludedSubstrings = [
            "/.", //"Hidden" files, git information etc
            realpath($pharPath) //don't add the phar to itself
        ];

        $regex = sprintf('/^(?!.*(%s))^%s(%s).*/i',
            implode('|', $this->preg_quote_array($excludedSubstrings, '/')), //String may not contain any of these substrings
            preg_quote($basePath, '/'), //String must start with this path...
            implode('|', $this->preg_quote_array([], '/')) //... and must be followed by one of these relative paths, if any were specified. If none, this will produce a null capturing group which will allow anything.
        );

        $count = count($phar->buildFromDirectory($basePath, $regex));
        $sender->sendMessage("[Altay] Added $count files");

        $sender->sendMessage("[Altay] Checking for compressible files...");
        foreach($phar as $file => $finfo){
            /** @var \PharFileInfo $finfo */
            if($finfo->getSize() > (1024 * 512)){
                $sender->sendMessage("[Altay] Compressing " . $finfo->getFilename());
                $finfo->compress(\Phar::GZ);
            }
        }
        $phar->stopBuffering();

        $sender->sendMessage("Phar plugin " . $description->getFullName() . " has been created on " . $pharPath);

        return true;
    }

    private function preg_quote_array(array $strings, string $delim = null) : array{
        return array_map(function(string $str) use ($delim) : string{ return preg_quote($str, $delim); }, $strings);
    }
}