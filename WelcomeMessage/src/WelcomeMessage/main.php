<?php

namespace WelcomeMessage;

use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;//基本的なuse

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;//プレイヤーの、ログイン、ログアウトイベント。

use pocketmine\command\Command;
use pocketmine\command\CommandSender;//コマンド関連。

use pocketmine\utils\Config;//Config関連。

use pocketmine\item\item;//アイテム関連
use pocketmine\inventory\PlayerInventory;//インベントリ関連

class main extends PluginBase implements Listener{

	public function onEnable(){
		$this->saveResource("setting.yml", false); 
		if(!file_exists($this->getDataFolder())){ 
		mkdir($this->getDataFolder(), 0755, true); 
		}
		$this->setting = new Config($this->getDataFolder() . "setting.yml", Config::YAML, array("WelMes1" => "§b さん、§d§mMinekureServer§bへようこそ！","WelMes2" => "§bこのサーバーでは、自由に建築したりして、楽しめます。","WelMes3" => "§b楽しんで行ってね！","WelBack" => "§b さん、おかえりなさい！",
"ruleinfo" => "§d/rule で、ルールの確認ができます。","tipsinfo" => "§6/tips で、このサーバーの豆知識が確認できます。","broadcastlogout" => "§e が寝ました。","broadcastjoin2" => "§e が起きました。","broadcastjoin2 for op" => "§cという神が出現した！","broadcastlogout for op" =>"§cという神が昇天した！",
"ruleEdge" => "§d========ルール========","rule1" => "以下の行為(荒らし行為)は、禁止","rule2" => "暴言、不法侵入、資源ワールドの無作為な資源確保","rule3" => "不適切な名前、発言、意味不明な文字の羅列","rule4" => "物をねだる行為、不愉快な行為,個人情報を聞き出す行為","rule5" => "意図的なバグの利用、その他非常識な行動",
"rule6" => "これらを破った場合、それなりの処罰を下します","rule7" => "例):罰金、uban","rule8" => "商業を営む場合",
"tipsEdge" => "§6========豆知識========","tip1" => "1./lockchestと入力すると、チェストがロックできます。","tip2" => "2./home <名前>と入力すると、自分だけのワープ地点が作れます。","tip3" => "3.未定義","tip4" => "4.未定義","tip5" => "5.未定義",
"delay" => "wait update","hochi" => "wait update","item1" => "275,0,2","item2" => "274,0,1",
"I/O WelMes" => true,"I/O rule" => true,"I/O tips" => true,"I/O broadcastjoin" => true,"I/O WelBack" => true,"I/O broadcastlogout" => true,"I/O dengon" => "wait update","I/O getitem" => true,"I/O hochi" => true,"showtips" => "wait update",
"Edgetop" => "+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-","Edgedown" => "+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-")); 

		$this->playerdata = new Config($this->getDataFolder() . "playerdata.yml", Config::YAML, array("Steve" => "now testing"));
		
		$this->numdata = new Config($this->getDataFolder() . "numdata.yml", Config::YAML, array("rulesum" => 8));
		
		$this->hochi = new Config($this->getDataFolder() ."hochi.yml", Config::YAML );

		$this->getServer()->getPluginManager()->registerEvents($this,$this);//イベント登録。	
	}
	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();//プレイヤー名の取得。
		if($this->setting->get("I/O WelMes")){
			if(!$this->playerdata->exists("$name")){
				$player->sendMessage($this->setting->get("Edgetop"));
				$player->sendMessage("§b" .$name .$this->setting->get("WelMes1"));
				$player->sendMessage($this->setting->get("WelMes2"));
				$player->sendMessage($this->setting->get("WelMes3"));
				$player->sendMessage($this->setting->get("Edgedown"));
			}
		}//WelMesについて
		
		if($this->setting->get("I/O getitem")){
			if(!$this->playerdata->exists("$name")){
				 $i = 1;
			while($i == true){
				if(!$this->setting->exists("item{$i}")){
				break;
				}
				$item{$i} = explode(',',$this->setting->get("item{$i}"));
				$item{$i}[0] = intval($item{$i}[0]); 
				$item{$i}[1] = intval($item{$i}[1]); 
				$item{$i}[2] = intval($item{$i}[2]);
				$give = Item::get($item{$i}[0],$item{$i}[1],$item{$i}[2]);
				$player->getInventory()->addItem($give);
				$i++;
			}//アイテムのループ配布
			}
		}
		if($this->setting->get("I/O WelBack")){
			if($this->playerdata->exists("$name")){
				$player->sendMessage("§b" .$name.$this->setting->get("WelBack"));
			}
		}//WelBackの機能。
		
		if($this->setting->get("I/O rule")){
			if(!$this->playerdata->exists("$name")){
				$player->sendMessage($this->setting->get("ruleinfo"));
			}
		}//ルールの説明。
		if($this->setting->get("I/O tips")){
			if(!$this->playerdata->exists("$name")){
				$player->sendMessage($this->setting->get("tipsinfo"));
			}
		}//豆知識の説明。
		if($this->setting->get("I/O broadcastjoin")){
		$event->setjoinMessage("");
			if(!$this->playerdata->exists("$name")){
			$this->getServer()->broadcastMessage("§c新しいプレイヤー" .$name ."§cが参加しました!");
			}else{
				if(!$player->isOp()){
					$this->getServer()->broadcastMessage("§b".$name .$this->setting->get("broadcastjoin2"));
				}else{
					$this->getServer()->broadcastMessage("§c".$name .$this->setting->get("broadcastjoin2 for op"));	
				}
			}
		}//broadcastmessage
		
		if(!$this->playerdata->exists("$name")){
				$this->playerdata->set("$name","now testing");
				$this->playerdata->save();
		}//プレイヤーの登録。
	}

	public function onCommand(CommandSender $sender,Command $command,$label,array $args){
		$commander = $sender->getName();
		switch($command->getName()) {
			
		case "rule":
			if(!isset($args[0])){
				return false;
			}else{
				if($this->setting->get("I/O rule")){
					if(!ctype_digit($args[0])){
						$sender->sendMessage("§cページの入力方法が間違ってます");
					}else{
						if($args[0] == 0){
							$sender->sendMessage("§cWelcomeMessage v2.0");
							$sender->sendMessage("§cこのプラグインの作者:ogiwara");
							$sender->sendMessage("§c連絡は@CostlierRain464まで");
							break;
						}
						
						if($args[0]*7-$this->numdata->get("rulesum") > 6){
							$sender->sendMessage("§cページの最大数を超えています。");
						}else{
							$sender->sendMessage($this->setting->get("ruleEdge"));
							for($i = 1+($args[0]-1)*7;$i <= 7*$args[0];$i++){
								$sender->sendMessage($this->setting->get("rule${i}"));
									if(!$this->setting->exists("rule${i}")){
									break;
									}
							}
								if(!$this->numdata->get("rulesum")%7 == 0){
									$sss = $this->numdata->get("rulesum")/7+1;
									settype($sss,"integer");
								$sender->sendMessage("§d~~~~~~~~<" .$args[0]."§d／" .$sss ."§d>~~~~~~~~");	
								}else{
									$sws = $this->numdata->get("rulesum")/7;
									settype($sws,"integer");
								$sender->sendMessage("§d~~~~~~~~<" .$args[0]."§d／" .$sws ."§d>~~~~~~~~");
								}
						}
					}
				}else{
					$sender->sendMessage(TextFormat::RED ."/rule has been disabled.");
				}
			}
			return true;
			break;
		case "setrule":
			if(!isset($args[0])){
				return false;
			}else{
				if(!ctype_digit($args[0])){
					$sender->sendMessage("§c入力方法が間違っています");
				}else{
					if(!$args[0] == 0){
						if($args[0]-1 > $this->numdata->get("rulesum")){
							$sender->sendMessage(TextFormat::RED ."ルールの数をスキップしないで下さい");
						}else{
							$this->setting->set("rule${args[0]}","$args[1]");
							$this->setting->save();
							if($args[0]-1 == $this->numdata->get("rulesum")){
								$this->numdata->set("rulesum","$args[0]");
								$this->numdata->save();
							}
							$sender->sendMessage(TextFormat::LIGHT_PURPLE ."新しいルールを追加しました。 ルール$args[0] \"$args[1]\"");
							$this->getServer()->broadcastMessage("§cルール$args[0]が§6".$commander ."§cによって更新されました。");
						}
					}else{
							$sender->sendMessage("§c入力方法が間違っています");
					}
				}
			}
		return true;
		break;
		case "tips":
			if($this->setting->get("I/O rule")){
				$sender->sendMessage($this->setting->get("tipsEdge"));
				$sender->sendMessage($this->setting->get("tip1"));
				$sender->sendMessage($this->setting->get("tip2"));
				$sender->sendMessage($this->setting->get("tip3"));
				$sender->sendMessage($this->setting->get("tip4"));
				$sender->sendMessage($this->setting->get("tip5"));
				$sender->sendMessage(TextFormat::GOLD ."~~~~~~Tips一覧~~~~~~");
			}else{
				$sender->sendMessage(TextFormat::RED ."/tip has been disabled.");
			}
			return true;
			break;
		case "settips":
			if(!isset($args[0])){
				return false;
			}else{
				if(!ctype_digit($args[0])){
					$sender->sendMessage("§c入力方法が間違っています");
				}else{
					if($args[0] > 5){
						$sender->sendMessage(TextFormat::RED ."tipsの最大数は5個までです");
					}else{
						if(!$args[0] == 0){
						$this->setting->set("tip${args[0]}","${args[1]}");
						$this->setting->save();
						$sender->sendMessage(TextFormat::GOLD ."新しいtipsを追加しました。 tip${args[0]} \"${args[1]}\"");
						$this->getServer()->broadcastMessage("§ctip${args[0]}が§6".$commander ."§cによって更新されました。");
						}else{
							$sender->sendMessage("§c入力方法が間違っています");
						}
					}
				}
			}
		return true;
		break;
		case "hochi":
			if($sender instanceof Player){
				if($this->setting->get("I/O hochi")){
					$player = $sender->getPlayer();
					$name = $player->getDisplayName();
					if($this->hochi->exists("$name")){
						$name = $this->hochi->get("$name");
						$this->getServer()->broadcastMessage("§e(｀・ω・´)§3".$name ."§eが放置を解除しました");
						$this->hochi->remove($name ."(ほーち(´・ω・｀))");
						$this->hochi->save();
						$player->setDisplayName($name);
						$player->save();
					}else{
						$this->getServer()->broadcastMessage("§e(´・ω・`)§3".$name ."§eが放置を開始しました");
						$this->hochi->set("{$name}(ほーち(´・ω・｀))","$name");
						$this->hochi->save();
						$player->setDisplayName($name ."(ほーち(´・ω・｀))");
						$player->save();
					}
				}else{
					$sender->sendMessage("§c放置機能は無効になっています");
				}
			}else{
				$sender->sendMessage("(´・ω・｀)");
			}
		return true;
		break;
		}
		return false;
	}
	public function onPlayerQuit(PlayerQuitEvent $event){
		if($this->setting->get("I/O broadcastlogout")){
		$event->setQuitMessage("");
		$player = $event->getPlayer();
		$name = $player->getName();
			if(!$player->isOp()){
				$this->getServer()->broadcastMessage("§b".$name .$this->setting->get("broadcastlogout"));
			}else{
				$this->getServer()->broadcastMessage("§c".$name .$this->setting->get("broadcastlogout for op"));
			}
		}
	}
	
	public function onDisable(){
		unlink($this->getDataFolder()."hochi.yml");
	}
}