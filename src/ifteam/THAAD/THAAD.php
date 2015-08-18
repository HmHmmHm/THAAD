<?php

namespace ifteam\THAAD;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\event\server\ServerCommandEvent;

class THAAD extends PluginBase implements Listener {
	public function onEnable() {
		@mkdir ( $this->getDataFolder () );
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	public function THAAD_report(Array $report) {
		foreach ( $report as $index => $data ) {
			echo "\n";
			echo "\033[31m" . "#--------------------------------------------\n";
			echo "\033[31m" . "THAAD Code Warning Dump " . date ( "D M j H:i:s T Y" ) . "\n";
			echo "\n";
			switch ($data ["find"]) {
				case "eval(" :
					echo "\033[31m" . "문제: eval 함수는 명령어를 외부환경에서 쓸 수 있게함." . "\n";
					break;
				case "exit(" :
					echo "\033[31m" . "문제: exit 함수는 포켓마인을 멈출 수 있습니다." . "\n";
					break;
				case "setop(" :
					echo "\033[31m" . "문제: setop 함수는 시스템의 OP 체계에 간섭합니다." . "\n";
					break;
				case "unlink(" :
					echo "\033[31m" . "문제: unlink 함수는 서버파일을 삭제할 수 있습니다." . "\n";
					break;
			}
			echo "\033[31m" . "파일: " . $data ["link"] . "\n";
			echo "\033[31m" . "줄번호: " . $data ["index"] . "\n";
			echo "\n\033[31m코드:\n";
			for($i = $data ["index"] - 10; $i <= $data ["index"] + 10; $i ++) {
				if (isset ( $data ["code"] [$i] )){
					if($data ["index"] == $i){
						echo "\033[41m" . "[" . $i . "]" . $data ["code"] [$i] . "\033[m\n";
					}else{
						echo "\033[31m" . "[" . $i . "]" . $data ["code"] [$i] . "\n";
					}
				}
			}
			// echo "\033[31m" . "" . "\n";
			echo "\033[31m" . "#--------------------------------------------\n";
		}
	}
	public function ServerCommand(ServerCommandEvent $event) {
		$command = $event->getCommand ();
		$player = $event->getSender ();
		
		if (strtolower ( $command ) !== "thaad")
			return;
		
		$event->setCancelled ();
		$this->getServer ()->getScheduler ()->scheduleAsyncTask ( new THAAD_async ( $this->getDataFolder () ) );
	}
}

?>