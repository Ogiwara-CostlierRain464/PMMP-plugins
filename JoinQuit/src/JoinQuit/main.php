<?php

namespace JoinQuit;

use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;//これらは、ほぼすべてのプラグインで使う

use pocketmine\utils\Config;//Config関連。

use pocketmine\event\player\PlayerJoinEvent;//プレイヤーがサーバーに参加した時のイベント。
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;//コマンド関連。

use ogiwara\Rank\Rank;

class main extends PluginBase implements Listener{
	
	const version = "version 1.0 WMcover";
	
	public function onEnable(){//このプラグインが読み込まれたときの処理
		
		if(!file_exists($this->getDataFolder())){
		mkdir($this->getDataFolder(), 0755, true); 
		}		
		
		$this->setting = new Config($this->getDataFolder() . "setting.yml", Config::YAML, array(
		"first1" => "§b-*.=*.':*+.-.+*:'.*=.*-*.=*.':≪","first2" => "ようこそ！§cOGIサーバへ","first3" => "§6/ruleで、ルールを確認して下さい","first4" => "§eゆっくりしていってね","first5" => "§b-*.=*.':*+.-.+*:'.*=.*-*.=*.':≪",
		"guestjoinfront" => "§6鯖民","guestjoinafter" => "が参加しました","opjoinfront" => "§c権限者","opjoinafter" => "という神が出現した！",
		"guestquitfront" => "§6鯖民","guestquitafter" => "が寝ました","opquitfront" => "§c権限者","opquitafter" => "という神が昇天した！",
		"guestsendpos" => "chat","opsendpos" => "chat",
		"welback1" => "§cおかえりなさい！"
		));
		
		$this->namelist = new Config($this->getDataFolder() ."namelist.yml", Config::YAML, array(
		"ogiwara2" => "chat^§c>>鯖主§6^§cが来た!^§c>>鯖主§6^§cが帰った..."
		));
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);//Event系を使う時は、これを必ず書くこと。
		$this->getLogger()->notice("JoinQuit " .self::version ."をご利用いただき、ありがとうございます 作者 ogiwara");
		$this->getLogger()->notice("このプラグインの二次配布は禁止です");
		$this->getLogger()->notice("不具合が発生した場合は、Twitterの@CostlierRain464まで");
		$this->getLogger()->notice("Copyright © 2016 ogiwara(CostlierRain464) All Rights Reserved.");		
	}
	
	public function onLogin(PlayerPreLoginEvent $event){
		
		$player = $event->getPlayer(); //イベントからプレイヤー取得
		$name = $player->getName();//プレイヤー名の取得
		$mut = $this->getServer()->getDataPath() .'players/' . strtolower($name .'.dat');
		
		if(!file_exists($mut)){
			$this->newplayer[$name] = true;
		}
		
	}
	
	public function onPlayerJoin(PlayerJoinEvent $event){//プレイヤーが参加した時のイベント
	
		$player = $event->getPlayer(); //イベントからプレイヤー取得
		$name = $player->getName();//プレイヤー名の取得
		
		if(isset($this->newplayer[$name])){
			$i = 1;
			while($i){
				if(!$this->setting->exists("first{$i}")){
					break;
				}
			$player->sendMessage($this->setting->get("first{$i}"));
			++$i;
			}
			$event->setjoinMessage("");
			$this->send($this->setting->get("guestsendpos"),"§c新しいプレイヤー、".$name ."が参加しました");
			unset($this->newplayer[$name]);
		}else{
			if(!$this->namelist->exists($name)){
				if(!$player->isOp()){
					$event->setjoinMessage("");
					$i = 1;
					while($i){
						if(!$this->setting->exists("welback{$i}")){
						break;
					}
					$player->sendMessage($this->setting->get("welback{$i}"));
					++$i;
					}
					$this->send($this->setting->get("guestsendpos"),$this->setting->get("guestjoinfront") .$name .$this->setting->get("guestjoinafter"));
				}else{
					$event->setjoinMessage("");
					$i = 1;
					while($i){
						if(!$this->setting->exists("welback{$i}")){
						break;
					}
					$player->sendMessage($this->setting->get("welback{$i}"));
					++$i;
					}					
					$this->send($this->setting->get("opsendpos"),$this->setting->get("opjoinfront") .$name .$this->setting->get("opjoinafter"));
				}
			}else{
				$event->setjoinMessage("");
				$i = 1;
					while($i){
						if(!$this->setting->exists("welback{$i}")){
						break;
					}
					$player->sendMessage($this->setting->get("welback{$i}"));
					++$i;
					}	
				$incase = explode('^',$this->namelist->get($name));
				$this->send($incase[0],$incase[1] .$name .$incase[2]);
			}
		}
		
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event){
	
		$player = $event->getPlayer(); //イベントからプレイヤー取得
		$name = $player->getName();//プレイヤー名の取得
		
			if(!$this->namelist->exists($name)){
				if(!$player->isOp()){
					$event->setquitMessage("");
					$this->send($this->setting->get("guestsendpos"),$this->setting->get("guestquitfront") .$name .$this->setting->get("guestquitafter"));
				}else{
					$event->setquitMessage("");
					$this->send($this->setting->get("opsendpos"),$this->setting->get("opquitfront") .$name .$this->setting->get("opquitafter"));
				}
			}else{
				$event->setquitMessage("");
				$incase = explode('^',$this->namelist->get($name));
				$this->send($incase[0],$incase[3] .$name .$incase[4]);
			}
	}
	
	public function onCommand(CommandSender $sender,Command $command,$label,array $args){
		
		switch($command->getName()){
			
			case "jq":
			if(!isset($args[0])){
				return false;
			}else{				
				switch($args[0]){
					
					case "help":
					case "?":
					case "h":
					if(!isset($args[1])){
						$sender->sendMessage("§a=+-=+-=+-[JoinQuit help]　" .self::version ."=+-=+-=+-");
						$sender->sendMessage("§d/jq：");
						$sender->sendMessage("§6| §b<choose/pos><op/guest><c/t/p>で、プレイヤーがサーバーにログイン・ログアウトした時に、どの位置にメッセージを表示するのか、opかgusetごとに決められます");
						$sender->sendMessage("§6| §b<add/new><name><c/t/p><文1><文2><文3><文4>で、特定のプレイヤーのログイン・ログアウト時の、カスタムプロファイルを作ります");
						$sender->sendMessage("§6| §b<remove/delete><name>で、指定したカスタムプロファイルを削除します");
						$sender->sendMessage("§6| §bさらに詳しい情報を得るには、/jq <help/?> <1か2>と、入力して下さい");
					}else{
						switch($args[1]){
							case "0":
								$sender->sendMessage("§c隠しメニュー");
								$sender->sendMessage("§cネタが思いつかん…");
								$sender->sendMessage("§cこないだ、最少年の「スーパークリエイター」でたじゃん？");
								$sender->sendMessage("§cあれ、俺と同じ学校行ってるんだよw by ogiwara");
							
							break;
							
							case "1":
								$sender->sendMessage("§a~|~|~|~|~[JoinQuit help]　" .self::version ." 詳細1~|~|~|~|~");
								$sender->sendMessage("§d/jq <choose/pos><op/guest><c/t/p>について:");
								$sender->sendMessage("§6| §b<op/guest>: ログインログアウト時の表示位置の設定をopかguestのどちらの場合か選択します");
								$sender->sendMessage("§6| §b<c/t/p>: cはチャットに、tはtip上に、pはポップアップ上に送るように選択できます");
								$sender->sendMessage("§6| §b例: '/setjq choose op c'　だと、");
								$sender->sendMessage("§6| §bopのログイン、ログアウト時のメッセージはチャット上に表示されます");
							break;
							
							case "2":
								$sender->sendMessage("§a^=-^=-^=-[JoinQuit help]　" .self::version ." 詳細2^=-^=-^=-");
								$sender->sendMessage("§d/jq <add/new><name><c/t/p><文1><文2><文3><文4>について");
								$sender->sendMessage("§6| §b<name>: どのプレイヤーのとき、カスタムメッセージを表示するのか");
								$sender->sendMessage("§6| §b<c/t/p>: cはチャットに、tはtip上に、pはポップアップ上に送るように選択できます");
								$sender->sendMessage("§6| §b<文1>: 特定プレイヤのログイン時に、プレイヤー名の前に表示されるメッセージ");
								$sender->sendMessage("§6| §b<文2>: 特定プレイヤのログイン時に、プレイヤー名の後に表示されるメッセージ");
								$sender->sendMessage("§6| §b<文3>: 特定プレイヤのログアウト時に、プレイヤー名の前に表示されるメッセージ");
								$sender->sendMessage("§6| §b<文4>: 特定プレイヤのログアウト時に、プレイヤー名の後に表示されるメッセージ");
								$sender->sendMessage("§6| §b例: '/setjq new ogiwara2 開発者　が来た　開発者　が帰った' だと、");
								$sender->sendMessage("§6| §b「開発者ogiwara2が来た」「開発者ogiwara2が帰った」");
								$sender->sendMessage("§c⚠注意！文字内に、'^'は含めません");
							break;
							
							default:
								$sender->sendMessage("§eⓘ>>1か2を入力して下さい");
							break;
						}
					}		
					break;

					case "choose":
					case "pos":
					case "c":
					case "p":
						if(isset($args[2])){
							if($args[1] == "op" || $args[1] == "guest"){
								switch($args[2]){
									case "c":
										$this->getServer()->broadcastMessage("§aⓘ>>" .$args[1] ."のメッセージの表示位置をチャット上にしました");
										$this->setting->set("{$args[1]}sendpos","chat");
										$this->setting->save();
									break;
									
									case "t":
										$this->getServer()->broadcastMessage("§aⓘ>>" .$args[1] ."のメッセージの表示位置をTip上にしました");
										$this->setting->set("{$args[1]}sendpos","tip");
										$this->setting->save();
									break;
									
									case "p":
										$this->getServer()->broadcastMessage("§aⓘ>>" .$args[1] ."のメッセージの表示位置をpopup上にしました");
										$this->setting->set("{$args[1]}sendpos","popup");
										$this->setting->save();
									break;
									
									default:
										$sender->sendMessage("§eⓘ>>入力に不備があります");
									break;
								}
							}else{
								$sender->sendMessage("§eⓘ>>入力に不備があります");
							}
						}else{
							$sender->sendMessage("§eⓘ>>入力に不備があります");
						}
					break;
					
					case "delete":
					case "remove":
					case "r":
					case "d":
						if(!isset($args[1])){
							$sender->sendMessage("§eⓘ>>入力に不備があります");
						}else{
							if($this->namelist->exists($args[1])){
								$sender->sendMessage("§aⓘ>>" .$args[1] ."のメッセージプロファイルを削除しました");
								$this->namelist->remove($args[1]);
								$this->namelist->save();
							}else{
								$sender->sendMessage("§eⓘ>>そのプレイヤー名は登録されていないようです");
							}
						}	
					break;
					
					case "add":
					case "new":
					case "a":
					case "n":
						if(isset($args[1]) &&  isset($args[2]) && isset($args[3]) && isset($args[4]) && isset($args[5]) && isset($args[6])){
							if(strpbrk($args[3],"^") ==true || strpbrk($args[4],"^") == true || strpbrk($args[5],"^") == true || strpbrk($args[6],"^") == true){
								$sender->sendMessage("§eⓘ>>'^'を含めることは出来ません");
							}else{
							if($args[2] == "c" || $args[2] == "t" || $args[2] == "p"){
								switch($args[2]){
									case "c":
										$sender->sendMessage("§aⓘ>>" .$args[1] ."のメッセージはチャット上に、次のように表示されます");
										$sender->sendMessage("ログイン時  " .$args[3] .$args[1] .$args[4]);
										$sender->sendMessage("ログアウト時  " .$args[5] .$args[1] .$args[6]);
										$this->namelist->set($args[1],"chat" ."^" .$args[3] ."^" .$args[4] ."^" .$args[5] ."^" .$args[6]);
										$this->namelist->save();
									break;
									
									case "t":
										$sender->sendMessage("§aⓘ>>" .$args[1] ."のメッセージはtip上に、次のように表示されます");
										$sender->sendMessage("ログイン時  " .$args[3] .$args[1] .$args[4]);
										$sender->sendMessage("ログアウト時  " .$args[5] .$args[1] .$args[6]);
										$this->namelist->set($args[1],"tip" ."^" .$args[3] ."^" .$args[4] ."^" .$args[5] ."^" .$args[6]);
										$this->namelist->save();										
									break;
									
									case "p":
										$sender->sendMessage("§aⓘ>>" .$args[1] ."のメッセージはpopup上に、次のように表示されます");
										$sender->sendMessage("ログイン時  " .$args[3] .$args[1] .$args[4]);
										$sender->sendMessage("ログアウト時  " .$args[5] .$args[1] .$args[6]);
										$this->namelist->set($args[1],"popup" ."^" .$args[3] ."^" .$args[4] ."^" .$args[5] ."^" .$args[6]);
										$this->namelist->save();				
									break;
								}
							}else{
								$sender->sendMessage("§eⓘ>>入力に不備があります");
							}
							}
						}else{
							$sender->sendMessage("§eⓘ>>入力に不備があります");
						}
					break;
				}
			return true;
			}
			break;
		}
		return false;
	}
	
	public function send($how,$message){
		
		switch($how){
			
		case "chat":
			$this->getServer()->broadcastMessage($message);

		break;
		
		case "popup":
			$this->getServer()->broadcastPopup($message);

		break;
		
		case "tip":
			$this->getServer()->broadcastTip($message);
		break;
		}
	}
}
	
	
	