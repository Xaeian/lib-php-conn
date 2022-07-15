## TCP/IP

Jeśli szukasz prostej implementacji serwera TCP/IP w technologii **PHP** dla danych serializowanych to chyba dobrze trafiłeś. Biblioteka charakteryzuje się niewielkim narzutem z poziomu aplikacji, jednak trzeba brać pod uwagę, że nie będzie ona odpowiednią dla niektórych rozwiązań. Ja wykorzystuje ją dla urządzeń **IoT**, które wrzucają dane do bazy za jej pośrednictwem.

### Serwer

Przykładowa aplikacja serwerowa, która zwraca klientowi otrzymaną wiadomość z dodanym prefiksem. Zamyka połączenie i czeka na następne.

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

### Klient

Przykładowa aplikacja kliencka, która wysyła do serwera `Hello` i wyświetla odpowiedź.

```php
require_once("./conn.php");

use lib\conn\TCP_CLIENT;

$client = new TCP_CLIENT($server = "127.0.0.1", $port = 7000, $timeout = 1000);
$res = $client->Run("Hello");
echo($res);
```