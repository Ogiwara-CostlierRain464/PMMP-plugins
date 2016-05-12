<?php

namespace DoorProtector;

use pocketmine\Player;
use pocketmine\Plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;//基本的なUSE

use pocketmine\math\Vector3;//座標指定
use pocketmine\level\Level;
use pocketmine\block\Block;//level関連

use pocketmine\item\Item;//アイテム関連

use pocketmine\event\player\PlayerInteractEvent;//プレイヤーのブロックタップイベント
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\RemovePacket;

use pocketmine\utils\Config;//Config関連。

use pocketmine\command\Command;
use pocketmine\command\CommandSender;//コマンド関連。
use pocketmine\command\CommandExecuter;
use pocketmine\command\ConsoleCommandSender;

class main extends PluginBase implements Listener{
		
		const version = 1.5;

	/*public function onSent(DataPacketSendEvent $event){
		$pk = $event->getPacket();
		if($pk instanceof UpdateBlockPacket){//設置時、破壊時に発生
			if($pk->records[0][3] == Item::WOOD_DOOR_BLOCK){
				$rpk = new RemoveBlockPacket();
				$rpk->x = $pk->records[0][0];
				$rpk->y = $pk->records[0][2];
				$rpk->z = $pk->records[0][1];
				Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(),$rpk);
			}
		}
	}*/
	
	public function onEnable(){
		if(!file_exists($this->getDataFolder())){
		mkdir($this->getDataFolder(), 0755, true); 
		}
		
		$this->getLogger()->notice("DoorProtector " .self::version ." をご利用いただき、ありがとうございます 作者 ogiwara");
		$this->getLogger()->notice("このプラグインの二次配布は禁止です");
		$this->getLogger()->notice("不具合が発生した場合は、Twitterの@CostlierRain464まで");
		$this->getLogger()->notice("Copyright © 2016 ogiwara(CostlierRain464) All Rights Reserved.");
		
		$this->doordata = new Config($this->getDataFolder() . "doordata.yml", Config::YAML);
		
		$this->history = new Config($this->getDataFolder() . "history.yml", Config::YAML);
		
		$this->setting = new Config($this->getDataFolder() . "setting.yml", Config::YAML, array(
		"protectnotice" => 1,"penalty" => 2,"beginnermode" => true
		));
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$notice = $this->setting->get("protectnotice");
		$penalty = $this->setting->get("penalty");
		$this->getLogger()->notice("§6【DP】ドア保護通知は、現在§b".$notice ."§6で、ペナルティーは§c".$penalty ."§6です");
	}
	
	public function onBlockTap(PlayerInteractEvent $event){
		
		
		if(
			$event->getBlock()->getID() == Item::WOOD_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::SPRUCE_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::BIRCH_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::JUNGLE_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::ACACIA_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::DARK_OAK_DOOR_BLOCK ||							//<------------ここで、触ったブロックの種類がドアかどうか判断
			$event->getBlock()->getID() == Item::SPRUCE_DOOR ||
			$event->getBlock()->getID() == Item::BIRCH_DOOR ||
			$event->getBlock()->getID() == Item::JUNGLE_DOOR ||
			$event->getBlock()->getID() == Item::ACACIA_DOOR ||
			$event->getBlock()->getID() == Item::DARK_OAK_DOOR ){
				
			$player = $event->getPlayer();
			$name = $player->getName();
			$ip = $player->getAddress();//ipアドレス
			$block = $event->getBlock();
			$doorx = $block->x;
			$doory = $block->y;
			$doorydecre = $doory;
			--$doorydecre;
			$dooryincre = $doory;
			++$dooryincre;
			$doorz = $block->z;
			$doorworld = str_replace(" ", "%", $block->getLevel()->getName());
			$doorplace = $doorx.",".$doory.",".$doorz.",".$doorworld;
			$doorplace2 = $doorx.",".$doorydecre.",".$doorz.",".$doorworld;
			$doorplace3 = $doorx.",".$dooryincre.",".$doorz.",".$doorworld;
			

			if($this->doordata->exists($doorplace2)){
					$doorplace = $doorplace2;												//<------------ここで、上のブロックを触った場合は下にある確認する
			}
				
				if(!isset($this->action[$name][0])){
					if($this->doordata->exists($doorplace)){ 
						$a = $this->doordata->get($doorplace);
						if($a["state"] != "public"){
						if($player->isOp()){
							$player->sendPopup("§6認証されました[OP]");
						}else{
							$master = explode(',',$this->getowner($doorplace));
							if($master[0] == $ip || $master[1] == $name){
								$player->sendPopup("§a認証されました[オーナー]");
							}else{
								if($this->isinviter($doorplace,$name)){
									$player->sendPopup("§d認証されました[招待者]");
								}else{
									$event->setCancelled();
									if(!$this->history->exists($name)){
										$this->history->set($name,1);
										$this->history->save();
									}else{
										$count = $this->history->get($name);
										++$count;
										$this->history->set($name,$count);
										$this->history->save();
									}
									
									if($this->setting->get("beginnermode")){
										if($this->history->get($name) < 4){
											$player->sendMessage("§c〔DP〕そこはあなたの家ではありません!");
											$player->sendMessage("§c>>また不法侵入すると、処罰の対象となります!");
										}else{
											$this->batu($player,$doorplace);
										}
									}else{
									$this->batu($player,$doorplace);
									}
								}
							}
						}
						}else{
							if($a["state"] == "public"){
							$player->sendPopup($a["message"]);
							}
						}
					}
				
				}else{
					switch($this->action[$name][0]){
						case "info":
							if($this->doordata->exists($doorplace)){
								$player->sendMessage("§6>>~~~~このドアの情報~~~~");
								$player->sendMessage("座標 : " .$doorplace);
								$player->sendMessage("保護の種類 : " .$this->getstate($doorplace));
								$player->sendMessage("オーナーの情報 : ".$this->getowner($doorplace));
								$event->setCancelled();
								unset($this->action[$name]);
							}else{
								$player->sendMessage("§e>>ⓘこの扉は誰も保護していないようです");
								$event->setCancelled();
								unset($this->action[$name]);
							}
						break;
						
						case "invite":
						$a = $this->doordata->get($doorplace);
							if($this->doordata->exists($doorplace)){
								if($player->isOp()){
									if(!isset($a["inviter1"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
									}
									
									if(!isset($a["inviter2"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $a["inviter1"],
										"inviter2" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
									}
									
									if(!isset($a["inviter3"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $a["inviter1"],
										"inviter2" => $a["inviter2"],
										"inviter3" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
									}
									
									if(!isset($a["inviter4"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $a["inviter1"],
										"inviter2" => $a["inviter2"],
										"inviter3" => $a["inviter3"],
										"inviter4" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
									}
									
									if(!isset($a["inviter5"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $a["inviter1"],
										"inviter2" => $a["inviter2"],
										"inviter3" => $a["inviter3"],
										"inviter4" => $a["inviter4"],
										"inviter5" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
									}
									
									if(isset($a["inviter5"])){
										$player->sendMessage("§e>>ⓘこれ以上招待することはできません");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
									}
								}else{
									$owner = $a["owner"];
									$master = explode(',',"$owner");
									if($master[0] == $ip || $master[1] == $name){
										if(!isset($a["inviter1"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
										}
									
										if(!isset($a["inviter2"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $a["inviter1"],
										"inviter2" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
										}
									
										if(!isset($a["inviter3"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $a["inviter1"],
										"inviter2" => $a["inviter2"],
										"inviter3" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
										}
									
										if(!isset($a["inviter4"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $a["inviter1"],
										"inviter2" => $a["inviter2"],
										"inviter3" => $a["inviter3"],
										"inviter4" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
										}
									
										if(!isset($a["inviter5"])){
										$this->doordata->set($doorplace,				//ここをテストしました
										[
										"owner" => $this->getowner($doorplace),
										"state" => $this->getstate($doorplace),
										"inviter1" => $a["inviter1"],
										"inviter2" => $a["inviter2"],
										"inviter3" => $a["inviter3"],
										"inviter4" => $a["inviter4"],
										"inviter5" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘ".$this->action[$name][1] ."が追加されました");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
										}
									
										if(isset($a["inviter5"])){
										$player->sendMessage("§e>>ⓘこれ以上招待することはできません");
										$event->setCancelled();
										unset($this->action[$name]);
										break;
										}
									}else{
										$player->sendMessage("§c>>ⓘ貴方はこのドアのオーナーではありません");
										$event->setCancelled();
										unset($this->action[$name]);
									}
								}
							}else{
								$player->sendMessage("§e>>ⓘこの扉は誰も保護していないようです");
								$event->setCancelled();
								unset($this->action[$name]);
							}
						break;

						case "public":
							if($this->doordata->exists($doorplace)){
								if($player->isOp()){
								$vector = new Vector3($doorx,$doorydecre,$doorz);
								$level = $player->getLevel();
								$block = $level->getBlock($vector);
								$id = $block->getID();
								if(
										$id == Item::WOOD_DOOR_BLOCK ||
										$id == Item::SPRUCE_DOOR_BLOCK ||
										$id == Item::BIRCH_DOOR_BLOCK ||
										$id == Item::JUNGLE_DOOR_BLOCK ||
										$id == Item::ACACIA_DOOR_BLOCK ||
										$id == Item::DARK_OAK_DOOR_BLOCK ||							//<------------ここで、触ったブロックの種類がドアかどうか判断
										$id == Item::SPRUCE_DOOR ||
										$id == Item::BIRCH_DOOR ||
										$id == Item::JUNGLE_DOOR ||
										$id == Item::ACACIA_DOOR ||
										$id == Item::DARK_OAK_DOOR ){
											$doorplace = $doorplace2;
								}
								$this->doordata->set($doorplace,
								[
								"owner" => $ip.",".$name,
								"state" => "public",
								"message" => $this->action[$name][1],
								]);
								$this->doordata->save();
								$player->sendMessage("§a>>ⓘこのドアを公共化しました");
								$event->setCancelled();
								unset($this->action[$name]);
								}else{
									$master = explode(',',$this->getowner($doorplace));
									if($master[0] == $ip || $master[1] == $name){
								$vector = new Vector3($doorx,$doorydecre,$doorz);
								$level = $player->getLevel();
								$block = $level->getBlock($vector);
								$id = $block->getID();
								if(
										$id == Item::WOOD_DOOR_BLOCK ||
										$id == Item::SPRUCE_DOOR_BLOCK ||
										$id == Item::BIRCH_DOOR_BLOCK ||
										$id == Item::JUNGLE_DOOR_BLOCK ||
										$id == Item::ACACIA_DOOR_BLOCK ||
										$id == Item::DARK_OAK_DOOR_BLOCK ||							//<------------ここで、触ったブロックの種類がドアかどうか判断
										$id == Item::SPRUCE_DOOR ||
										$id == Item::BIRCH_DOOR ||
										$id == Item::JUNGLE_DOOR ||
										$id == Item::ACACIA_DOOR ||
										$id == Item::DARK_OAK_DOOR ){
											$doorplace = $doorplace2;
								}
										$this->doordata->set($doorplace,
										[
										"owner" => $ip.",".$name,
										"state" => "public",
										"message" => $this->action[$name][1],
										]);
										$this->doordata->save();
										$player->sendMessage("§a>>ⓘこのドアを公共化しました");
										$event->setCancelled();
										unset($this->action[$name]);
									}else{
										$player->sendMessage("§e>>ⓘこのドアは既に保護済みです");
										$event->setCancelled();
										unset($this->action[$name]);
									}
								}
							}else{
								$this->doordata->set($doorplace,
								[
								"owner" => $ip.",".$name,
								"state" => "public",
								"message" => $this->action[$name][1],
								]);
								$this->doordata->save();
								$player->sendMessage("§a>>ⓘこのドアを公共化しました");
								$event->setCancelled();
								unset($this->action[$name]);
							}
						break;
						
						case "protect":
							if($this->doordata->exists($doorplace)){
								$player->sendMessage("§e>>ⓘこのドアは既に保護済みです");
								$event->setCancelled();
								unset($this->action[$name]);
							}else{
								$vector = new Vector3($doorx,$doorydecre,$doorz);
								$level = $player->getLevel();
								$block = $level->getBlock($vector);
								$id = $block->getID();
								if(
										$id == Item::WOOD_DOOR_BLOCK ||
										$id == Item::SPRUCE_DOOR_BLOCK ||
										$id == Item::BIRCH_DOOR_BLOCK ||
										$id == Item::JUNGLE_DOOR_BLOCK ||
										$id == Item::ACACIA_DOOR_BLOCK ||
										$id == Item::DARK_OAK_DOOR_BLOCK ||							//<------------ここで、触ったブロックの種類がドアかどうか判断
										$id == Item::SPRUCE_DOOR ||
										$id == Item::BIRCH_DOOR ||
										$id == Item::JUNGLE_DOOR ||
										$id == Item::ACACIA_DOOR ||
										$id == Item::DARK_OAK_DOOR ){
											$doorplace = $doorplace2;
								}
								$this->doordata->set($doorplace,
								[
								"owner" => $ip.",".$name,
								"state" => "protect",
								]);
								$this->doordata->save();
								$player->sendMessage("§a>>ⓘこのドアを保護しました");
								$event->setCancelled();
								unset($this->action[$name]);
							}
							
					}
				}
		}		
	}
	
	public function onCommand(CommandSender $sender,Command $command,$label,array $args){
		switch ($command->getName()) {//コマンド名で条件分岐
			case "dp":
				if($sender instanceof Player){
					$player = $sender->getPlayer();
					$name = $player->getName();
					if(!isset($args[0])){
						$penalty = $this->setting->get("penalty");
						$sender->sendMessage("---[DoorProtector サブコマンド]------------------");
						$sender->sendMessage("/dp：");
						$sender->sendMessage("| ⊢<info/i>で、保護されたドアの情報を確認します");
						$sender->sendMessage("| ⊢<add/invite><名前>で、保護されたドアにアクセスできる人を追加します");
						$sender->sendMessage("| ⊢<public><メッセージ>で、そのドアに誰でも入れるようにします");
						$sender->sendMessage("| ⊢<cancel> で、選択を中止できます");
						$sender->sendMessage("| ⊢<check><名前> で、プレイヤーの不法侵入回数を確認できます");
						$sender->sendMessage("| ⊢<p/protect> で、ドアを開けようとした人に、指定された処理をします");
						$sender->sendMessage("| 処理一覧↓      現在の処理は、" .$penalty ."です");
						$sender->sendMessage("| 0 : 警告のみ 1 : アラーム 2 : kick & アラーム 3 : otu(batu) & アラーム & runa");
					}else{
					switch($args[0]){
						case "i":
						case "info":
							$sender->sendMessage("§6〔DP〕情報を確認したいドアをタップしてください");
							$this->action[$name][0] = "info";
						break;
						
						case "add":
						case "invite":
							if(!isset($args[1])){
								$sender->sendMessage("§e>>ⓘアクセスを許可する人の名前が記入されていません");
							}else{
								$sender->sendMessage("§6〔DP〕アクセスを許可するドアをタップしてください(オーナー、op)のみ");
								$this->action[$name][0] ="invite";
								$this->action[$name][1] = $args[1];
							}
						break;
						
						case "p":
						case "protect":
							$sender->sendMessage("§6〔DP〕保護したいドアをタップしてください");
							$this->action[$name][0] = "protect";
						break;
						
						case "public":
							if(!isset($args[1])){
								$sender->sendMessage("§e>>ⓘメッセージが入力されていません");
							}else{
								$sender->sendMessage("§6〔DP〕公共化したいドアをタップして下さい");
								$this->action[$name][0] = "public";
								$this->action[$name][1] = $args[1];
							}
						break;
						
						case "cancel":
							$sender->sendMessage("§e>>ⓘ選択をキャンセルしました");
							unset($this->action[$name]);
						break;
						
						case "check":
							if(!isset($args[1])){
								$sender->sendMessage("§e>>ⓘプレイヤー名が入力されていません");
							}else{
								if($this->history->exists($args[1])){
									$sender->sendMessage("§a>>" .$args[1] ."が不法侵入した回数 : " .$this->history->get($args[1]) ."回");
								}else{
									$sender->sendMessage("§e>>ⓘ" .$args[1] ."は、まだ不法侵入したことがないそうです");
								}
							}
						break;
						
						default:
							$sender->sendMessage("§e>>ⓘそのサブコマンドは存在しません");
						break;
					}
					}
				}else{
					$sender->sendMessage("§e>>ⓘこのコマンドはプレイヤーのみ実行できます");
				}
			return true;
			break;
			
			case "setdp":
				if(!isset($args[0])){
						$penalty = $this->setting->get("penalty");	
						$notice = $this->setting->get("protectnotice");
							$sender->sendMessage("---[DoorProtector 設定]------------------");
							$sender->sendMessage("/setdp：");
							$sender->sendMessage("| ⊢<penalty><0 or 1 or 2 or 3>で、ペナルティーを設定 ");
							$sender->sendMessage("|| 0 : 警告のみ 1 : アラーム 2 : kick & アラーム 3 : otu & アラーム & runa");
							$sender->sendMessage("|| 現在の処理は、" .$penalty ."です");
							$sender->sendMessage("| ⊢<protect><0 or 1 or 2> で、保護通知の設定");
							$sender->sendMessage("|| 0 : 何もしない 1 : 保護の推薦 2 : 自動保護");
							$sender->sendMessage("|| 保護通知は現在" .$notice ."です");
				}else{
					switch($args[0]){
						case "penalty":
						if(!isset($args[1])){
							$sender->sendMessage("§e>>ⓘペナルティーを 0,1,2,3 の中から選択してください");
						}else{
							switch($args[1]){
								case "0":
								$this->setting->set("penalty","0");
								$this->setting->save();
								$this->getServer()->broadcastMessage("§c>>DP : ペナルティーが0に変更されました");
								break;
								
								case "1":
								$this->setting->set("penalty","1");
								$this->setting->save();
								$this->getServer()->broadcastMessage("§c>>DP : ペナルティーが1に変更されました");
								break;
								
								case "2":
								$this->setting->set("penalty","2");
								$this->setting->save();
								$this->getServer()->broadcastMessage("§c>>DP : ペナルティーが2に変更されました");
								break;
								
								case "3":
								$this->setting->set("penalty","3");
								$this->setting->save();
								$this->getServer()->broadcastMessage("§c>>DP : ペナルティーが3に変更されました");
								break;
								
								default:
								$sender->sendMessage("§e>>ⓘ0,1,2,3 から選んで下さい");
								$sender->sendMessage("0 : 警告のみ §a1 : アラーム §b2 : kick & アラーム §c3 : otu(batu) & アラーム & runa");
								break;
							}
						}
						break;
						case "protect":
						if(!isset($args[1])){
							$sender->sendMessage("§e>>ⓘ保護モードが0か1か2か選択してください");
						}else{
							switch($args[1]){
								case "0":
								$this->setting->set("protectnotice",0);
								$this->setting->save();
								$this->getServer()->broadcastMessage("§c>>DP : ドア設置時の保護モードは0になりました");
								break;
								
								case "1":
								$this->setting->set("protectnotice",1);
								$this->setting->save();
								$this->getServer()->broadcastMessage("§c>>DP : ドア設置時の通知は1になりました");
								break;
								
								case "2":
								$this->setting->set("protectnotice",2);
								$this->setting->save();
								$this->getServer()->broadcastMessage("§c>>DP : ドア設置時の通知は2になりました");
								break;
								
								default:
								$sender->sendMessage("§c>>0か1か2か選んで下さい");
								break;
							}
						}
						break;
						
						default:
							$sender->sendMessage("§e>>ⓘそのサブコマンドは存在しません");
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
		$ip = $player->getAddress();
		$block = $event->getBlock();
		$breakx = (Int)$block->x;
		$breaky = (Int)$block->y;
		$breaky2 = $breaky;
		++$breaky2;
		$breakz = (Int)$block->z;
		$breakworld = str_replace(" ", "%", $block->getLevel()->getName());
		$breakplace2 = $breakx.",".$breaky2.",".$breakz.",".$breakworld;
		
		if($this->doordata->exists($breakplace2)){
				if($player->isOp()){
					$player->sendMessage("§6>>ⓘドアは撤去され、保護が解除されました");
					$this->doordata->remove($breakplace2);
					$this->doordata->save();
				}else{
					$master = explode(',',$this->getowner($breakplace2));
					if($master[0] == $ip || $master[1] == $name){
						$player->sendMessage("§6>>ⓘドアは撤去され、保護が解除されました");
						$this->doordata->remove($breakplace2);
						$this->doordata->save();
					}else{
						$player->sendMessage("§6>>ⓘ貴方はこの家のオーナーではありません!");
						$this->getServer()->broadcastMessage("§c⚠" .$name ."が、§6" .$master[1] ."§cの家のドアを壊そうとしました！");
						$this->batu($player,$doorplace);
						$event->setCancelled();
					}
				}
		}
		
		if(
			$event->getBlock()->getID() == Item::WOOD_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::SPRUCE_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::BIRCH_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::JUNGLE_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::ACACIA_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::DARK_OAK_DOOR_BLOCK ||							//<------------ここで、触ったブロックの種類がドアかどうか判断
			$event->getBlock()->getID() == Item::SPRUCE_DOOR ||
			$event->getBlock()->getID() == Item::BIRCH_DOOR ||
			$event->getBlock()->getID() == Item::JUNGLE_DOOR ||
			$event->getBlock()->getID() == Item::ACACIA_DOOR ||
			$event->getBlock()->getID() == Item::DARK_OAK_DOOR ){
				
			$player = $event->getPlayer();
			$name = $player->getName();
			$ip = $player->getAddress();//ipアドレス
			$block = $event->getBlock();
			$doorx = (Int)$block->x;
			$doory = (Int)$block->y;
			$doory2 = $doory;
			--$doory2;
			$doorz = (Int)$block->z;
			$doorworld = str_replace(" ", "%", $block->getLevel()->getName());
			$doorplace = $doorx.",".$doory.",".$doorz.",".$doorworld;
			$doorplace2 = $doorx.",".$doory2.",".$doorz.",".$doorworld;
			
			if($this->doordata->exists($doorplace2)){
					$doorplace = $doorplace2;												//<------------ここで、上のブロックを触った場合は下にある確認する
			}
			
			if($this->doordata->exists($doorplace)){
				if($player->isOp()){
					$player->sendMessage("§6>>ⓘドアは撤去され、保護が解除されました");
					$this->doordata->remove($doorplace);
					$this->doordata->save();
				}else{
					$master = explode(',',$this->getowner($doorplace));
					if($master[0] == $ip || $master[1] == $name){
						$player->sendMessage("§6>>ⓘドアは撤去され、保護が解除されました");
						$this->doordata->remove($doorplace);
						$this->doordata->save();
					}else{
						$player->sendMessage("§c>>ⓘ貴方はこの家のオーナーではありません!");
						$this->getServer()->broadcastMessage("§c⚠" .$name ."が、§6" .$master[1] ."§cの家のドアを壊そうとしました！");
						$this->batu($player,$doorplace);
						$event->setCancelled();
					}
				}
			}
		}
	 }
	
	public function setblock(BlockPlaceEvent $event){
		
		if(
			$event->getBlock()->getID() == Item::WOOD_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::SPRUCE_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::BIRCH_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::JUNGLE_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::ACACIA_DOOR_BLOCK ||
			$event->getBlock()->getID() == Item::DARK_OAK_DOOR_BLOCK ||							//<------------ここで、触ったブロックの種類がドアかどうか判断
			$event->getBlock()->getID() == Item::SPRUCE_DOOR ||
			$event->getBlock()->getID() == Item::BIRCH_DOOR ||
			$event->getBlock()->getID() == Item::JUNGLE_DOOR ||
			$event->getBlock()->getID() == Item::ACACIA_DOOR ||
			$event->getBlock()->getID() == Item::DARK_OAK_DOOR){
			
			$player = $event->getPlayer();
			$name = $player->getName();
			$ip = $player->getAddress();//ipアドレス
			$block = $event->getBlock();
			$doorx = (Int)$block->x;
			$doory = (Int)$block->y;
			$dooryincre = $doory;
			++$dooryincre;
			$doorz = (Int)$block->z;
			$doorworld = str_replace(" ", "%", $block->getLevel()->getName());
			$doorplace = $doorx.",".$doory.",".$doorz.",".$doorworld;
				
				switch($this->setting->get("protectnotice")){
					case 1:
					$player->sendMessage("§e>>ⓘ/dp p で、ドアを保護することをお勧めします");
					break;
					
					case 2:
					if(!$event->isCancelled()){
					$this->doordata->set($doorplace,
					[
					"owner" => $ip.",".$name,
					"state" => "protect",
					]);
					$this->doordata->save();
					$player->sendMessage("§6>>ⓘドアが自動保護されました");
					break;
					}else{
					$player->sendMessage("§6>>ⓘドアの設置に失敗したので、保護はされません");	
					}
				}
				
		}
	}
	
	public function getowner($doorplace){
		$a = $this->doordata->get($doorplace);
		$owner = $a["owner"];
		return $owner;
	}
	
	public function getstate($doorplace){
		$a = $this->doordata->get($doorplace);
		$state = $a["state"];
		return $state;
	}

	public function isinviter($doorplace,$name){
		$a = $this->doordata->get($doorplace);
		$inviter1 = @$a["inviter1"];
		$inviter2 = @$a["inviter2"];
		$inviter3 = @$a["inviter3"];
		$inviter4 = @$a["inviter4"];
		$inviter5 = @$a["inviter5"];
		if($inviter1 == $name || $inviter2 == $name || $inviter3 == $name || $inviter4 == $name || $inviter5 == $name){
			return true;
		}else{
			return false;
		}
	}
		
	public function batu($player,$doorplace){
		
		$master = explode(',',$this->getowner($doorplace));
		
		switch($this->setting->get("penalty")){
			case 0:
				$player->sendMessage("§c>>ⓘそこはあなたの家ではありません!");
			break;
			
			case 1:
				$name = $player->getName();
				$this->getServer()->broadcastMessage("§c【DP】" .$name ."が、§6" .$master[1] ."§cの家に侵入しました!");
			break;
			
			case 2:
				$name = $player->getName();
				$this->getServer()->broadcastMessage("§c【DP】" .$name ."が、§6" .$master[1] ."§cの家に侵入しました!");
				$player->kick("§c他人の家に勝手に入らないで下さい", true);
			break;
			
			case 3:
				if(strpos($player->getDisplayName(),"§c[罰]") === false){
					$name = $player->getName();
					$this->getServer()->broadcastMessage("§c【DP】" .$name ."が、§6" .$master[1] ."§cの家に侵入しました!");
					$this->getServer()->dispatchCommand(new ConsoleCommandSender(),"otu " .$name);
					$this->getServer()->dispatchCommand(new ConsoleCommandSender(),"x " .$name);
					$this->getServer()->dispatchCommand(new ConsoleCommandSender(),"runa " .$name);
				}
			break;
		}
	}
}