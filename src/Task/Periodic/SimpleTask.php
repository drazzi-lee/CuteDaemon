<?php

namespace CuteDaemon\Task\Periodic;

use CuteDaemon\Task\BaseTask;

class SimpleTask extends BaseTask{

	private $period = 5;
	private $phpScript;
	private $lastRun = 0;

	public function run(){
		exec('php -q '.$this->phpScript);
	}

	public function getPeriod(){
		return $this->period;
	}

	public function setPeriod($period){
		$this->period = $period;
	}

	public function getScript(){
		return $this->phpScript;
	}

	public function setScript($script){
		$this->phpScript = $script;
	}

	public function getLastRun(){
		return $this->lastRun;
	}

	public function setLastRun($time){
		$this->lastRun = $time;
	}

	public function prepared(){
		$this->setScript($this->taskFrom);
		
		$configFile = dirname(realpath($this->phpScript)).'/config.ini';
		$taskFileName = basename($this->phpScript, '.php');

		$config = parse_ini_file($configFile, TRUE);
		if(isset($config[$taskFileName]['period'])
			&& $config[$taskFileName]['period'] > 1){
			$this->period = $config[$taskFileName]['period'];
		}
	}
}
