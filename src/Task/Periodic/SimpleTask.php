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
		return $this->setScript($this->taskFrom);
	}
}
