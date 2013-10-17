<?php

namespace CuteDaemon\Task\Periodic;

use CuteDaemon\Task\BaseTask;

/**
 * SimpleTask, the task is only run the php script simply without any other
 * job to do.
 *
 * @author Pengfei Li
 */
class SimpleTask extends BaseTask{

	/**
	 * Run the task every period seconds
	 */
	private $period = 5;

	/**
	 * The php script, is the realpath normaly.
	 */
	private $phpScript;

	/**
	 * Last run time, Unix timestamp parse.
	 */
	private $lastRun = 0;

	/**
	 * Times need to run, -1 by default, will run on no times limit.
	 */
	private $timesNeed = -1;

	/**
	 * The task name, can be the same with the script file name.
	 */
	public $taskName;

	/**
	 * Flag whether the task is running.
	 */
	private $isRunning = FALSE;

	/**
	 * Run the task, it will callback when it comes to end.
	 */
	public function run($callback = null){
		if(($this->timesNeed > 0 || $this->timesNeed == -1) && !$this->isRunning){
			$output = array();

			$this->isRunning = TRUE;
			/**
			 * 2>&1 : This will cause the stderr ouput of a program to be
			 * 			written to the same filedescriptor than stdout.
			 * &>	: This will place every output of a program to a file. 
			 *
			 * @TODO Need to fork a child process.
			 */
			exec('/usr/bin/php -q ' . $this->phpScript . ' 2>&1', $output);
			$this->timesNeed--;
			$this->lastRun = time();
			$this->isRunning = FALSE;	

			if(is_callable($callback)){
				call_user_func_array($callback, array($this, $output));
			}
		}
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

	public function setTimesNeed($count){
		if(!empty($count)){
			$this->timesNeed = $count;
		}
	}

	public function getTimesNeed(){
		return $this->timesNeed;
	}

	/**
	 * Perpare the script to run, initialize the settings from config.ini
	 */
	public function prepared(){
		$this->setScript($this->taskFrom);
		
		$configFile = dirname(realpath($this->phpScript)).'/config.ini';
		$taskFileName = basename($this->phpScript, '.php');
		$this->taskName = $taskFileName;

		$config = parse_ini_file($configFile, TRUE);
		if(isset($config[$taskFileName]['period'])
			&& $config[$taskFileName]['period'] > 1){
			$this->period = $config[$taskFileName]['period'];
		}
		if(isset($config[$taskFileName]['times'])){
			$this->timesNeed = (int)$config[$taskFileName]['times'];
		}
	}
}
