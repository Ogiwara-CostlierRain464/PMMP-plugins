<?php

namespace ogiwara\batu;

use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;

use pocketmine\entity\Entity;

use pocketmine\event\player\PlayerJoinEvent;//プレイヤーがサーバーに参加した時のイベント。
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent; 

use pocketmine\utils\Config;//Config関連。

use pocketmine\command\Command;
use pocketmine\command\CommandSender;//コマンド

class batu extends PluginBase implements Listener{
	
	const version = "v2.0 UUI";
	
	public function onEnable(){

		$this->getLogger()->info("§4[罰]荒らし対策用プラグインが起動");
		$this->getLogger()->notice("batu " .self::version ."をご利用いただき、ありがとうございます 作者 ogiwara");
		$this->getLogger()->notice("このプラグインの二次配布は禁止です");
		$this->getLogger()->notice("不具合が発生した場合は、Twitterの@CostlierRain464まで");
		$this->getLogger()->notice("Copyright © 2016 ogiwara(CostlierRain464) All Rights Reserved.");
	
		if(!file_exists($this->getDataFolder())){ 
		mkdir($this->getDataFolder(), 0755, true); 
		}
		
		$this->batulist = new Config($this->getDataFolder() . "batulist.data", Config::YAML);
		
		$this->history = new Config($this->getDataFolder() . "history.data", Config::YAML);
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);//Event系を使う時は、これを必ず書くこと。
		
	}
	
	public function onPlayerJoin(PlayerJoinEvent $event){//プレイヤーが参加した時のイベント
	
		$player = $event->getPlayer(); //イベントからプレイヤー取得
		$name = strtolower($player->getName());//プレイヤー名の取得
		if($this->batulist->exists($name)){
			$player->setDisplayName("§c[罰]" .$player->getDisplayName());
			$player->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 1);
		}
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch($command->getName()){
			case "batu":
				$sname = $sender->getName();
				$time = date("Y年m月d日/H時i分");
				if(!isset($args[0])){
					$sender->sendMessage("§eⓘ>>プレイヤー名を入力して下さい");
				}else{
					$player = $this->getServer()->getPlayer(strtolower($args[0]));
					if($player instanceof Player){
						$name = strtolower($player->getName());
						if(!$this->isbatu($name)){
							$this->setbatu($name,$sname,$time);
							if(!$this->hasbatued($name)){
								$this->history->set($name,1);
								$this->history->save();
							}else{
								$count = $this->history->get($name);
								++$count;
								$this->history->set($name,$count);
								$this->history->save();
							}
							$this->getServer()->broadcastMessage("§c[罰]" .$sname ."が" .$name ."を、batuしました");
							$player->setDisplayName("§c[罰]" .$player->getDisplayName());
							$player->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 1);
						}else{
							$this->batulist->remove($name);
							$this->batulist->save();
							$this->getServer()->broadcastMessage("§a[罰]" .$sname ."が" .$name ."を釈放しました");
							$player->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 0);
							$player->setDisplayName(str_replace("§c[罰]","",$player->getDisplayName()));
						}
					}else{
						$mut = $this->getServer()->getDataPath() .'players/' . strtolower($args[0] .'.dat');
						if(file_exists($mut)){						
							if(!$this->batulist->exists(strtolower($args[0]))){
								$this->batulist->set($args[0],$sname ."," .$time);
								$this->batulist->save();							
								$this->getServer()->broadcastMessage("§c[罰]" .$sname ."がオフラインの".$args[0] ."をbatuしました");
								if(!$this->hasbatued($args[0])){
									$this->history->set($args[0],1);
									$this->history->save();
								}else{
									$count = $this->history->get($args[0]);
									++$count;
									$this->history->set($args[0],$count);
									$this->history->save();
								}
							}else{
								$this->batulist->remove($args[0]);
								$this->batulist->save();
								$this->getServer()->broadcastMessage("§a[罰]" .$sname ."がオフラインの" .$args[0] ."を釈放しました");							
							}
						}else{
							$sender->sendMessage("§eⓘ>>該当するプレイヤーが見つかりませんでした");
							$sender->sendMessage("§eⓘ>>フルネームで記入して下さい");
						}
					}
				}
			return true;
			break;
			
			case "batulist":
				$batus = $this->batulist->getAll(true);
				$batui = $this->batulist->getAll();
				if(!isset($args[0])){
					$args[0] = "1";
				}
						
				if(!ctype_digit($args[0])){
					$sender->sendMessage("§eⓘ>>整数を入力して下さい");
				}else{
					$count = count($batus);
					$pagemax = $count/8;
					settype($pagemax,"integer");
								
					if($count%8 != 0){
						++$pagemax;
					}
					if($args[0] > $pagemax){
						$sender->sendMessage("§eⓘ>>ページの最大数は" .$pagemax ."です");
					}else{
						$sender->sendMessage("§a§o＊-========batulist-" .$args[0] ."／" .$pagemax ."========-＊");
						if($count == 0){
							$sender->sendMessage("§e>>batuされた人はまだいないようです");
						}else{
							$saisyo = $args[0]*8 -8;
							$saidai = $args[0]*8 - 1;
							for($i = $saisyo; $i <= $saidai; $i++){
								if(!isset($batus[$i])){
									break;
								}else{
									$batuph = explode(',',@$batui[$batus[$i]]);
									$sender->sendMessage("§6| §b" .@$batus[$i] ." ≫ §6batuしたop : [" .$batuph[0] ."] §3日時 : [" .$batuph[1] ."]");
								}
							}
						}
					}
				}
			return true;
			break;
			
			case "setbatu":
			if(!isset($args[0])){
				$sender->sendMessage("§9§o＊-========Batu " .self::version ." 設定/Setting========-＊");
				$sender->sendMessage("§b/setx (list/l) (ページ数) || batulistを表示(/xlistでも可)");
				$sender->sendMessage("§b/setx (check/c) (name) || プレイヤーのbatu情報をチェック");
				$sender->sendMessage("§b/setx (remove/r) || batuされたプレイヤーのデータを削除");
			}else{
				switch($args[0]){
					case "list":
					case "l":
						$batus = $this->batulist->getAll(true);
						$batui = $this->batulist->getAll();
						if(!isset($args[1])){
							$args[1] = "1";
						}
						
						if(!ctype_digit($args[1])){
							$sender->sendMessage("§eⓘ>>整数を入力して下さい");
						}else{
							$count = count($batus);
							$pagemax = $count/8;
							settype($pagemax,"integer");
								
							if($count%8 != 0){
								++$pagemax;
							}
							if($args[1] > $pagemax){
								$sender->sendMessage("§eⓘ>>ページの最大数は" .$pagemax ."です");
							}else{
								$sender->sendMessage("§a§o＊-========batulist-" .$args[1] ."／" .$pagemax ."========-＊");
								if($count == 0){
									$sender->sendMessage("§e>>batuされた人はまだいないようです");
								}else{
									$saisyo = $args[1]*8 -8;
									$saidai = $args[1]*8 - 1;
									for($i = $saisyo; $i <= $saidai; $i++){
										if(!isset($batus[$i])){
											break;
										}else{
											$batuph = explode(',',@$batui[$batus[$i]]);
											$sender->sendMessage("§6| §b" .@$batus[$i] ." ≫ §6batuしたop : [" .$batuph[0] ."] §3日時 : [" .$batuph[1] ."]");
										}
									}
								}
							}
						}
					break;
					
					case "check":
					case "c":
						if(!isset($args[1])){
							$sender->sendMessage("§eⓘ>>プレイヤー名を入力して下さい");
						}else{
							$player = $this->getServer()->getPlayer(strtolower($args[1]));
							if($player instanceof Player){
								$args[1] = $player->getName();
							}
							$sender->sendMessage("§a＊-========" .$args[1] ."のbatu状態========-＊");
							if(!$this->batulist->exists($args[1])){
								$sender->sendMessage("§6| §bbatu≫§afalse");
							}else{
								$sender->sendMessage("§6| §bbatu≫§ctrue");
								$batuinfo = explode(',',$this->batulist->get($args[1]));
								$sender->sendMessage("§6| §6batuした人 : " .$batuinfo[0]);
								$sender->sendMessage("§6| §d日時 : " .$batuinfo[1]);
							}
							
							if(!$this->history->exists($args[1])){
								$sender->sendMessage("§6| §bbatu回数≫まだbatuされたことはないようです");
							}else{
								$sender->sendMessage("§6| §bbatu回数≫" .$this->history->get($args[1]) ."回");
							}
						}
					break;
					
					case "remove":
					case "r":
					case "delete":
					case "d":
						if(!isset($args[1])){
							$sender->sendMessage("§eⓘ>>プレイヤー名を入力して下さい");
						}else{
							$player = $this->getServer()->getPlayer(strtolower($args[1]));
							if($player instanceof Player){
								$args[1] = $player->getName();
							}
							if(!$this->history->exists($args[1])){
								$sender->sendMessage("§eⓘ>>そのプレイヤーはまだbatuされたことはないようです");
							}else{
								$this->history->remove($args[1]);
								$this->history->save();
								$sender->sendMessage("§aⓘ>>" .$args[1] ."のbatuデータを削除しました");
							}
						}
					break;
					
					default:
						$sender->sendMessage("§eⓘ>>そのようなサブコマンドはありません");
					break;
				}
			}
			return true;
			break;
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if($this->batulist->exists($name)){
			$event->setCancelled();
			$player->sendTip("§c貴方は処罰の対象となっています!");
		}
	}
	public function onPreCommand(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if($this->batulist->exists($name)){
			if(!$player->isOp()){
				$m = $event->getMessage();
				if($m[0] == "/"){
				$event->setCancelled();
				$player->sendMessage("§c>>貴方は処罰の対象となっているので、コマンドは実行出来ません");
				}
			}
		}
	}
	
	public function onTouch(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if($this->batulist->exists($name)){
			$event->setCancelled();
			$player->sendTip("§c貴方は処罰の対象となっています!");
		}
	}
	
	public function onPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if($this->batulist->exists($name)){
			$event->setCancelled();
			$player->sendTip("§c貴方は処罰の対象となっています!");
		}
	}

	public function isbatu($name){
		if($this->batulist->exists($name)){
			return true;
		}else{
			return false;
		}
	}

	public function setbatu($name,$sname,$time){
		$this->batulist->set($name,$sname ."," .$time);
		$this->batulist->save();
		if(!$this->hasbatued($name)){
			$this->history->set($name,1);
			$this->history->save();
		}else{
			$count = $this->history->get($name);
			++$count;
			$this->history->set($name,$count);
			$this->history->save();
		}
	}

	public function unbatu($name){
		$this->batulist->remove($name);
		$this->batulist->save();
	}

	public function hasbatued($name){
		if($this->history->exists($name)){
			return true;
		}else{
			return false;
		}
	}

	public function removebatudata($name){
		$this->history->remove($name);
		$this->history->save();
	}
}
	
	
	