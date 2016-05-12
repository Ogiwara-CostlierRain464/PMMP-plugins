<?php

namespace FroatText;

use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\protocol\InteractPacket;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;//座標指定
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\entity\Entity;
use pocketmine\entity\Item;

use pocketmine\network\protocol\AddEntityPacket;

use pocketmine\event\player\PlayerRespawnEvent;//プレイヤーがサーバーに参加した時のイベント。

class main extends PluginBase implements Listener{
	
	public function onEnable(){//このプラグインが読み込まれたときの処理
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);//Event系を使う時は、これを必ず書くこと。
		
	}
	
	public function onReceive(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		if($pk instanceof InteractPacket){
			if($pk->target == "10000000000"){
				$p = $event->getPlayer();
				$i = rand(1,10);
				switch($i){
					case "1":
					$p->sendPopup("§aOGIserverで楽しんでいってね！");
					break;
					
					case "2":
					$p->sendPopup("§b青鬼イベントで優勝すると、賞金が...");
					break;
					
					case "3":
					$p->sendPopup("§a/dengon (名前) (メッセージ) で、伝言が残せるって知ってる？");
					break;
					
					case "4":
					$p->sendPopup("§cくれぐれも他人の家には勝手に入らないでね\n勝手に入っていいのは管理者だけだよ");
					break;
					
					case "5":
					$p->sendPopup("§a/tellで話していることは、実は鯖主から全部見えているらしいよ…");
					break;
					
					case "6":
					$p->sendPopup("§aこの鯖いいんじゃないじゃないザワザワ");
					break;
					
					case "7":
					$p->sendPopup("§c梅は最強! by yuki1127");
					break;
					
					case "8":
					$p->sendPopup("§aあと、あんまり殴らないでね…(＃＾ω＾)ﾋﾟｷﾋﾟｷ");
					break;
					
					case "9":
					$p->sendPopup("§aこの鯖の村人です!");
					break;
					
					case "10":
					$p->sendPopup("§c猫大好き ! by saki1108");
					break;
				
				}
			}
		}
	}
	
	public function onJoin(PlayerRespawnEvent $event){
		$ps = Server::getInstance()->getOnlinePlayers();
		$pk1 = new AddEntityPacket();
		$eid1 = "10000000000";
		list($pk1->eid,$pk1->type,$pk1->x,$pk1->y,$pk1->z,$pk1->speedX,$pk1->speedY,$pk1->speedZ,$pk1->yaw,$pk1->pitch,$pk1->metadata) =
		[
			$eid1,
			15,
			131,
			65,
			116,
			0,
			0,
			0,
			90,
			0,
			[
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING,"§l§eOGIserverの村人"],
				Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
			]
		];
		Server::broadcastPacket($ps,$pk1);
		
		
		$pk2 = new AddEntityPacket();
		$eid2 = "10000000001";
		list($pk2->eid,$pk2->type,$pk2->x,$pk2->y,$pk2->z,$pk2->metadata) =
		[
			$eid2,
			Item::NETWORK_ID,
			83,
			103,
			158,
			[
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING,"§bイベント開催予定リスト\n§9・ -青鬼- AOONI\n§e>>毎週金曜日午後10時\n§3・ ゾンビ襲来イベント\n§e>>現在開催予定はありません"],
				Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
			]
		];
		Server::broadcastPacket($ps,$pk2);

		$pk3 = new AddEntityPacket();
		$eid3 = "10000000002";
		list($pk3->eid,$pk3->type,$pk3->x,$pk3->y,$pk3->z,$pk3->metadata) =
		[
			$eid3,
			Item::NETWORK_ID,
			65,
			109,
			157,
			[
				Entity::DATA_FLAGS  => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING,"§cようこそ! OGIserverへ!\n§6①/ruleで、このサーバーのルールを確認して下さい\n§e②/tipsで、このサーバーの豆知識を確認できます\n§a③荒らし行為は厳禁です\nそれでは、ゆっくりしていってね!"],
				Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
			]
		];
		Server::broadcastPacket($ps,$pk3);
			
		$pk4 = new AddEntityPacket();
		$eid4 = "10000000003";
		list($pk4->eid,$pk4->type,$pk4->x,$pk4->y,$pk4->z,$pk4->metadata) =
		[
			$eid4,
			Item::NETWORK_ID,
			76,
			108,
			157,
			[
				Entity::DATA_FLAGS  => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING,"§6権限者一覧\n§b[=鯖主=]ogiwara2\n§68n2ilove\n§7konapero\n§ahiro_\n§dyuki1127\n§1saki1108\n§2moriyan\n§3kagerou0713\n§6Sion5017\n§fSayaka\n§dToilet-Friendly"],
				Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
			]
		];
		Server::broadcastPacket($ps,$pk4);
	}
	
}
	
	
	