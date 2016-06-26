<?php

namespace JoinMessage;//このPHPデータが入っているフォルダ名。基本的にはプラグイン名

use pocketmine\Plugin\PluginBase;
use pocketmine\event\Listener;//基本的に、どのプラグインでも使うuse名。

use pocketmine\event\player\PlayerJoinEvent;//プレイヤーがサーバーに参加した時のイベント。

class main extends PluginBase implements Listener{

	public function onEnable(){//このプラグインが読み込まれたときの処理
		
      	$this->getServer()->getPluginManager()->registerEvents($this,$this);//イベントを使う時は、これを必ず書くこと。
	}
	
	public function onPlayerJoin(PlayerJoinEvent $event){//プレイヤーが参加した時のイベント
		$player = $event->getPlayer(); //イベントからプレイヤー取得
		$name = $player->getName();
		
		$this->getServer()->broadcastMessage($name + "さんがやってきた!");
	}
}

/*?>はいらない*/