<?php

namespace CuteDaemon\Task\Periodic;

use CuteDaemon\Task\BaseTask;

/**
 * RepetitiveTask, the task is only run the php script simply without any other
 * job to do.
 *
 * @author Pengfei Li
 */
class RepetitiveTask extends BaseTask{
	/**
	 * Times need to repeat, -1 by default, will run on no times limit.
	 */
	private $needRepeat = -1;

	/**
	 * Run the task every period seconds
	 */
	private $period = 5;

	private function countDownTimesNeed(){
		if($this->needRepeat > 0){
			$this->needRepeat--;
		}
	}

	/**
	 * Perpare the script to run, initialize the settings from config.ini
	 */
	public function initialize($taskFile){
		$this->setTaskFrom($taskFile);
		$this->setScript($this->taskFrom);
		
		$configFile = dirname($taskFile).'/config.ini';
		$taskFileName = basename($taskFile, '.php');
		$this->taskName = 'RepetitiveTask::'.$taskFileName;

		$config = parse_ini_file($configFile, TRUE);
		if(isset($config[$taskFileName]['period'])
			&& $config[$taskFileName]['period'] > 1){
			$this->period = $config[$taskFileName]['period'];
		}
		if(isset($config[$taskFileName]['times'])){
			$this->needRepeat = (int)$config[$taskFileName]['times'];
		}
	}

	/**
	 * Check whether it is the time to run the task.
	 *
	 * @return boolean.
	 */
	public function isTimeToWakeUp(){
		if(($this->needRepeat > 0 || $this->needRepeat == -1)
			&& time() - $this->lastRun >= $this->period){
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * After the run method was called, count down the timesneed,
	 * set lastRun.
	 */
	public function afterRun(){
		$this->countDownTimesNeed();
	}
}
