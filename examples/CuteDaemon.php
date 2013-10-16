<?php
Class CuteDaemon{
	private $tasks = array();
	private $currentTaskLine = array();
	private static $minPeriod;
	private static $namespacePeriodic = 'Task\Periodic';
	private static $periodicDirectory = 'task/periodic/';

	public function __construct(){

	}

	public function attach(Task $task){
		if($task->period < self::$minPeriod){
			$task->period = self::$minPeriod;
		}
		unset($task->lastRun);
		$this->tasks[] = $task;
	}

	public function detach(Task $task){
		foreach($this->tasks as $tkey => $tval){
			if($tval == $task){
				unset($this->tasks[$tkey]);
			}
		}
	}

	public function notify(){
		foreach($this->tasks as $task){
			//if it is time to run this task.
			if($this->isTimeToRun($task)){
				try{
					$task->lastRun = time();
					System_Daemon::log(System_Daemon::LOG_INFO,
							'Call task to run: '. get_class($task));
					//TODO Need fork as child process. 
					$task->run(array($this, 'complete'));
					System_Daemon::log(System_Daemon::LOG_INFO,
							'Task ' . get_class($task) . ' run to end');
				} catch(Exception $e){
					System_Daemon::log(System_Daemon::LOG_INFO,
							'An exception was caught by running task  ' .
							get_class($task) . "\nError Message: " .
							$e->getMessage());
				}
			}
		}
	}

	private function isTimeToRun(Task $task){
		if(time() - $task->lastRun > $task->period){
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function updateTasks(){
		//include file in periodic directory.
		$included = get_included_files();
		$needIncluded = array_diff(glob(self::$periodicDirectory . '*.class.php'), $included);
		foreach(glob(self::$periodicDirectory.'*.class.php') as $taskFile){
			include($taskFile);	
		}

		//Add new tasks.
		$newDeclaredTaskClasses = $this->getDeclaredTaskClasses();
		foreach($newDeclaredTaskClasses as $taskClassName){
			if(!$this->isTaskAttached($taskClassName)){
				$task = new $taskClassName();
				$this->attach($task);
			}
		}
		
		//Remove expired tasks.
		$classesNeedToRemove = array_diff($this->currentTaskLine, $newDeclaredTaskClasses);
		foreach($classesNeedToRemove as $classNeedToRemove){
			foreach($this->tasks as $attachedTask){
				if($attachedTask instanceof $classNeedToRemove){
					$this->detach($attachedTask);
				}
			}
		}
	}

	private function isTaskAttached($className){
		$isTaskAttached = FALSE;
		foreach($this->tasks as $attachedTask){
			if($attachedTask instanceof $className){
				$isTaskAttached = TRUE;
				break;
			}
		}
		return $isTaskAttached;
	}

	private function getDeclaredTaskClasses(){
		$allClasses = get_declared_classes();
		$userDefinedClasses = array();
		foreach($allClasses as $className){
			$function = new \ReflectionClass($className);
			if($function->getNamespaceName() == self::$namespacePeriodic){
				$userDefinedClasses[] = $className;
			}
		}
		return $userDefinedClasses;
	}
}
