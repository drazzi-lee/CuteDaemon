<?php

namespace CuteDaemon\Task;

use CuteDaemon\System\Daemon;

abstract class BaseTask{
	/**
	 * The task name, can be the same with the script file name.
	 */
	public $taskName;

	protected $taskFrom;

	/**
	 * The php script, is the realpath normaly.
	 */
	protected $phpScript;

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

	protected function setScript($script){
		$this->phpScript = $script;
	}

	public function getScript(){
		return $this->phpScript;
	}

	/**
	 * Check whether it is the time to run the task.
	 *
	 * @return boolean.
	 */
	abstract public function isTimeToWakeUp();

	/**
	 * After the run method was called, what to do next.
	 * Like count down the times need. set lastRun..etc.
	 */
	abstract public function afterRun();

	/**
	 * Read setttings from the config.ini, prepared the script
	 * to run.
	 */
	abstract public function initialize($taskFile);

	/**
	 * Run the task, it will callback when it comes to end.
	 */
	public function run($callback = null){
		if(!$this->isRunning()){
			/**
			 * 2>&1 : This will cause the stderr ouput of a program to be
			 * 			written to the same filedescriptor than stdout.
			 * &>	: This will place every output of a program to a file. 
			 */
			$this->lastRun = time();
			$pid = pcntl_fork();
			if($pid === -1){
				//'Process could not be forked.';
				Daemon::Log(Daemon::LOG_INFO, "[{$this->taskName}] Process could not be forked.");
			} else if($pid){
				//Parent return.
				Daemon::Log(Daemon::LOG_INFO, "[{$this->taskName}] parent process end.\n");
				return TRUE;			
			} else {
				$output = array();

				$this->attachRunning();
  				exec( PHP_BIN. ' -q ' . $this->phpScript . ' 2>&1', $output);
				$this->detachRunning();	

				Daemon::Log(Daemon::LOG_INFO, "[{$this->taskName}] child process end.\n");

				if(is_callable($callback)){
					call_user_func_array($callback, array($this, $output));
				}
			}
		}
	}
	
	/**
	 *  Check the task is running or not.
	 *  
	 *  @return boolean;
	 */
	protected function isRunning(){
		$runningList = $this->getRunning();
		return in_array($this->taskFrom, $runningList);
	}
	
	/**
	 *  Assign the current task is running to file.
	 *  
	 *  @return the number of bytes that were written to the file, or FALSE on failure
	 */
	protected function attachRunning(){
		$runningList = array();
		if(file_exists(TASK_RUNNING_FILE)){
			$runningList = json_decode(file_get_contents(TASK_RUNNING_FILE));
		}
		array_push($runningList, $this->taskFrom);
		return file_put_contents(TASK_RUNNING_FILE, json_encode($runningList));
	}
	
	/**
	 *  Detach the current task from running list.
	 *  
	 *  @return the number of bytes that were written to the file, or FALSE on failure
	 */
	protected function detachRunning(){
		$runningList = $this->getRunning();
		if(($key = array_search($this->taskFrom, $runningList)) !== false) {
			unset($runningList[$key]);
		}		
		return file_put_contents(TASK_RUNNING_FILE, json_encode($runningList));
	}
	
	/**
	 *  Get Running task List.
	 *  
	 *  @return array
	 */
	protected function getRunning(){
		$runningList = array();
		if(file_exists(TASK_RUNNING_FILE)){
			$runningList = json_decode(file_get_contents(TASK_RUNNING_FILE));
		}
		if($runningList == null){
			$runningList = array();
		}
		return $runningList;
	}
}
