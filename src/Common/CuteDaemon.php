<?php
namespace CuteDaemon\Common;

use CuteDaemon\System\Daemon;
use CuteDaemon\Task\BaseTask;
use CuteDaemon\Task\Periodic\RepetitiveTask;
use CuteDaemon\Task\Periodic\DailyTask;

Class CuteDaemon{
	private $tasks = array();
	private $currentTaskLine = array();
	private static $minPeriod;
	private static $repetitiveTaskDirectory;
	private static $dailyTaskDirectory;

	public function __construct(){
		self::$minPeriod = 1;
		self::$repetitiveTaskDirectory = TASK_REPETITIVE_PATH;
		self::$dailyTaskDirectory = TASK_DAILY_PATH;
	}

	/**
	 * Attach new task in tasks.
	 *
	 * @param BaseTask $task
	 * @return void.
	 */
	public function attach(BaseTask $task){
		$this->tasks[] = $task;
		Daemon::log(Daemon::LOG_INFO, 'Attach task '. $task->taskName .
			" Task infor: \n" . print_r($task, TRUE));
	}#method end.

	/**
	 * Detach task which need to be removed.
	 *
	 * @param BaseTask $task
	 * @return void.
	 */
	public function detach(BaseTask $task){
		foreach($this->tasks as $tkey => $tval){
			if($tval == $task){
				unset($this->tasks[$tkey]);
				Daemon::log(Daemon::LOG_INFO, 'Detach task '.$task->taskName);
			}
		}
	}#method end.

	/**
	 * Notify the task which is the time to run.
	 *
	 * @return void.
	 */
	public function notify(){
		foreach($this->tasks as $task){
			//if it is time to run this task.
			if($task->isTimeToWakeUp()){
				try{
					Daemon::log(Daemon::LOG_INFO,
							'Call task to wake up: '. $task->taskName);
					$task->run(array($this, 'complete'));
					$task->afterRun();
				} catch(Exception $e){
					Daemon::log(Daemon::LOG_INFO,
							'An exception was caught by running task  ' .
							$task->taskName . "\nError Message: " .
							$e->getMessage());
				}
			}
		}
	}#method end.

	/**
	 * When the task runs to an end, logging its output.
	 */
	public function complete(BaseTask $task, array $output){
		if(count($output) > 0){
			Daemon::log(Daemon::LOG_INFO,
					'Task ' . $task->taskName . " exit with output: \n" .
					print_r($output, TRUE));
		} else {
			Daemon::log(Daemon::LOG_INFO,
					'Task ' . $task->taskName . ' has run to the end ' .
					'with nothing output.');
		}
		exit();
	}

	/**
	 * Update tasks by files in task directory. 
	 *
	 * @return void.
	 */
	public function updateTasks(){
		$currentDailyTaskFiles = glob(self::$dailyTaskDirectory . '*.php');
		$currentRepetitiveTaskFiles = glob(self::$repetitiveTaskDirectory . '*.php');


		//Add new simple periodic tasks.
		foreach($currentRepetitiveTaskFiles as $taskFile){
			if(is_file($taskFile) && !$this->isTaskAttached($taskFile)){
				$repetitiveTask = new RepetitiveTask();
				$repetitiveTask->initialize($taskFile);
				$this->attach($repetitiveTask);
				$this->currentTaskLine[] = $taskFile;
			}
		}
		//Add new simple periodic tasks.
		foreach($currentDailyTaskFiles as $taskFile){
			if(is_file($taskFile) && !$this->isTaskAttached($taskFile)){
				$dailyTask = new DailyTask();
				$dailyTask->initialize($taskFile);
				$this->attach($dailyTask);
				$this->currentTaskLine[] = $taskFile;
			}
		}

		//Remove expired tasks.
		$taskFileRemoved = array_diff($this->currentTaskLine, array_merge($currentDailyTaskFiles, $currentRepetitiveTaskFiles));
		foreach($taskFileRemoved as $taskFile){
			foreach($this->tasks as $attachedTask){
				if($attachedTask->getTaskFrom() == $taskFile){
					$this->detach($attachedTask);
				}
			}
		}
	}#method end.
	
	/**
	 * Check is the task has attached already.
	 *
	 * @param string $fileRealName: the file's realpath and name in string parse.
	 * @return boolean.
	 */
	private function isTaskAttached($fileRealName){
		$isTaskAttached = FALSE;
		foreach($this->tasks as $attachedTask){
			if($attachedTask->getTaskFrom() == $fileRealName){
				$isTaskAttached = TRUE;
				break;
			}
		}
		return $isTaskAttached;
	}#method end.

	/**
	 * Get declare classes in specify namespace
	 *
	 * @param string $namespace:
	 * @return array.
	 * @note Temporarily not used.
	 */
	private function getClassesInNamespace($namespace){
		$allClasses = get_declared_classes();
		$userDefinedClasses = array();
		foreach($allClasses as $className){
			$function = new \ReflectionClass($className);
			if($function->getNamespaceName() == $namespace){
				$userDefinedClasses[] = $className;
			}
		}
		return $userDefinedClasses;
	}#method end.
}
