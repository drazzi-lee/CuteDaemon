#!/usr/bin/php -q
<?php
require_once 'bootstrap.inc.php';

// Include Class
error_reporting(E_ALL);
use CuteDaemon\System\Daemon;

Daemon::setOption("usePEAR", false);
// Bare minimum setup
Daemon::setOption("appName", "simple");
Daemon::setOption("authorEmail", "drazzi.lee@gmail.com");
Daemon::setOption("appDescription", "A simple daemon.");
Daemon::setOption("authorName", "Drazzi Lee");

//Daemon::setOption("appDir", dirname(__FILE__));
Daemon::log(Daemon::LOG_INFO, "Daemon not yet started so ".
    "this will be written on-screen");

// Spawn Deamon!
Daemon::start();
Daemon::log(Daemon::LOG_INFO, "Daemon: '".
    Daemon::getOption("appName").
    "' spawned! This will be written to ".
    Daemon::getOption("logLocation"));

// Your normal PHP code goes here. Only the code will run in the background
// so you can close your terminal session, and the application will
// still run.
$path = Daemon::writeAutoRun();

while(true){
	Daemon::log(Daemon::LOG_INFO, 'running..');
	sleep(5);
}

Daemon::stop();
