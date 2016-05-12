<?php

namespace hochi;

use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;//これらは、ほぼすべてのプラグインで使う

use pocketmine\utils\Config;//Config関連。

use pocketmine\event\player\PlayerQuitEvent;//プレイヤーの、ログイン、ログアウトイベント
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;//コマンド関連。

class main extends PluginBase implements Listener{
	
	const version = "v2.0 WMcover";
	
	public function onEnable(){
		
		if(!file_exists($this->getDataFolder())){ 
		mkdir($this->getDataFolder(), 0755, true); 
		}
		
		$this->getLogger()->notice("hochi " .self::version ."をご利用いただき、ありがとうございます 作者 ogiwara");
		$this->getLogger()->notice("このプラグインの二次配布は禁止です");
		$this->getLogger()->notice("不具合が発生した場合は、Twitterの@CostlierRain464まで");
		$this->getLogger()->notice("Copyright © 2016 ogiwara(CostlierRain464) All Rights Reserved.");		
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);//Event系を使う時は、これを必ず書くこと。
		$this->setting = new Config($this->getDataFolder() . "setting.yml", Config::YAML, array(
		"hochistart" =>"§b(´・ω・`)@pが放置を開始しました","hochiend" => "§b(｀・ω・´)@pが放置を終了しました",
		"nametag" => "[放置]"
		));		
	}
	
	public function onCommand(CommandSender $sender,Command $command,$label,array $args){
		switch($command->getName()) {	
			case "hochi":
				if($sender instanceof Player){
					$player = $sender->getPlayer();
					$name = $player->getName();
					if(!isset($this->hochi[$name])){
						$this->hochi[$name] = $player->getDisplayName();
						$player->setDisplayName($this->setting->get("nametag").$this->hochi[$name]);
						$mesa = str_replace("@p",$name,$this->setting->get("hochistart"));
						$this->getServer()->broadcastMessage($mesa);
						$player->sendMessage("§a>>/hochiで解除できます");
					}else{
						$player->setDisplayName($this->hochi[$name]);
						unset($this->hochi[$name]);
						$mesb = str_replace("@p",$name,$this->setting->get("hochiend"));
						$this->getServer()->broadcastMessage($mesb);
					}
				}else{
					$sender->sendMessage("§e>>ⓘ貴方は放置できません");
				}
			return true;
			break;
		}
	}
	
	public function onQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();		
		if(isset($this->hochi[$name])){
			unset($this->hochi[$name]);
		}
	}
	
	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();		
		if(isset($this->hochi[$name])){
			$event->setCancelled();
			$player->sendTip("§a/hochiで解除できます");
		}
	}		
		
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();		
		if(isset($this->hochi[$name])){
			$event->setCancelled();
			$player->sendTip("§a/hochiで解除できます");
		}				
	}
}
	
	
	