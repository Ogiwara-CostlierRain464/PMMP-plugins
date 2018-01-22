<?php

namespace skyblock;

use pocketmine\Plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\math\Vector3;//座標指定
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\entity\Item;

use pocketmine\network\protocol\AddEntityPacket;

use pocketmine\utils\Config;//Config関連。

use pocketmine\entity\Entity;
use pocketmine\entity\Effect;

use pocketmine\scheduler\PluginTask;//タスク

class main extends PluginBase implements Listener{
	
	public function onEnable(){//このプラグインが読み込まれたときの処理

		if(!file_exists($this->getDataFolder())){ 
		mkdir($this->getDataFolder(), 0755, true); 
		}
		
		$this->level = new Config($this->getDataFolder() . "level.yml", Config::YAML);
		$this->death = new Config($this->getDataFolder() . "death.yml", Config::YAML);
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);//Event系を使う時は、これを必ず書くこと。
		
	}
	
	public function onPreLogin(PlayerPreLoginEvent $event){
		
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$mut = $this->getServer()->getDataPath() .'players/' . $name .'.dat';
		
		if(!file_exists($mut)){
			$this->newplayer[$name] = true;
		}
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		
		if(isset($this->newplayer[$name])){
			$effect = Effect::getEffect(15);//effectID
			$effect->setDuration(15*20);//効果の時間*20
			$effect->setAmplifier(0);//効果の強さ
			$effect->setVisible(false);//パーティクルを表示するかどうか
			$player->addEffect($effect);
			$player->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 1);
			$task = new welcome($this,$player);
			$this->tid[$name] = $this->getServer()->getScheduler()->scheduleRepeatingTask($task,60)->getTaskId();
			unset($this->newplayer[$name]);
		}
	}
	
	public function onRespawn(PlayerRespawnEvent $event){
		
		$list = $this->level->getAll();
		arsort($list,SORT_NUMERIC);
		foreach($list as $key => $val){
			static $i = 0;
			$comp[$i] = $key." ".$val;
			++$i;
		}
		$text = "";
		for($a = 0;$a <= 1;++$a){
			if(!isset($comp[$a])){
				break;
			}else{
				$text .= $comp[$a];
			}
		}
		$ps = Server::getInstance()->getOnlinePlayers();
		$pk3 = new AddEntityPacket();
		$eid3 = "11111111111111111";
		list($pk3->eid,$pk3->type,$pk3->x,$pk3->y,$pk3->z,$pk3->metadata) =
		[
			$eid3,
			Item::NETWORK_ID,
			65,
			109,
			157,
			[
				Entity::DATA_FLAGS  => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING,$text],
				Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
			]
		];
		Server::broadcastPacket($ps,$pk3);
		
		foreach($ps as $player){
			if($player->getLevel()->getName() == "world"){
				$name = strtolower($player->getName());
				$level = $this->getlevel($name);
				$death = $this->getdeath($name);
				$soul = $this->mathlevel($level);
				$status = "$name のステータス\n倒した数 $level \n死んだ回数 $death";
				$pk1 = new AddEntityPacket();
				$eid1 = "2222222222222222";
				list($pk1->eid,$pk1->type,$pk1->x,$pk1->y,$pk1->z,$pk1->metadata) =
				[
					$eid1,
					Item::NETWORK_ID,
					64,
					109,
					156,
					[
						Entity::DATA_FLAGS  => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
						Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING,$status],
						Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
						Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
					]
				];
				$player->dataPacket($pk1);
			}
		}
	}
	
	public function onQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		if(isset($this->tid[$name])){
			$this->closetask($name);
		}
	}
	
	public function onItem(PlayerItemHeldEvent $event){
		$item = $event->getItem();
		if($item->getId() == 339){
			$player = $event->getPlayer();
			$name = strtolower($player->getName());
			$level = $this->getlevel($name);
			$death = $this->getdeath($name);
			$soul = $this->mathlevel($level);
			$player->sendMessage("-====".$name."======");
			$player->sendMessage("kill ".$level);
			$player->sendMessage("Death ".$death);
			$player->sendMessage("Level ".$soul);
		}
	}
	
	/*public function onEntityDamage(EntityDamageEvent $event){
			@$name = get_class($event->getDamage());
			/*if($killer instanceof Player){
				$killed = $event->getEntity();
				$name = strtolower($killer->getName());
				$add = 0.1*$this->mathlevel($this->level->get($name));
				$ev = new EntityDamageEvent($killed, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $add);//EntityDamageEvent::CAUSE_MAGICを変えることでダメージの種類を、1を変えることでダメージの強さが変更できます
				$killed->attack($ev->getFinalDamage(), $ev);
			}
	}*/

	
	public function onEntityDeath(EntityDeathEvent $event){
		if($event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			$killed = $event->getEntity();
			$killer = $event->getEntity()->getLastDamageCause()->getDamager();
			if($killed instanceof Player){
				$event->setCancelled();
			}else{
				if($killer instanceof Player){
					$this->addlevel(strtolower($killer->getName()));
				}
			}
		}
	}
	
	public function closetask($name){
		$this->getServer()->getScheduler()->cancelTask($this->tid[$name]);
		$player = $this->getServer()->getPlayer($name);
		$player->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 0);
		unset($this->tid[$name]);
	}
	
	public function addlevel($name){
		if(!$this->level->exists($name)){
			$this->level->set($name,1);
			$this->level->save();
		}else{
			$count = $this->level->get($name);
			++$count;
			$this->level->set($name);
			$this->level->save();
		}
	}
	
	public function getdeath($name){
		if(!$this->death->exists($name)){
			return 0;
		}else{
			return $this->death->get($name);
		}				
	}
	
	public function getlevel($name){
		if(!$this->level->exists($name)){
			return 0;
		}else{
			return $this->level->get($name);
		}		
	}
	
	public function mathlevel($num){
		$level = $num/20;
	}
	
	
	public function adddeath($name){
		if(!$this->death->exists($name)){
			$this->death->set($name,1);
			$this->death->save();
		}else{
			$count = $this->death->get($name);
			++$count;
			$this->death->set($name);
			$this->death->save();
		}		
	}
	
	public function onDeath(PlayerDeathEvent $event){
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$this->getServer()->dispatchCommand(new ConsoleCommandSender(),"takemoney " .$name ." 200");
		$this->adddeath($name);
	}
	
	
}

class welcome extends PluginTask{
	
	public function __construct($owner,$player){
		parent::__construct($owner);
		$this->player = $player;
	}
	
	public function onRun($ticks){
		if(!isset($this->counter[strtolower($this->player->getName())])){
			$this->counter[strtolower($this->player->getName())] = 0;
		}
		++$this->counter[strtolower($this->player->getName())];
		
		switch($this->counter[strtolower($this->player->getName())]){
			case 2:
				$this->player->sendMessage("ようこそ");
			break;
			case 3:
				$this->player->sendMessage("OGIserverへ");
			break;
			case 4:
				$this->player->sendMessage("ゆっくりしていってね");
				$this->owner->closetask(strtolower($this->player->getName()));
			break;
		}
	}
}