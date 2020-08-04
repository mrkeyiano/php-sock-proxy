<?php

namespace Sock;

class SocketClient {

	private $connection;
	private $address;
	private $port;

	public function __construct( $connection ) {
		$address = ''; 
		$port = '';
		socket_getpeername($connection, $address, $port);
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
     //   print_r($bytes);

//
        $firstByte = (int) count($bytes) / 256;
        $secondByte = count($bytes) % 256;


      //   $bytemain = pack ('n', count($bytes));
         $bytemain = pack ( 'C*', $firstByte,$secondByte);

//        socket_write($this->connection, $byte1, strlen($byte1));
//        socket_write($this->connection, $byte2, strlen($byte2));

        socket_write($this->connection, $bytemain, strlen($bytemain));




    }


	
	public function read($len = 1024) {
//		if ( ( $buf = @socket_read( $this->connection, $len, PHP_BINARY_READ  ) ) === false ) {
//				return null;
//		}
//
//		return $buf;


        $offset = 0;
        $socketData = '';

        while ($offset < $len) {
            if (($data = @socket_read ($this->connection, $len-$offset, PHP_BINARY_READ)) === false) {
             //   $this->error();
                return null;
            }

            $dataLen = strlen ($data);
            $offset += $dataLen;
            $socketData .= $data;

            if ($dataLen == 0) { break; }
        }

        return $socketData;
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
