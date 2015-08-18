<?php

namespace ifteam\THAAD;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class THAAD_async extends AsyncTask {
	public $pluginFolder;
	public $report = [ ];
	public function __construct($pluginFolder) {
		$this->pluginFolder = $pluginFolder;
	}
	public function onRun() {
		$fileList = $this->getPHPFile ( $this->pluginFolder );
		$this->report = $this->parsePHPFile ( $fileList );
	}
	public function getPHPFile($link) {
		$folderList = $this->getFolderList ( $link, "folder" );
		$fileList = $this->getFolderList ( $link, "file" );
		
		foreach ( $fileList as $index => $string )
			$fileList [$index] = $link . '/' . $string;
		
		foreach ( $folderList as $folder ) {
			$list = $this->getPHPFile ( $link . '/' . $folder );
			foreach ( $list as $file )
				$fileList [] = $file;
		}
		return $fileList;
	}
	public function parsePHPFile($fileList) {
		$report = [ ];
		foreach ( $fileList as $file ) {
			$checkExtension = explode ( '.', $file );
			if (isset ( $checkExtension [1] )) {
				
				switch ($checkExtension [count ( $checkExtension ) - 1]) {
					case "php" :
						$nowreports = $this->checkPHPFile ( $file );
						foreach ( $nowreports as $nowreport )
							$report [] = $nowreport;
						break;
					case "phar" :
						$nowreports = $this->checkPHARFile ( $file );
						foreach ( $nowreports as $nowreport )
							$report [] = $nowreport;
						break;
				}
			}
		}
		return $report;
	}
	public function checkPHPFile($link) {
		$contents = file_get_contents ( $link );
		if ($contents == null)
			return;
		
		$contents = explode ( "\n", $contents );
		$problemCode = [ 
				"eval(",
				"exit(",
				"setop(",
				"unlink(" 
		];
		$result = [ ];
		foreach ( $contents as $index => $line ) {
			$line = strtolower ( $line );
			$line = explode ( " ", $line );
			$line = implode ( "", $line );
			foreach ( $problemCode as $badcode ) {
				$check = explode ( $badcode, $line );
				if (isset ( $check [1] )) {
					$result [] = [ 
							"link" => $link,
							"index" => $index,
							"code" => $contents,
							"find" => $badcode 
					];
				}
			}
		}
		return $result;
	}
	public function checkPHARFile($link) {
		$link = "phar://" . $link;
		$report = [ ];
		foreach ( new \RecursiveIteratorIterator ( new \RecursiveDirectoryIterator ( $link ) ) as $index => $file ) {
			$path = ltrim ( str_replace ( array (
					"\\",
					$link 
			), array (
					"/",
					"" 
			), $file ), "/" );
			$nowreports = $this->checkPHPFile ( $index );
			foreach ( $nowreports as $nowreport )
				$report [] = $nowreport;
		}
		return $report;
	}
	public function onCompletion(Server $server) {
		$thaad = $server->getPluginManager ()->getPlugin ( "THAAD" );
		if ($thaad instanceof THAAD)
			$thaad->THAAD_report ( $this->report );
	}
	/**
	 *
	 * It gets a list of folders or files
	 *
	 * @param string $rootDir        	
	 * @param string $filter
	 *        	= "folder" || "file" || null
	 *        	
	 * @return array $rList
	 */
	public function getFolderList($rootDir, $filter = "") {
		$handler = opendir ( $rootDir );
		$rList = array ();
		$fCounter = 0;
		while ( $file = readdir ( $handler ) ) {
			if ($file != '.' && $file != '..') {
				if ($filter == "folder") {
					if (is_dir ( $rootDir . "/" . $file )) {
						$rList [$fCounter ++] = $file;
					}
				} else if ($filter == "file") {
					if (! is_dir ( $rootDir . "/" . $file )) {
						$rList [$fCounter ++] = $file;
					}
				} else {
					$rList [$fCounter ++] = $file;
				}
			}
		}
		closedir ( $handler );
		return $rList;
	}
}

?>