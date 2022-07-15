## TCP/IP

If you are looking for a simple implementation of a TCP/IP server in **PHP** technology for serialized data, you've come to the right place. The library has a low overhead from the application level, but you have to take into account that it will not be suitable for some solutions. I use it for **IoT** devices that upload data to the database via it.

### Server

An example server application that returns a received message to the client with an added prefix. Closes the connection and waits for the next one.

```php
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
```

### Client

A sample client application that sends `Hello` to the server and displays the response.

```php
require_once("./conn.php");

use lib\conn\TCP_CLIENT;

$client = new TCP_CLIENT($server = "127.0.0.1", $port = 7000, $timeout = 1000);
$res = $client->Run("Hello");
echo($res);
```