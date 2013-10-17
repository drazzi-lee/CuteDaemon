<?php

namespace CuteDaemon\Task;

abstract class BaseTask{
	/**
	 * The task name, can be the same with the script file name.
	 */
	public $taskName;

	/**
	 * Run the task every period seconds
	 */
	protected $period = 5;

	/**
	 * Times need to run, -1 by default, will run on no times limit.
	 */
	protected $timesNeed = -1;

	protected $taskFrom;

	/**
	 * Last run time, Unix timestamp parse.
	 */
	protected $lastRun;

	protected function setTaskFrom($taskFromFile){
		$this->taskFrom = $taskFromFile;
	}

	public function getTaskFrom(){
		return $this->taskFrom;
	}

	public function getLastRun(){
		return $this->lastRun;
	}

	public function setLastRun($time){
		if($this->lastRun < $time){
			$this->lastRun = $time;
		}
	}

	public function getPeriod(){
		return $this->period;
	}

	protected function setPeriod($period){
		$this->period = $period;
	}

	protected function setTimesNeed($count){
		if(!empty($count)){
			$this->timesNeed = $count;
		}
	}

	public function getTimesNeed(){
		return $this->timesNeed;
	}
}
