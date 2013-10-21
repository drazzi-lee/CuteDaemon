<?php


function write($message){
	$now = date('[ c ]');
	if(empty($destination))
		$destination = __DIR__ . DIRECTORY_SEPARATOR . date('y_m_d').'.log';
	if(is_file($destination) && floor(2097131) <= filesize($destination))
		rename($destination, dirname($destination) . DIRECTORY_SEPARATOR 
				. basename($destination) . '-' . time());
	error_log("{$now} #{$message}\r\n", 3, $destination);
}

sleep(2);
write('aaaaaaaaaaaa');
echo '1asdfafd';
var_dump(Exception);
trigger_error('errors information ...', E_USER_WARNING);
trigger_error('errorsadfasdfasdfadsffdsafasdf', E_ERROR);
