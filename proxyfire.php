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
            printf( "[%s:%s] Disconnected\n", $client->getAddress(), $client->getPort() );
            return false;
        }
        else {


            $decoded = unpack ( 'n', substr ( $read, 0, 2 ) );
            $responselength = end ( $decoded );

            $bytes = array();
            for($i = 0; $i < mb_strlen($read, 'ASCII'); $i++)

          //  for($i = 0; $i < strlen($read); $i++)
            {
                $bytes[] = ord($read[$i]);
            }


            printf( "\n[%s:%s]: local 2 byte header length is: %s", $remotehost, $remoteport, $responselength );

            printf( "\n\n[%s:%s]: [%s] local bytes in...", $client->getAddress(), $client->getPort(), count($bytes) );

            printf( "\n[%s:%s] recieved: %s", $client->getAddress(), $client->getPort(), $read );



        }

		if( $read != '' ) {



            $bytes = array();
            for($i = 0; $i < mb_strlen($read, 'ASCII'); $i++)
            {
                $bytes[] = ord($read[$i]);
            }

            // create remote socket
            $remotesocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Could not create socket\n");
            $result = socket_connect($remotesocket, $remotehost, $remoteport) or die("Could not connect to server\n");
            $remoteclient = new Client( $remotesocket );

            // forward message
            $remoteclient->sendheader(substr ( $read, 2, count($bytes) ));
            $remoteclient->send($read);

            // get responses
         //   while( true ) {
            $response = $remoteclient->read();
            $remotedecoded = unpack ( 'n', substr ( $response, 0, 2 ) );
            $remoteresponselength = end ( $remotedecoded );




            $remotebytes = array();

            for($i = 0; $i < mb_strlen($response, 'ASCII'); $i++)

             //   for($i = 0; $i < strlen($response); $i++)
            {
                $remotebytes[] = ord($response[$i]);
            }


            printf( "\n[%s:%s]: remote 2byte header length is: %s", $remotehost,$remoteport, $remoteresponselength );


            printf( "\n[%s:%s]: [%s] remote bytes in...", $remotehost, $remoteport, count($remotebytes)  );

            printf( "\n[%s:%s] recieved: %s", $remotehost, $remoteport, $response );



            // send back to local client
                $client->sendheader(substr ( $response, 2, count($remotebytes) ));
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
	printf( "[%s:%s] Disconnected\n", $client->getAddress(), $client->getPort() );
	
}

require "sock/SocketServer.php";

$server = new \Sock\SocketServer();
$server->init();
$server->setConnectionHandler( 'onConnect' );
$server->listen();



function stringToBinary($string)
{
    $characters = str_split($string);

    $binary = [];
    foreach ($characters as $character) {
        $data = unpack('H*', $character);
        $binary[] = base_convert($data[1], 16, 2);
    }

    return implode(' ', $binary);
}