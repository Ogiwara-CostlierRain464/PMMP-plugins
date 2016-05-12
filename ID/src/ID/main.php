<?php

namespace ID;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use pocketmine\item\item;

class main extends PluginBase implements Listener{

  public function onCommand(CommandSender $sender, Command $command, $label, array $args){

	switch ($command->getName()) {
		case "id":
			if(!isset($args[0])){
				$id = $sender->getInventory()->getItemInHand()->getId();
				$meta  = $sender->getInventory()->getItemInHand()->getDamage();
				$sender->sendMessage("§b>>貴方が今持っているアイテム".$id ." : " .$meta);
			}else{
				if(!ctype_digit($args[0])){
					$sender->sendMessage("§c>>IDの入力方法が間違ってます");
				}else{
					$player = $sender->getPlayer();
					if(!$player->isOp()){
						$sender->sendMessage("§b>>貴方はOPではないので、アイテム取得は出来ません");
					}else{
						if(isset($args[1]) &&isset($args[2])){
							if(ctype_digit($args[1]) && ctype_digit($args[2])){
								$item = Item::get($args[0],$args[1],$args[2]);
								$player->getInventory()->addItem($item);
								$sender->sendMessage("§a>>貴方に" .$args[0] ." : " .$args[1] ."を" .$args[2] ."個与えました");
							}else{
								$sender->sendMessage("§c>>ダメージ値,個数の入力方法が間違ってます");
							}
						break;
						}
						
						if(isset($args[1])){
							if(ctype_digit($args[1])){
								$item = Item::get($args[0],$args[1],1);
								$player->getInventory()->addItem($item);
								$sender->sendMessage("§a>>貴方に" .$args[0] ." : " .$args[1] ."を1個与えました");
							}else{
								$sender->sendMessage("§c>>ダメージ値の入力方法が間違ってます");
							}
						break;
						}
						
						$item = Item::get($args[0],0);
						$player->getInventory()->setItemInHand($item);
						$index = $player->getInventory()->getHeldItemSlot();
						$player->getInventory()->setHotbarSlotIndex($index,$index);
						$player->save();
						$sender->sendMessage("§a>>貴方の手持ちに" .$args[0] ." : 0をセットしました");
					}		 
				}

			}
			return true;
			break;
     }
  }
}