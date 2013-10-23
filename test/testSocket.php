<?php

require_once("./bootstrap.inc.php"); // Include the File

use CuteDaemon\Common\SocketServer;

$server = new SocketServer("192.168.6.12", 6612); // Create a Server binding to the given ip address and listen to port 31337 for connections
$server->max_clients = 10; // Allow no more than 10 people to connect at a time
$server->hook("CONNECT","handle_connect"); // Run handle_connect every time someone connects
$server->hook("INPUT","handle_input"); // Run handle_input whenever text is sent to the server
$server->infinite_loop(); // Run Server Code Until Process is terminated.


function handle_connect(&$server,&$client,$input){
	SocketServer::socket_write_smart($client->socket,"String? ","");
}

function handle_input(&$server,&$client,$input){
	// You probably want to sanitize your inputs here
	$trim = trim($input); // Trim the input, Remove Line Endings and Extra Whitespace.

	if(strtolower($trim) == "quit") // User Wants to quit the server
	{
		SocketServer::socket_write_smart($client->socket,"Oh... Goodbye..."); // Give the user a sad goodbye message, meany!
		$server->disconnect($client->server_clients_index); // Disconnect this client.
		return; // Ends the function
	}

	//$output = strrev($trim); // Reverse the String
	$output = "You said: ".$trim;

	SocketServer::socket_write_smart($client->socket,$output); // Send the Client back the String
	SocketServer::socket_write_smart($client->socket,"String? ",""); // Request Another String
}
