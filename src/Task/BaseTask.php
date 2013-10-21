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
	 * Flag whether the task is running.
	 */
	protected $isRunning = FALSE;

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
		if(!$this->isRunning){
			/**
			 * 2>&1 : This will cause the stderr ouput of a program to be
			 * 			written to the same filedescriptor than stdout.
			 * &>	: This will place every output of a program to a file. 
			 *
			 * @TODO Need to fork a child process.
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

				$this->isRunning = TRUE;
  				exec('/usr/bin/php -q ' . $this->phpScript . ' 2>&1', $output);
				$this->isRunning = FALSE;	

				Daemon::Log(Daemon::LOG_INFO, "[{$this->taskName}] child process end.\n");
				$this->isRunning = FALSE;	

				if(is_callable($callback)){
					call_user_func_array($callback, array($this, $output));
				}
			}
		}
	}
}
