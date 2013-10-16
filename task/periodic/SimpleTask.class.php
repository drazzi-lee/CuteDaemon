<?php

namespace CuteDaemon\Task\Periodic;

use CuteDaemon\Task\BaseTask;

class SimpleTask extends BaseTask{
	public $period = 5;
	public function run(){
		$this->write("Running.\n");
	}


	public function write($message){
		$now = date('[ c ]');
		if(empty($destination))
			$destination = date('y_m_d').'.log';
		if(is_file($destination) && floor(2097131) <= filesize($destination))
			rename($destination, dirname($destination) . DIRECTORY_SEPARATOR 
									. basename($destination) . '-' . time());
		error_log("{$now} #{$method}# {$message}\r\n", 3, $destination);
	}
}
