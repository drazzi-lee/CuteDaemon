<?php

/**
 * bootstrap.inc.php
 *
 * CuteDaemon config file.
 * 
 * Li Pengfei <lipengfei@izptec.com>
 */
namespace CuteDaemon;

if(!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300){
	trigger_error('CuteDaemon require php version not lower than 5.3', E_USER_ERROR);
	die();
}

define('ENVIRONMENT', 'development');
//define('ENVIRONMENT', 'production');

define('CUTEDAEMON_ROOT', __DIR__);
define('CUTEDAEMON_SRC', CUTEDAEMON_ROOT . DIRECTORY_SEPARATOR . 'src');

function load($namespace){
	$splitpath = explode('\\', $namespace);
	$path = CUTEDAEMON_SRC;
	$name = '';
	$firstword = true;
	for ($i = 0; $i < count($splitpath); $i++) {
		if ($splitpath[$i] && !$firstword) {
			if ($i == count($splitpath) - 1)
				$name = $splitpath[$i];
			else
				$path .= DIRECTORY_SEPARATOR . $splitpath[$i];
		}
		if ($splitpath[$i] && $firstword) {
			if ($splitpath[$i] != __NAMESPACE__)
				break;
			$firstword = false;
		}
	}
	if (!$firstword) {
		$fullpath = $path . DIRECTORY_SEPARATOR 
				. $name . '.php';
		return include_once($fullpath);
	}
	return false;
}

spl_autoload_register(__NAMESPACE__.'\load');
