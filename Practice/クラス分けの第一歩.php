プラグインのメインクラスでConfigオブジェクトや、配列をメンバ変数として保持する
プラグイン多いけど、そういった操作は「抽象化」すべきだよね、といったお話。

ここでは、生活鯖とかでよくあるドア保護プラグインを例に上げます
(/saveDoorコマンドで、どのドアが、どのユーザーの物かを管理するプラグイン)

<?php
class Main extends PluginBase{
	
	//Configオブジェクト
	private $config;
	
	function onCommand($commandName){
		if("saveDoor" == $commandName){
			$this->config->save(/*ドアのデータの保存*/);
		}
	}
}
?>

今までこうだったものを、

<?php

class Main extends PluginBase{
	
	//DoorManagerオブジェクト
	private $manager;
	
	function onCommand($commandName){
		if("saveDoor" == $commandName){
			$this->manager->addOwner(/**/);
		}
	}
}

//ドアのデータの管理をするクラス
class DoorManager{
	
	//Configオブジェクト
	private $config;
	
	/**
	* $ownerNameはドアの管理者の名前
	* $doorPositionはドアの座標
	*/
	function addOwner(string $ownerName,Position $doorPosition){
		$this->config->save(/*ドアデータの保存*/);
	}
}

?>

こんな感じに、ドアデータ保存の処理を別のclassに「抽出」する。

Configオブジェクト(若しくは配列)をメインクラスのメンバ変数として保持し、直接アクセスするのではなく、
その処理を別のclassに任せる。

いちいち、Configのどのキーに保存すればいいとか気にしなくてよくなるね
