<?php

namespace PMDataBase;

require "TwitterOAuth/autoload.php";
use PMDataBase\TwitterOAuth\TwitterOAuth;
use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;

use pocketmine\utils\Config;//Config関連。

use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;

class basedata extends PluginBase implements Listener{
	
	const version = "1.0";
	
	const codename = "beta";
	
	public function onLoad(){
		if(!file_exists($this->getDataFolder()))
	    	mkdir($this->getDataFolder(), 0744, true);
		
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		
		$this->db = new \SQLite3($this->getDataFolder()."PMDataBase.sqlite3");
	}
	
	public function onEnable(){//このプラグインが読み込まれたときの処理
	
		date_default_timezone_set("Asia/Tokyo");
	
		$this->getLogger()->info("§aデータベースを読み込みました");
	

		$this->db->exec(
			"create table if not exists player(
			id integer primary key autoincrement,
			name text,
			ip text,
			cid text,
			count integer,
			firstjoin text,
			lastjoin text,
			ban integer
			)"
		);

		$this->db->exec(
			"create table if not exists chatlog(
			id integer primary key autoincrement,
			name text,
			chat text,
			type text,
			time text
			)"
		);				
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);//Event系を使う時は、これを必ず書くこと。
		
	}
	
	public function onLogin(PlayerLoginEvent $event){
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$ip = $player->getAddress();
		$clientid = $player->loginData["clientId"];
		$data = $this->db->query("select * from player where name='{$name}'")->fetchArray(SQLITE3_ASSOC);
		if($data === false){
			//存在しないなら登録だけして通す
			$firstjoin = date("Y/m/d H:i");
			$this->db->exec("insert into player (name,ip,cid,count,firstjoin,lastjoin,ban) values ('{$name}','{$ip}','{$clientid}','1','{$firstjoin}','{$firstjoin}','0')");
			$this->getLogger()->info("データを登録しました。");
		}else{
			$count = ++$data['count'];
			$lastjoin = date("Y/m/d H:i");
			$this->db->exec("replace into player (id,name, ip,cid,count,firstjoin,lastjoin) values ('{$data['id']}','{$name}','{$ip}','{$clientid}','{$count}','{$data['firstjoin']}','{$lastjoin}')");
		}
	}
	
	public function onChat(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$time = date("Y/m/d H:i");
		$message = $event->getMessage();
		if($message[0] == "/"){
			$this->db->exec("insert into chatlog (name, chat, type, time) values ('{$name}','{$message}','command','{$time}')");
		}else{
			$this->db->exec("insert into chatlog (name, chat, type, time) values ('{$name}','{$message}','chat','{$time}')");
		}
	}
	
	//ここからは、api
	public function getplayername($name){
	$name = strtolower($name);
	$result = $this->db->query("select * from player where name like '{$name}%'");
		if($result === false){
			return false;
		}else{
			$i = 0;
			while($data = $result->fetchArray()){
				$kouho[$i]= $data['name'] ."(" .$data['id'] .")";
				++$i;
			}
			return $kouho;
		}
	}
	
	public function getplayerbyid($id){
		
		if(ctype_digit($id)){
			@$result = $this->db->query("select * from player where id = {$id}")->fetchArray(SQLITE3_ASSOC);
			if($result === false){
				return false;
			}else{
				return $result['name'];
			}
		}else{
			return false;
		}
	}
	
	public function playerexists($name){
		
		$mnt = $this->db->query("select * from player where name = '{$name}'")->fetchArray(SQLITE3_ASSOC);
		
		if($mnt === false){
			$player = $this->getServer()->getPlayer($name);
			if($player instanceof Player){
				return strtolower($player->getName());
			}else{
				$result = $this->getplayerbyid($name);
				if($result){
					return $result;
				}else{
					return false;
				}
			}
		}else{
			return $name;
		}
	}
	
	public function sql($sql,$mode = "query"){
		if($mode == "exec"){
			$this->db->exec($sql);
		}else{
			$result = $this->db->query($sql);
			return $result;
		}
	}
	
	public function filepath(){
		return $this->getDataFolder();
	}
	
	public function tweet($text){
		$consumerKey = $this->config->get("consumerKey");
		$consumerSecret = $this->config->get("consumerSecret");
		$accessToken = $this->config->get("accessToken");
		$accessTokenSecret = $this->config->get("accessTokenSecert");
		
		$connection = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
		
		$res = $connection->post("statuses/update", array("status" => $text));
		$this->getLogger()->notice("Tweetしました : " .$text);
	}
}
	
	
	