<?php

/**
 * Check dependencies
 */

use Sock\SocketClient as Client;

if( ! extension_loaded('sockets' ) ) {
	echo "This example requires sockets extension (http://www.php.net/manual/en/sockets.installation.php)\n";
	exit(-1);
}

if( ! extension_loaded('pcntl' ) ) {
	echo "This example requires PCNTL extension (http://www.php.net/manual/en/pcntl.installation.php)\n";
	exit(-1);
}

/**
 * Connection handler
 */
function onConnect( $client ) {

    $remotehost="10.154.0.12";
    $remoteport="43666";

	$pid = pcntl_fork();
	
	if ($pid == -1) {
		 die('could not fork');
	} else if ($pid) {
		// parent process
		return;
	}
	
	$read = '';
	printf( "\n[%s] Connected at port %d\n", $client->getAddress(), $client->getPort() );
	
	while( true ) {
		$read = $client->read();

        if( $read === null ) {
            printf( "[%s] Disconnected\n", $client->getAddress() );
            return false;
        }
        else {


            printf( "\n\n[%s]: [%s] local bytes in...", $client->getAddress(), strlen($read) );

            printf( "\n[%s] recieved: %s", $client->getAddress(), $read );



        }

		if( $read != '' ) {
		//	$client->send( '[' . date( DATE_RFC822 ) . '] ' . $read  );

            // create remote socket
            $remotesocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Could not create socket\n");
            $result = socket_connect($remotesocket, $remotehost, $remoteport) or die("Could not connect to server\n");
            $remoteclient = new Client( $remotesocket );

            // forward message
            $remoteclient->send($read);

            // get responses
         //   while( true ) {
            $response = $remoteclient->read();

            printf( "\n[%s]: [%s] remote bytes in...", $remotehost, strlen($response) );

            printf( "\n[%s] recieved: %s", $remotehost, $response );



            // send back to local client
                $client->send($response);

        //    }

        }
		else {
			break;
		}
		
		if( preg_replace( '/[^a-z]/', '', $read ) == 'exit' ) {
			break;
		}

	}
	$client->close();
	printf( "[%s] Disconnected\n", $client->getAddress() );
	
}

require "sock/SocketServer.php";

$server = new \Sock\SocketServer();
$server->init();
$server->setConnectionHandler( 'onConnect' );
$server->listen();
