<?php

require_once("./conn.php");

use lib\conn\TCP_SERVER;

$servis = function($req) {
  echo($req . PHP_EOL);
  return ">> " . $req;
};

$server = new TCP_SERVER($server = "127.0.0.1", $port = 7000, $timeout = 0);

while(1) {
  $server->Loop($servis);
}