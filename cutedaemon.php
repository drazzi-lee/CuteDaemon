#!/usr/bin/php -q
<?php

require_once 'bootstrap.inc.php';

error_reporting(E_ALL);
use CuteDaemon\System\Daemon;
use CuteDaemon\Common\CuteDaemon;

Daemon::setOptions(array(
    'appName' => 'cutedaemon',
    'appDir' => dirname(__FILE__),
    'appDescription' => 'CuteDaemon, a daemon process as a notifier for wake up tasks written by php',
    'authorName' => 'Pengfei Li',
    'authorEmail' => 'drazzi.lee@gmail.com',
    'sysMemoryLimit' => '1024M',
	'usePEAR'	=> FALSE,
    //'appRunAsGID' => 1000,
    //'appRunAsUID' => 1000,
));
Daemon::setOption('usePEAR', FALSE);
Daemon::setOption('appName', 'cutedaemon');
Daemon::setOption('appDir', dirname(__FILE__));
Daemon::log(Daemon::LOG_INFO, 'CuteDaemon will start soon.');

Daemon::start();
Daemon::log(Daemon::LOG_INFO, 'Daemon: #' .
	Daemon::getOption('appName') .
	' spawned. Log will be written to '.
	Daemon::getOption('logLocation'));
$path = Daemon::writeAutoRun();

$cuteDaemon = new CuteDaemon();
while(TRUE){
	try{
		$cuteDaemon->updateTasks();
		$cuteDaemon->notify();
	} catch(Exception $e){
		Daemon::log(Daemon::LOG_INFO, '[ERROR]'. $e->getMessage());
	}
	sleep(1);
} 
