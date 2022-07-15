<?php

require_once("./conn.php");

use lib\conn\TCP_CLIENT;

$client = new TCP_CLIENT($server = "127.0.0.1", $port = 7000, $timeout = 1000);
$res = $client->Run("Hello");
echo($res);