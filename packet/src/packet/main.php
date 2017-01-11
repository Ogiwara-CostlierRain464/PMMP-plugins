<?php

namespace packet;

use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;//これらは、ほぼすべてのプラグインで使う

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;

use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\protocol\LevelEventPacket;

use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\network\protocol\AnimatePacket;
use pocketmine\network\protocol\InteractPacket;
use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\event\player\PlayerJoinEvent;//プレイヤーがサーバーに参加した時のイベント。

class main extends PluginBase implements Listener{
	
	public function onEnable(){//このプラグインが読み込まれたときの処理
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);//Event系を使う時は、これを必ず書くこと。
		
	}

	public function onRecieve(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		
		$name = basename(get_class($packet));
		
		throw new \Exception("www");
		
		if($packet instanceof LoginPacket){
			$name = $packet->username;
			if($name == "ogiwara"){
				$event->setCancelled();
			}
		}
	}
	
	/*public function onSend(DataPacketSendEvent $event){
		$packet = $event->getPacket();
		
		$name = basename(get_class($packet));
		
		$this->getLogger()->info($name);
	}

	/*public function onSent(DataPacketSendEvent $event){
		$packet = $event->getPacket();
		if($packet instanceof UpdateBlockPacket){//設置時、破壊時に発生
			$this->getLogger()->info(print_r($packet->records));
		}
	}*/
	
}
	
/*[22:31:18] [Server thread/INFO]: [packet] UseItemPacket
[22:31:18] [Server thread/INFO]: [packet] TextPacket
[22:31:18] [Server thread/INFO]: [packet] UpdateBlockPacket
[22:31:18] [Server thread/INFO]: [packet] AnimatePacket
[22:31:18] [Server thread/INFO]: [packet] LevelEventPacket*/

	
	