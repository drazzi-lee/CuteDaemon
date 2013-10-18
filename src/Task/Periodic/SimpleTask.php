<?php

namespace CuteDaemon\Task\Periodic;

use CuteDaemon\Task\BaseTask;
use CuteDaemon\System\Daemon;

/**
 * SimpleTask, the task is only run the php script simply without any other
 * job to do.
 *
 * @author Pengfei Li
 */
class SimpleTask extends BaseTask{

	/**
	 * The php script, is the realpath normaly.
	 */
	private $phpScript;

	/**
	 * Flag whether the task is running.
	 */
	private $isRunning = FALSE;

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
			$pid = pcntl_fork();
			if($pid === -1){
				//'Process could not be forked.';
				Daemon::Log(Daemon::LOG_INFO, "----------|>\n");
				throw new Exception($this->taskName.'Process could not be forked ');
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

	public function getScript(){
		return $this->phpScript;
	}

	public function setScript($script){
		$this->phpScript = $script;
	}

	/**
	 * Perpare the script to run, initialize the settings from config.ini
	 */
	public function prepared($taskFile){
		$this->setTaskFrom($taskFile);
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
