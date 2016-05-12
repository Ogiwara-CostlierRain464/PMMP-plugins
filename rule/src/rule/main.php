<?php

namespace rule;

use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;//これらは、ほぼすべてのプラグインで使う

use pocketmine\command\Command;
use pocketmine\command\CommandSender;//コマンド関連。

use pocketmine\utils\Config;

class main extends PluginBase implements Listener{
	
	const version = "v1.0 MWcover";
	
	public function onEnable(){
		if(!file_exists($this->getDataFolder())){ 
		mkdir($this->getDataFolder(), 0755, true); 
		}
		$this->filelist = new Config($this->getDataFolder() . "filelist.data", Config::YAML, array(
		"id123456" => "禁止事項"
		));
		
		$this->id123456 = new Config($this->getDataFolder() . "id123456.yml", Config::YAML, array(
		"1" => "§c以下の行為(荒らし行為)は、禁止","2" => "暴言、不法侵入、資源ワールドの無作為な資源確保","3" => "不適切な名前、発言、意味不明な文字の羅列",
		"4" => "物をねだる行為、不愉快な行為,個人情報を聞き出す行為", "5" => "意図的なバグの利用、その他非常識な行動","6" => "§cこれらを破った場合、それなりの処罰を下します"
		));
		
		$this->match = new Config($this->getDataFolder() . "match.data", Config::YAML, array(
		"禁止事項" => "id123456"
		));
		
		$this->getLogger()->notice("rule " .self::version ."をご利用いただき、ありがとうございます 作者 ogiwara");
		$this->getLogger()->notice("このプラグインの二次配布は禁止です");
		$this->getLogger()->notice("不具合が発生した場合は、Twitterの@CostlierRain464まで");
		$this->getLogger()->notice("Copyright © 2016 ogiwara(CostlierRain464) All Rights Reserved.");		
	}
	
	public function onCommand(CommandSender $sender,Command $command,$label,array $args){
		switch($command->getName()){
			case "rule":
				if(!isset($args[0])){
					$sender->sendMessage("§b＊-=======ルールフォルダ一覧=======-＊");
					$msgs = $this->filelist->getAll();
					foreach($msgs as $msg){
						$sender->sendMessage("§6▶".$msg);
					}
					$sender->sendMessage("§b/rule <フォルダ名>で、各フォルダ内のルールを確認できます");
				}else{		
					if($this->match->exists($args[0])){
						$sender->sendMessage("§6◢ " .$args[0] .">>-=-=-=-=-=-=");
						$this->{$this->match->get($args[0])} =  new Config($this->getDataFolder() ."{$this->match->get($args[0])}.yml", Config::YAML);
						$msgs = $this->{$this->match->get($args[0])}->getAll();
						if(!isset($msgs[1])){
							$sender->sendMessage("§aⓘこのフォルダには、まだルールがないようです");
						}else{
							foreach($msgs as $msg){
								$sender->sendMessage("§a- ".$msg);
							}
						}
					}else{
						$sender->sendMessage("§e>>ⓘそのようなルールフォルダは存在しません");
					}
				}
			return true;
			break;
			
			case "setrule":
				if(!isset($args[0])){
					$sender->sendMessage("§6＊-=======rule " .self::version ."=======-＊");
					$sender->sendMessage("/setrule：");
					$sender->sendMessage("§6| §b<new/n/+><フォルダー名>で、ルールフォルダの生成");
					$sender->sendMessage("§6| §b<add/a><フォルダ名><追加するルール>[上書きする項目]で、ルールの追加、上書き");
					$sender->sendMessage("§6| §b<delete/remove/d/r><フォルダ名>で、ルールフォルダの削除");
				}else{
					switch($args[0]){
						case "new":
						case "n":
						case "+":
							if(!isset($args[1])){
								$sender->sendMessage("§e>>ⓘルールフォルダ名を入力して下さい");
							}else{
								$mut = $this->getDataFolder()."{$this->match->get($args[1])}.yml";
								if(!file_exists($mut)){
									$count = $this->filelist->getAll();
									if(isset($count[9])){
										$sender->sendMessage("§e>>ⓘこれ以上追加出来ません");
									}else{
									$land = uniqid("id");
									$this->filelist->set($land,$args[1]);
									$this->filelist->save();
									$this->match->set($args[1],$land);
									$this->match->save();
									$this->{$land} =  new Config($this->getDataFolder() ."{$land}.yml", Config::YAML);
									$name = $sender->getName();									
									$this->getServer()->broadcastMessage("§c[rule]" .$name ."が'" .$args[1] ."'ルールフォルダを作成しました");
									}
								}else{
									$sender->sendMessage("§e>>ⓘ" .$args[1] ."というフォルダ名は既に存在します。別の名前を入力して下さい");
								}
							}
						break;
						
						case "add":
						case "a":
							if(!isset($args[2])){
								$sender->sendMessage("§e>>ⓘルールフォルダ名、もしくはを入力して下さい");
							}else{
								if($this->match->exists($args[1])){
									$land = $this->match->get($args[1]);
									$this->{$land} =  new Config($this->getDataFolder() ."{$land}.yml", Config::YAML);
									//Max searcher////
									$i = 1;
									while($i){
										if(!$this->{$land}->exists($i)){
												break;
										}else{
										++$i;
										}
									}
									
										if(!isset($args[3])){
											if($i == 11){
												$sender->sendMessage("§e>>ⓘこれ以上追加出来ません");
											}else{
											$this->{$land}->set($i,$args[2]);
											$this->{$land}->save();
											$name = $sender->getName();
											$this->getServer()->broadcastMessage("§c[rule]" .$name ."が'" .$args[1] ."'にルールを追加しました");
											}
										}else{
											if($this->{$land}->exists($args[3])){
												$this->{$land}->set($args[3],$args[2]);
												$this->{$land}->save();
												$name = $sender->getName();
												$this->getServer()->broadcastMessage("§c[rule]" .$name ."が'" .$args[1] ."'のルール" .$args[3] ."を更新しました");												
											}else{
												$sender->sendMessage("§e>>ⓘ入力が正しくありません");
											}
										}
								}else{
									$sender->sendMessage("§e>>ⓘそのようなフォルダは見つかりませんでした");
								}
							}
						break;
						
						case "delete":
						case "remove":
						case "r":
						case "d":
							if(!isset($args[1])){
								$sender->sendMessage("§e>>ⓘ削除するフォルダ名を入力して下さい");
							}else{
								if($this->match->exists($args[1])){
									$delid = $this->match->get($args[1]);
									unlink($this->getDataFolder()."{$delid}.yml");
									$this->match->remove($args[1]);
									$this->match->save();
									$this->filelist->remove($delid);
									$this->filelist->save();
									$name = $sender->getName();
									$this->getServer()->broadcastMessage("§c[rule]" .$name ."が" .$args[1] ."フォルダを削除しました");
								}else{
									$sender->sendMessage("§e>>ⓘ該当するフォルダ名が見つかりませんでした");
								}
							}
						break;
					}
				}
			return true;
			break;
		}
	}
}
	
	
	