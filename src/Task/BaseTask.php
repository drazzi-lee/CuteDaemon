<?php

namespace CuteDaemon\Task;

abstract class BaseTask{

	protected $taskFrom;

	public function setTaskFrom($taskFromFile){
		$this->taskFrom = $taskFromFile;
	}

	public function getTaskFrom(){
		return $this->taskFrom;
	}
}
