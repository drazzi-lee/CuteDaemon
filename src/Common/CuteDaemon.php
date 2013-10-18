<?php
namespace CuteDaemon\Common;

use CuteDaemon\System\Daemon;
use CuteDaemon\Task\BaseTask;
use CuteDaemon\Task\Periodic\SimpleTask;

Class CuteDaemon{
	private $tasks = array();
	private $currentTaskLine = array();
	private static $minPeriod;
	private static $namespacePeriodic;
	private static $periodicDirectory;

	public function __construct(){
		self::$minPeriod = 1;
		self::$namespacePeriodic = CUTEDAEMON_PERIODIC_NAMESPACE;
		self::$periodicDirectory = CUTEDAEMON_PERIODIC_PATH;
	}

	/**
	 * Attach new task in tasks.
	 *
	 * @param BaseTask $task
	 * @return void.
	 */
	public function attach(BaseTask $task){
		if($task->getPeriod() < self::$minPeriod){
			$task->setPeriod(self::$minPeriod);
		}
		$task->setLastRun(0);
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
			if($this->isTimeToWakeUp($task)){
				try{
					Daemon::log(Daemon::LOG_INFO,
							'Call task to wake up: '. $task->taskName);
					$task->setLastRun(time());
					$task->run(array($this, 'complete'));
					$task->countDownTimesNeed();
				} catch(Exception $e){
					Daemon::log(Daemon::LOG_INFO,
							'An exception was caught by running task  ' .
							$task->taskName . "\nError Message: " .
							$e->getMessage());
				}
			}
		}
	}#method end.

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
	 * Check whether it is the time to run the task.
	 *
	 * @param BaseTask $task:
	 * @return boolean.
	 */
	private function isTimeToWakeUp(BaseTask $task){
		if(($task->getTimesNeed() > 0 || $task->getTimesNeed() === -1)
			&& time() - $task->getLastRun() >= $task->getPeriod()){
			return TRUE;
		} else {
			return FALSE;
		}
	}#method end.

	/**
	 * Update task by files in periodic directory. 
	 *
	 * @return void.
	 */
	public function updateTasks(){
		$currentTaskFiles = glob(self::$periodicDirectory . '*.php');

		//Add new tasks.
		foreach($currentTaskFiles as $taskFile){
			if(is_file($taskFile) && !$this->isTaskAttached($taskFile)){
				$simpleTask = new SimpleTask();
				$simpleTask->prepared($taskFile);
				$this->attach($simpleTask);
				$this->currentTaskLine[] = $taskFile;
			}
		}

		//Remove expired tasks.
		$taskFileRemoved = array_diff($this->currentTaskLine, $currentTaskFiles);
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
