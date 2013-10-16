<?php

ini_set('include_path', ini_get('include_path') . ':..');

error_reporting(E_ALL);
require_once 'System/Daemon.php';

System_Daemon::setOption('usePEAR', FALSE);
System_Daemon::setOption('appName', 'CuteDaemon');
System_Daemon::setOption('appDir', dirname(__FILE__));
System_Daemon::log(System_Daemon::LOG_INFO, 'CuteDaemon will start soon.';

System_Daemon::start();
System_Daemon::log(System_Daemon::LOG_INFO, 'Daemon: #' .
	System_Daemon::getOption('appName') .
	' spawned. Log will be written to '.
	System_Daemon::getOption('logLocation');

require_once 'CuteDaemon.php';
$cuteDaemon = new CuteDaemon();
while(TRUE){
	$cuteDaemon->updateTasks();
	$cuteDaemon->notify();
	sleep(1);
} 
