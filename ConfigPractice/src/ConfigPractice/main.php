<?php

namespace ConfigPractice;

use pocketmine\Plugin\PluginBase;

use pocketmine\utils\Config;//Config

class main extends PluginBase{
	
	public function onEnable(){//このプラグインが読み込まれたときの処理
		
		/* コンフィグファイル(YAML文書) について
		これは簡単にいうと、配列をそのまま保存できるようにしたもの
		key: value
		の形式で保存し、keyを指定することにより、valueがゲットできる
		*/
		
		//1 コンフィグファイルを保存するフォルダーをつくる
		if(!file_exists($this->getDataFolder())){ 
		mkdir($this->getDataFolder(), 0755, true); 
		}
		
		//2 コンフィグファイルを作る
		//ここで、config.ymlが生成される(すでに作成済みの場合はなにもしない)
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
		"名前" => "ogiwara","年齢" => "17"//保存内容
		));
		
		/*なお、いかのようにすると空のコンフィグファイルを生成
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML):
		*/
		
		//3 コンフィグファイルへのアクセス方法
		/*
		おもなメソッドは以下の5つ
		set
		get
		exists
		remove
		save
		*/
		
		//コンフィグファイルに、データを追加する
		$this->config->set("性別","男");
		$this->config->save();//saveしないと保存されません!
		
		//keyを指定して、valueを取得する
		//例、先程保存した「性別」の取得
		$this->Debug($this->config->get("性別"));
		
		//コンフィグファイルに、データがあるかどうか(keyがあるかどうか)
		if($this->config->exists("身長")){
			$this->Debug("身長という項目はあります");
		}else{
			$this->Debug("身長という項目はありません");
		}
		
		//指定したデータを削除
		$this->config->remove("性別");
		$this->config->save();//saveしないと保存されません!
	}
	
	//デバッグ用の関数
	public function Debug($message){
		$this->getLogger()->notice($message);
	}
}
	
	
	