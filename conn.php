<?php

namespace lib\conn;

use Socket;

//--------------------------------------------------------------------------------------------------------------------- CURL

function curl_conn(string $url = "localhost",
                   string $method = "GET",
                   null|object|array $object = NULL,
                   int $timeout = 1000): object|string
{
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
  curl_setopt($curl, CURLOPT_TIMEOUT_MS, $timeout);
  if($object) {
    $json = json_encode((object)$object);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: text/plain","Content-Length: " . strlen($json)]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
  }
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($curl);
  curl_close($curl);
  if($object = json_decode($response)) return $object;
  return $response;
}

class CURL
{
  public array $postArray = [];
  public int $fileCount = 0;

  function Reset()
  {
    $this->postArray = [];
    $this->fileCount = 0;
  }

  function __construct(public $url = "localhost", public $timeout = 1000)
  {
  }

  function PushValue($key, $value)
  {
    $this->Post[$key] = $value;
  }

  function PushFile($key, $path)
  {
    if(!file_exists($path)) return;
    switch(pathinfo($path, PATHINFO_EXTENSION)) {
      case "zip": $ext = "application/zip"; break;
      default: $ext = "";
    }
    $this->postArray[$key] = curl_file_create(realpath($path), $ext, basename($path));
    $this->fileCount++;
  }
  
  function Run($reset = true)
  {
    $curl = curl_init($this->url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_TIMEOUT_MS, $this->timeout);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type" => "multipart/form-data"]);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $this->Post);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($curl);
    curl_close($curl);
    if($reset) $this->Reset(); 
    return $response;
  }
}

//--------------------------------------------------------------------------------------------------------------------- TCP/IP

abstract class _TCP
{
  public ?string $eFnc = null;
  public ?string $eMsg = null;

  protected function setError($fnc)
  {
    $this->eFnc = $fnc;
    $this->eMag = $this->sock ? socket_strerror(socket_last_error($this->sock)) : socket_strerror(socket_last_error());
  }
}

class TCP_SERVER extends _TCP
{
  private Socket $sock;

  function __construct( public string $server = "127.0.0.1",
                        public int $port = 7000,
                        public float $timeout = 0, // [ms]
                        public int $buffer = 0xFFFF, // (size)
                        public int $backlog = 5
                        )
  {
    set_time_limit(0);
    ob_implicit_flush();

    if(($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
      $this->setError("socket_create");
      return;
    }
    $this->sock = $sock;
    if($timeout) {
      $sec = intval($timeout / 1000);
      $usec = intval(($timeout % 1000) * 1000);
      socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ["sec" => $sec, "usec" => $usec]);
    }

    if(socket_bind($sock, $server, $port) === false) {
      $this->setError("socket_bind");
      return;
    }
    if(socket_listen($sock, $backlog) === false) {
      $this->setError("socket_listen");
      return;
    }
  }

  function Loop($service): void
  {
    if($this->eFnc) return;
    if(($msgsock = socket_accept($this->sock)) === false) {
      $this->setError("socket_accept");
      return;
    }
    if(($req = socket_read($msgsock, $this->buffer, PHP_BINARY_READ)) === false) {
      $this->setError("socket_accept");
      return;
    }
    $res = $service($req);
    socket_write($msgsock, $res, strlen($res));
    socket_close($msgsock);
  }

  function Exit()
  {
    socket_close($this->sock);
  }
}

class TCP_CLIENT extends _TCP
{
  function __construct(public string $server = "127.0.0.1", public int $port = 7000, public int $buffer = 0xFFFF)
  {
  }

  function Run(string $req): ?string
  {
    if(($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
      $this->setError("socket_create");
      return null;
    }
    if((socket_connect($sock, $this->server, $this->port)) === false) {
      $this->setError("socket_create");
      return null;
    }
    socket_write($sock, $req, strlen($req));
    $res = "";
    while($get = socket_read($sock, $this->buffer)) $res .= $get;
    socket_close($sock);
    return $res;
  }
}

//---------------------------------------------------------------------------------------------------------------------
