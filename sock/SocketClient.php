<?php

namespace Sock;

class SocketClient {

	private $connection;
	private $address;
	private $port;

	public function __construct( $connection ) {
		$address = ''; 
		$port = '';
		socket_getsockname($connection, $address, $port);
		$this->address = $address;
		$this->port = $port;
		$this->connection = $connection;
	}
	
	public function send( $message ) {	
		socket_write($this->connection, $message, strlen($message));
	}

	public function sendheader($message){
        $bytes = array();
        for($i = 0; $i < strlen($message); $i++){
            $bytes[] = ord($message[$i]);
        }
      //  print_r($bytes);


        $byte1 = pack ( 'N', count($bytes) / 256); // unsigned 32 bit big endian byte order
        $byte2 = pack ( 'N', count($bytes) % 256); // unsigned 32 bit big endian byte order


        $firstByte = count($bytes) / 256;
        $secondByte = count($bytes) % 256;


      //  $messageLength = "{$firstByte}{$secondByte}";
        $messageLength = "{$byte1}{$byte2}";


        socket_write($this->connection, $messageLength, strlen($messageLength));

    }
	
	public function read($len = 1024) {
		if ( ( $buf = @socket_read( $this->connection, $len, PHP_BINARY_READ  ) ) === false ) {
				return null;
		}
		
		return $buf;
	}

	public function getAddress() {
		return $this->address;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function close() {
	//	socket_shutdown( $this->connection );
		socket_close( $this->connection );
	}
}
