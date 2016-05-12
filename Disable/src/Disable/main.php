<?php

namespace Disable;

use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;//コマンド

class main extends PluginBase implements Listener{
	
	public function onEnable(){
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch($command->getName()){
			case "disable":
			if(!isset($args[0])){
				return false;
			}else{
				$plu = $this->getServer()->getPluginManager()->getPlugin($args[0]); 
				if($plu != null){
					$sender->sendMessage("Disabling ". $args[0] ." ...");
					$this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin($args[0])); 
					$sender->sendMessage("Disabled. You can reload it after server reboot.");
					return true;
				}else{
					$sender->sendMessage("Plugin name : " .$args[0] ." does not exists.");
					return true;
				}
			}
			
		}
	}
}