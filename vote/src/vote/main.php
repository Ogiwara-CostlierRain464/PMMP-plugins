<?php

namespace vote;

use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;//コマンド関連。
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\entity\Entity;
use pocketmine\entity\Item;

use pocketmine\event\player\PlayerRespawnEvent;//プレイヤーがサーバーに参加した時のイベント。

class main extends PluginBase implements Listener{
	
	public function onEnable(){//このプラグインが読み込まれたときの処理
	
		if(!file_exists($this->getDataFolder())){ 
		mkdir($this->getDataFolder(), 0755, true); 
		}
		
		$this->vote= new Config($this->getDataFolder() . "vote.data", Config::YAML);
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);//Event系を使う時は、これを必ず書くこと。
		$this->mes = "";
		$msgs = $this->vote->getAll();
		foreach($msgs as $key => $value){
			$this->mes .= $key ." by ".$value ."§f\n";
		}
		
	}
	
	public function onPlayerJoin(PlayerRespawnEvent $event){//プレイヤーが参加した時のイベント
	
	
		$ps = Server::getInstance()->getOnlinePlayers();
		$pk2 = new AddEntityPacket();
		$eid2 = "20000000006";
		list($pk2->eid,$pk2->type,$pk2->x,$pk2->y,$pk2->z,$pk2->metadata) =
		[
			$eid2,
			Item::NETWORK_ID,
			87,
			107,
			157,
			[
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING,$this->mes],
				Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
			]
		];
		Server::broadcastPacket($ps,$pk2);
	}
	
	public function onCommand(CommandSender $sender,Command $command,$label,array $args){
		switch($command->getName()){

		case "vote":
			if(!isset($args[0])){
				return false;
			}else{
				$this->vote->set($args[0],$sender->getName());
				$this->vote->save();
				$sender->sendMessage("§aⓘ>>投票されました.再起動後にリスポーン地点で確認して下さい.");
				return true;
			}
		break;
		}
	}
}
	
	
	