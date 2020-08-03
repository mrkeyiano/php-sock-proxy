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
        for($i = 0; $i < mb_strlen($message, 'ASCII'); $i++)
        {
            $bytes[] = ord($message[$i]);
        }
        print_r($bytes);


        $firstByte = count($bytes) / 256;
        $secondByte = count($bytes) % 256;


        $byte1 = pack ( 'C', $firstByte);
        $byte2 = pack ( 'C', $secondByte);


        socket_write($this->connection, $byte1, strlen($byte1));
        socket_write($this->connection, $byte2, strlen($byte2));


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
