<?php

function run(){
	$this->write("Running.\n");
}


function write($message){
	$now = date('[ c ]');
	if(empty($destination))
		$destination = date('y_m_d').'.log';
	if(is_file($destination) && floor(2097131) <= filesize($destination))
		rename($destination, dirname($destination) . DIRECTORY_SEPARATOR 
				. basename($destination) . '-' . time());
	error_log("{$now} #{$message}\r\n", 3, $destination);
}

write('aaaaaaaaaaaa');
