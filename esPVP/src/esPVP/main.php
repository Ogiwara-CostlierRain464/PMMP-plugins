<?php

namespace esPVP;

use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;//基本的なUSE

use pocketmine\math\Vector3;//座標指定
use pocketmine\level\Level;
use pocketmine\block\Block;//level関連
use pocketmine\level\Position;

use pocketmine\utils\Config;//Config関連。

use pocketmine\command\Command;
use pocketmine\command\CommandSender;//コマンド関連。

use pocketmine\event\player\PlayerRespawnEvent;//quit event
use pocketmine\event\player\PlayerQuitEvent;//quit event
use pocketmine\event\player\PlayerDeathEvent;//death event
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\item\Item;//アイテム関連

use onebone\economyapi\EconomyAPI;//economyapi

class main extends PluginBase implements Listener{
	
	public function onEnable(){
		if(!file_exists($this->getDataFolder())){ 
		mkdir($this->getDataFolder(), 0755, true); 
		}
		$this->setting = new Config($this->getDataFolder() . "setting.yml", Config::YAML, array(
			"life" => 4,"pvpplace" => "default",//機数、場所
			"helmet" => "298","chestplate" => "299","leggings" => "300","boots" => "301",//装備
			"item1" => "267,0,2","item2" => "261,0,1","item3" => "262,0,64","item4" => "322,0,2",//アイテム
			"prizemoney" => 1500,"/quitpenalty" => 50,"quiteventpenalty" => 300,//お金関連
			"list1" => "<default>: world 100,100,100","placesum" => 1,//setpvp list の表示の時使う
			"silent" => false
			));
			
		$this->default = new Config($this->getDataFolder() ."default.yml", Config::YAML, array(
			"world" => "world","xyz1" => "100,73,100","xyz2" => "110,73,110","list" => 1//デフォルトのpvp会場
			));
		
		$this->winners = new Config($this->getDataFolder() . "winners.yml", Config::YAML);//MVPを記録する。
		
		$this->getServer()->getPluginManager()->registerEvents($this,$this);//イベント登録
		$this->pvpplace = $this->setting->get("pvpplace");
		$this->{$this->pvpplace} =  new Config($this->getDataFolder() ."{$this->pvpplace}.yml", Config::YAML);
		$pvpworld = $this->{$this->pvpplace}->get("world");//success!!
		$this->level = Server::getInstance()->getLevelByName($pvpworld); 
		if(!($this->level instanceof Level)){//レベルオブジェクトかの判定,違う場合は以下の処理
			if(is_dir(Server::getInstance()->getDataPath() . "worlds/" . $pvpworld . "/")){
				$this->getLogger()->notice("§c>§6>§2>§6ワールド" . $pvpworld . "を読み込みます"); 
				Server::getInstance()->loadLevel($pvpworld); 
					if(Server::getInstance()->getLevelByName($pvpworld) instanceof Level){//レベルオブジェクトかの判定//違う場合は以下の処理 
 						$this->level = Server::getInstance()->getLevelByName($pvpworld); 
 						$this->getLogger()->notice("§c>§6>§2>§6ワールド" . $pvpworld . "を使用します"); 
 					} 
 			}else{ 
 					$this->getLogger()->warning("§c[esPVP]>>>ワールド${pvpworld}が存在しません!"); 
 					$this->getLogger()->warning("§c[esPVP]>>>デフォルトで使用されるワールドを使用します"); 
 					$this->level = Server::getInstance()->getDefaultLevel(); 
 			}
 		}
		$this->pvping = false;
		$this->playernum = 0;
	}		
	
	public function onPlayerRespawn(PlayerRespawnEvent $event){
		
		$player = $event->getPlayer();
		$name = $player->getName();
		if(isset($this->rink[$name])){
			$this->pvpmode($player);
			$xyz{$this->rink[$name]} = explode(',', $this->{$this->pvpplace}->get("xyz{$this->rink[$name]}"));
			$vector = new Position($xyz{$this->rink[$name]}[0],$xyz{$this->rink[$name]}[1],$xyz{$this->rink[$name]}[2],$this->level);
			$player->teleport($vector);
		}
	}
	
	public function onCommand(CommandSender $sender,Command $command,$label,array $args){
		switch (strtolower($command->getName())) {//コマンド名で条件分岐
			case "joinpvp":
				if($sender instanceof Player){
					$player = $sender->getPlayer();
					$name = $player->getName();
					if($this->pvping){
						$sender->sendMessage("§3[esPVP]§c>§6>§2>§3PVPは既に開始しました");
					}else{
						if(isset($this->rink[$name])){
							$sender->sendMessage("§3[esPVP]§c>§6>§2>§3あなたは既に参加しています");
						}else{
							if($this->playernum == 0){
								$this->playernum = 1;
								$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c". $name ."§cがPVPを申し込みました");
								$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c参加する場合は、自宅のチェストに荷物を入れ、/joinpvp");
								$xyz1 = explode(',',$this->{$this->pvpplace}->get("xyz1"));
								$vector = new Position($xyz1[0],$xyz1[1],$xyz1[2],$this->level);
								$player->teleport($vector);
								$this->pvper[1][0] = "$name";
								$this->pvper[1][1] = $this->setting->get("life");
								$this->rink[$name] = 1;
								$this->pvpmode($player);
							}else{
								$numcounter = $this->playernum+1;
								if($this->{$this->pvpplace}->exists("xyz{$numcounter}")){
									++$this->playernum;
									$this->getServer()->broadcastMessage("§a[esPVP]§c>§6>§2>§a". $name ."§aがPVPに参加しました");
									$xyz{$this->playernum} = explode(',', $this->{$this->pvpplace}->get("xyz{$this->playernum}"));
									$vector = new Position($xyz{$this->playernum}[0],$xyz{$this->playernum}[1],$xyz{$this->playernum}[2],$this->level);
									$player->teleport($vector);
									$this->pvper[$this->playernum][0] = "$name";
									$this->pvper[$this->playernum][1] = $this->setting->get("life");
									$this->rink[$name] = $this->playernum;
									$this->pvpmode($player);
									++$numcounter;
									if(!$this->{$this->pvpplace}->exists("xyz{$numcounter}")){
										$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c締め切ります");
									}
								}else{
									$sender->sendMessage("§c[esPVP]§c>§6>§2>§cこれ以上参加出来ません");
								}
							}
						}
					}
				}else{
					$sender->sendMessage("§c[esPVP]§c>§6>§2>§cあなたは参加出来ません");
				}
				return true;//これで、コマンドの説明を回避
				break;
			case "quitpvp":
				if($sender instanceof Player){
					$player = $sender->getPlayer();
					$name = $player->getName();
					if(!isset($this->rink[$name])){
						$sender->sendMessage("§3[esPVP]§c>§6>§2>§3あなたはPVPに参加していません");
					}else{
						if($this->pvping){
							$penalty = $this->setting->get("/quitpenalty");
							$sender->sendMessage("§3[esPVP]§c>§6>§2>§3あなたはPVPを放棄したので".$penalty."円罰金です");
							$sender->sendMessage("§3[esPVP]§c>§6>§2>§3/spawnで戻って下さい");
							$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c".$name."がPVPの途中で離脱しました");
							EconomyAPI::getInstance()->reduceMoney($name, $penalty);
							$player->getInventory()->clearAll();
							unset($this->pvper[$this->rink[$name]]);
							unset($this->rink[$name]);
							--$this->playernum;
							if($this->playernum ==1){
								$this->endofpvp();
							}
						}else{
							$sender->sendMessage("§3[esPVP]§c>§6>§2>§3PVPは開始していないので罰金はありません");
							$sender->sendMessage("§3[esPVP]§c>§6>§2>§3/spawnで戻って下さい");
							$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c".$name."がPVPをキャンセルしました");
							$player->getInventory()->clearAll();
							unset($this->pvper[$this->rink[$name]]);
							unset($this->rink[$name]);
							--$this->playernum;
							if($this->playernum == 0){
								$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c参加者が誰もいないので、PVPの申請は破棄されました");
							}
						}
					}
				}else{
					$sender->sendMessage("§3[esPVP]§c>§6>§2>§3貴方はこのコマンドを実行できません");
				}
				return true;
				break;
			case "startpvp":
				if($this->playernum >= 2){
					$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c只今よりPVPを開始します");
					$this->pvping = true;
				}else{
					$sender->sendMessage("§3[esPVP]§c>§6>§2>§3人数が足りません");
				}
				return true;
				break;
			case "toppvp"://ただいま検証中
				$data = $this->winners->getAll();
				$sender->sendMessage("Top of PVP");
				$sender->sendMessage($data["ogiwara"]);
				return true;
				break;
			case "endpvp":
				if($sender instanceof Player){
					$player = $sender->getPlayer();
					$name = $player->getName();
				}else{
					$name = "コンソール";
				}
				
				if(isset($args[0])){
					$this->getServer()->broadcastMessage("§c>§6>§2>§cesPVPを強制終了しています...");
					$this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("esPVP")); 
					break;
				}
				
				if($this->pvping){
					$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c".$name ."によって、PVPが強制終了されました");
					unset($this->pvper);
					unset($this->rink);
					
					$this->pvping = false;
					$this->playernum = 0;
				}else{
					$sender->sendMessage("§3[esPVP]§c>§6>§2>§3PVPは始まっていません");
				}
				return true;
				break;
			case "rulepvp":
					$sender->sendMessage("=====PVPのルール=====");
					$sender->sendMessage("/joinpvpでPVPに参加、/startpvpでPVPを開始します");
					$sender->sendMessage("優勝者には賞金" .$this->setting->get("prizemoney") ."\$が与えられます");
					$sender->sendMessage("途中で抜ける場合は、/quitpvpと入力して下さい");
					$sender->sendMessage("罰金について↓");
					$sender->sendMessage("/quitpvpコマンド:" .$this->setting->get("/quitpenalty") ."\$ サーバーからログアウトした場合:" .$this->setting->get("quiteventpenalty") ."\$");
					$sender->sendMessage("プレイヤーは、参加時に、サバイバルモードとなり、");
					$i = 1;
					while($i == true){
						if(!$this->setting->exists("item{$i}")){
						break;
						}
					static $list = "ID ";
					$item{$i} = explode(',',$this->setting->get("item{$i}"));
					$add = $item{$i}[0] .":" .$item{$i}[1] ."が" .$item{$i}[2] ."個、";
					$list .= $add;
					++$i;
					}
					$sender->sendMessage($list ."が付与されます");
					$sender->sendMessage("何らかの問題が発生した場合は、/endpvpで、PVPを強制終了し、");
					$sender->sendMessage("/endpvpで、PVPを強制終了し、/endpvp true で、esPVPを強制終了します");
					$sender->sendMessage("pvpの詳細は、/infopvpで確認できます");
				return true;
				break;
			case "infopvp":
				$sender->sendMessage("=====PVPの詳細=====");
				$sender->sendMessage("ワールドリスト↓");
				/////Skipper////////
				for($i = 1;$i <= 100;$i++){
					if(!$this->setting->exists("list{$i}")){
						++$i;
					continue;
					}
				$sender->sendMessage($this->setting->get("list{$i}"));
				}
				//////////////////////
				$sender->sendMessage("只今選択されている場所は、" .$this->pvpplace ."です");
				return true;
				break;
			case "setpvp":
			if($this->playernum >= 1){
				$sender->sendMessage("只今PVPを準備中、もしくは開催中なので、esPVPの設定の変更はできません");
			}else{
				if(!isset($args[0])){return false;}//説明を表示
				switch($args[0]){
					
					case "help":
					case "?":
					case "h":
					$sender->sendMessage("=====esPVP v2.0 設定画面=====");
					$sender->sendMessage("/setpvp ↓");
					$sender->sendMessage("| <c/choose><name>で、PVPを行う場所を選択");
					$sender->sendMessage("| <new/n/+><name>で、PVPを行う場所の新規作成");
					$sender->sendMessage("| <pos/p><name><num>で、num番目のPVP参加者のスポーン地点変更");
					$sender->sendMessage("| <item/i><項目数><id>[damage][個数]で、(項目数)目のアイテムのid(及びdamage,個数)の変更");
					$sender->sendMessage("| <s/silent>で、esPVPをサイレントモードのオン/オフを切り替えます");
					$sender->sendMessage("| <d/delete>でPVPの場所の削除を行います");
					
					return true;
					break;
					
					case "c":
					case "choose"://ただいま作成中
					if(!isset($args[1])){
						$sender->sendMessage("PVPの場所名を入力して下さい");
						$sender->sendMessage("/infopvpから確認できます");
					}else{
						$mut = $this->getDataFolder()."{$args[1]}.yml";
						if(file_exists($mut)){
							$this->setting->set("pvpplace",$args[1]);
							$this->pvpplace = $this->setting->get("pvpplace");
							$this->{$this->pvpplace} =  new Config($this->getDataFolder() ."{$args[1]}.yml", Config::YAML);
							$pvpworld = $this->{$this->pvpplace}->get("world");//success!!
							$this->level = Server::getInstance()->getLevelByName($pvpworld); 
							if(!($this->level instanceof Level)){//レベルオブジェクトかの判定,違う場合は以下の処理
								if(is_dir(Server::getInstance()->getDataPath() . "worlds/" . $pvpworld . "/")){
									$sender->sendMessage("§c>§6>§2>§6ワールド" . $pvpworld . "を読み込みます"); 
									Server::getInstance()->loadLevel($pvpworld); 
										if(Server::getInstance()->getLevelByName($pvpworld) instanceof Level){//レベルオブジェクトかの判定//違う場合は以下の処理 
											$this->level = Server::getInstance()->getLevelByName($pvpworld); 
											$sender->sendMessage("§c>§6>§2>§6ワールド" . $pvpworld . "を使用します"); 
										} 
								}else{ 
										$sender->sendMessage("§c[esPVP]>>>ワールド${pvpworld}が存在しません!"); 
										$sender->sendMessage("§c[esPVP]>>>デフォルトで使用されるワールドを使用します"); 
										$this->level = Server::getInstance()->getDefaultLevel(); 
								}
							}
							//Max searcher////
							$i = 1;
							while($i){
								if(!$this->{$this->pvpplace}->exists("xyz{$i}")){
									break;
								}
								$max = $i;
								++$i;
							}								
							$sender->sendMessage("PVPの場所は" .$args[1] ."に変更されました");
							$sender->sendMessage("ワールド:" .$this->{$this->pvpplace}->get("world") .
															"座標 :" .$this->{$this->pvpplace}->get("xyz1") .
															"最大人数 :" .$max ."人");
							$this->{$this->pvpplace}->save();
						}else{
							$sender->sendMessage("該当するPVPの場所が見つかりませんでした");
							$sender->sendMessage("/infopvpで、PVPの場所のリストをご確認ください");
						}
					}
					return true;
					break;

					case "new":
					case "n":
					case "+":
					if($sender instanceof Player){
					if(!isset($args[1])){
						$sender->sendMessage("作成するPVPの場所名を入力して下さい");
					}else{
						$mut = $this->getDataFolder()."{$args[1]}.yml";
						if(!file_exists($mut)){
							$player = $sender->getPlayer();
							$x = (Int)$player->x;
							$y = (Int)$player->y;
							$z = (Int)$player->z;
							$xyz = $x ."," .$y .",".$z;
							$level = $player->getLevel();
							$levname = $level->getName();
							//Max searcher////
							$i = 1;
							while($i){
								if(!$this->setting->exists("list{$i}")){
									$max = $i;
									break;
								}
								++$i;
							}
							////////////////////
							$this->{$args[1]} = new Config($this->getDataFolder() ."{$args[1]}.yml", Config::YAML, array(
							"world" => $levname,"xyz1" => $xyz,"xyz2" => $xyz,"list" => $max
							));
							
							$this->setting->set("list{$max}","<" .$args[1] .">" .": " .$levname ." ".$x ."," .$y ."," .$z);
							$this->setting->save();
							$sender->sendMessage($args[1] ."を新たに作成しました");
							$sender->sendMessage("ワールド:" .$levname .
															"座標 :" .$xyz.
															"最大人数 : 現在二人");
						}else{
							$sender->sendMessage($args[1] ."というPVPの場所は既に存在します。別の名前を入力して下さい");
						}
					}
					}else{
							$sender->sendMessage("プレイヤーのみがこのコマンドを実行できます");
					}
					return true;
					break;
					
					case "pos":
					case "p":
					if($sender instanceof Player){
						if(!isset($args[1]) || !isset($args[2])){
							$sender->sendMessage("PVPの場所か、何人目のプレイヤーのポジションかが記入されていません");
						}else{
							$mut = $this->getDataFolder()."{$args[1]}.yml";
							if(!file_exists($mut)){
								$sender->sendMessage("そのような名前のPVPプロファイルは見つかりませんでした");
							}else{
								if(!ctype_digit($args[2])){
									$sender->sendMessage("プレイヤー数の入力が正しくありません");
								}else{
									//Max searcher////
									$i = 1;
									while($i){
										if(!$this->{$args[1]}->exists("xyz{$i}")){
											$max = $i;
											break;
										}
									++$i;
									}
									////////////////////
									if($args[2] > $max){
										$sender->sendMessage("次に設定できるのは、POS" .$max ."までです");
									}else{
										$player = $sender->getPlayer();
										$x = (Int)$player->x;
										$y = (Int)$player->y;
										$z = (Int)$player->z;
										$xyz = $x ."," .$y .",".$z;
										$this->{$args[1]} =  new Config($this->getDataFolder() ."{$args[1]}.yml", Config::YAML);
										$this->{$args[1]}->set("xyz{$args[2]}",$xyz);
										$this->{$args[1]}->save();
										$sender->sendMessage($args[1] ."プロファイルに、POS" .$args[2] .":座標" .$xyz ."を追加しました");
									}
								}
							}
						}
					}else{
						$sender->sendMessage("プレイヤーのみがこのコマンドを実行できます");
					}
					return true;
					break;
					
					case "s":
					case "silent":
					
					return true;
					break;
					
					case "d":
					case "delete":
						if(!isset($args[1])){
							$sender->sendMessage("削除するPVPプロファイル名を入力して下さい");
						}else{
							$mut = $this->getDataFolder()."{$args[1]}.yml";
							if(!file_exists($mut)){
								$sender->sendMessage("そのような名前のPVPプロファイルは見つかりませんでした");
							}else{
								$this->{$args[1]} =  new Config($this->getDataFolder() ."{$args[1]}.yml", Config::YAML);
								$dellink = $this->{$args[1]}->get("list");
								$this->setting->remove("list{$dellink}");
								$this->setting->save();
								unlink($this->getDataFolder()."{$args[1]}.yml");
								$sender->sendMessage("PVPプロファイル、" .$args[1] ."を削除しました");
							}
						}
					return true;
					break;
					
					case "item":
					case "i":
					if(!isset($args[1])|| !isset($args[2])){
						$sender->sendMessage("項目数、もしくはidを記入して下さい");
					}else{
						if(!ctype_digit($args[1]) || !ctype_digit($args[2])){
							$sender->sendMessage("正しい数値を記入して下さい");
						}else{
							$del = $args[1]-1;
							if($this->setting->exists("item{$del}") || $this->setting->exists("item{$args[1]}")){
								if(isset($args[3])){
									if(ctype_digit($args[3])){
										$damage = $args[3];
									}else{
										$damage = "0";
									}
								}else{
									$damage = "0";
								}
								
								if(isset($args[4])){
									if(ctype_digit($args[4])){
										$amount = $args[4];
									}else{
										$amount = "1";
									}
								}else{
									$amount = "1";
								}

								$this->setting->set("item{$args[1]}",$args[2] ."," .$damage ."," .$amount);
								$sender->sendMessage("item" .$args[1] ."に,ID" .$args[2] .":" .$damage ."を" .$amount ."個設定しました");
								$this->setting->save();
							}else{
								$i = 1;
								while($i == true){
									if(!$this->setting->exists("item{$i}")){
										break;
									}
								$mux = $i+1;
								++$i;
								}
								$sender->sendMessage("次に設定できるのは、item" .$mux ."までです");
							}							
						}
					}
					return true;
					break;
					
				}
				return true;
				break;
				
			}
		}
		return false;
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(isset($this->rink[$name])){
			if($this->pvping){
				$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c".$name."がPVPの途中で退出しました");
				$penalty = $this->setting->get("quiteventpenalty");
				EconomyAPI::getInstance()->reduceMoney($name, $penalty);
				$player->getInventory()->clearAll();
				unset($this->pvper[$this->rink[$name]]);
				unset($this->rink[$name]);
				--$this->playernum;
				if($this->playernum ==1){
					$this->endofpvp();
				}				
			}else{
				$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c".$name."がPVPをキャンセルしました");
				$player->getInventory()->clearAll();
				unset($this->pvper[$this->rink[$name]]);
				unset($this->rink[$name]);
				--$this->playernum;
				if($this->playernum == 0){
					$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c参加者が誰もいないので、PVPの申請は破棄されました");
				}
			}	
		}
	}
	
	public function onPlayerDeath(PlayerDeathEvent $event){
		if($event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent){
			$player = $event->getEntity();
				if($player instanceof Player){
					$killer = $event->getEntity()->getLastDamageCause()->getDamager();
					$killername = $killer->getName();//殺したひと
					$dead = $event->getEntity();//殺された人
					$deadname = $event->getEntity()->getName();//死んだ人の名前
					if($this->pvping){
						if(isset($this->rink[$deadname])){
						$this->getServer()->broadcastMessage("§6[esPVP]§c>§6>§2>§4" .$deadname ."§6が§5" .$killername ."§6によってkillされました");
							if($this->pvper[$this->rink[$deadname]][1] ==1){
								unset($this->pvper[$this->rink[$deadname]]);
								unset($this->rink[$deadname]);
								--$this->playernum;
								if($this->playernum ==1){
									$this->endofpvp();
								}
							}else{
								--$this->pvper[$this->rink[$deadname]][1];
								$player->sendMessage("§c>§6>§2>§c貴方は残り" .$this->pvper[$this->rink[$deadname]][1] ."機です");
							}
						}
					}else{
						if(isset($this->rink[$deadname])){
							$this->getServer()->broadcastMessage("§6[esPVP]§c>§6>§2>§4" .$deadname ."§6が§5" .$killername ."§6によってkillされたので、PVPから離脱しました");
							$player->getInventory()->clearAll();
							unset($this->pvper[$this->rink[$deadname]]);
							unset($this->rink[$deadname]);
							--$this->playernum;
							if($this->playernum == 0){
								$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c参加者が誰もいないので、PVPの申請は破棄されました");
							}						
						}	
					}
				}
		}else{
			$player = $event->getEntity();
			$name = $player->getName();
			if(isset($this->rink[$name])){
				if($this->pvping){
					$this->getServer()->broadcastMessage("§6[esPVP]§c>§6>§2>§4" .$name ."§6が何らかの理由で死にました");
							if($this->pvper[$this->rink[$name]][1] ==1){
								unset($this->pvper[$this->rink[$name]]);
								unset($this->rink[$name]);
								--$this->playernum;
								if($this->playernum ==1){
									$this->endofpvp();
								}
							}else{
								--$this->pvper[$this->rink[$name]][1];
								$player->sendMessage("§c>§6>§2>§c貴方は残り" .$this->pvper[$this->rink[$name]][1] ."機です");
							}
				}else{
					$this->getServer()->broadcastMessage("§6[esPVP]§c>§6>§2>§4" .$name ."§6が何らかの理由で死に、PVPをキャンセルしました");
					$player->getInventory()->clearAll();
					unset($this->pvper[$this->rink[$name]]);
					unset($this->rink[$name]);
					--$this->playernum;
					if($this->playernum == 0){
					$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c参加者が誰もいないので、PVPの申請は破棄されました");
					}
				}
			}
		}
		
	}
					
	
	public function pvpmode($player){
		$player->setGamemode(0);
		$player->getInventory()->clearAll();
		$player->getInventory()->setArmorItem(0,Item::get($this->setting->get("helmet"),0,1));//ヘルメット
		$player->getInventory()->setArmorItem(1,Item::get($this->setting->get("chestplate"),0,1));//チェストプレート
		$player->getInventory()->setArmorItem(2,Item::get($this->setting->get("leggings"),0,1));//レギンス
		$player->getInventory()->setArmorItem(3,Item::get($this->setting->get("boots"),0,1));//ブーツ
		$player->getInventory()->sendArmorContents($player);//防具の変更を反映
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
			++$i;
		}
	}	
	
	public function endofpvp(){
		$this->pvping = false;
		$e = 1;
		while($e == true){	
			if(!$this->{$this->pvpplace}->exists("xyz{$e}")){
				break;
			}
			++$e;
		}
		
		for($i = 1;$i <= $e;$i++){
			if(isset($this->pvper[$i])){
			$winner = $this->pvper[$i][0];
			}
		}
		$this->getServer()->broadcastMessage("§c[esPVP]§c>§6>§2>§c". $winner ."が優勝しました！");
		$prize = $this->setting->get("prizemoney");
		EconomyAPI::getInstance()->addMoney($winner, $prize);
		if(!$this->winners->exists("$winner")){
			$this->winners->set("$winner","1");
			$this->winners->save();
		}else{
			$times = $this->winners->get("$winner");
			++$times;
			$this->winners->set("$winner", "$times");
			$this->winners->save();
		}
		unset($this->pvper);
		unset($this->rink);
		$this->playernum = 0;
		$this->pvping = false;
	}
}